<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LocalAIEngine
{
    private $summary;
    private $debts;
    private $transactions;
    private $personality;
    private $user;

    public function __construct(array $summary, $debts, $transactions, string $personality = 'tough_love')
    {
        $this->summary = $summary;
        $this->debts = $debts;
        $this->transactions = $transactions;
        $this->personality = $personality;

        // Retrieve user context safely
        $this->user = Auth::user()
            ?? ($debts->first() ? $debts->first()->user : ($transactions->first() ? $transactions->first()->user : null));
    }

    /**
     * Get tone-specific text based on the active personality setting.
     */
    private function getToneText(string $encouragingText, string $toughText): string
    {
        $prefix = $this->personality === 'encouraging' ? "Let's look at this together — " : "Alert: ";
        return $prefix . ($this->personality === 'encouraging' ? $encouragingText : $toughText);
    }

    /**
     * Generate comprehensive heuristic insights based on the local financial telemetry.
     */
    public function generateInsights(): array
    {
        $insights = [];

        // Fetch user metadata & settings
        $settings = $this->user ? $this->user->setting : null;
        $paydayAmount = $settings ? (float)$settings->payday_amount : 0.0;
        $paydayFreq = $settings ? $settings->payday_freq : 'monthly';

        // 1. DETERMINE GROSS MONTHLY INCOME PROJECTION
        if ($paydayAmount > 0) {
            if ($paydayFreq === 'weekly') {
                $grossMonthlyIncome = $paydayAmount * 4.33;
            } elseif ($paydayFreq === '15_days' || $paydayFreq === 'biweekly') {
                $grossMonthlyIncome = $paydayAmount * 2;
            } else {
                $grossMonthlyIncome = $paydayAmount;
            }
        } else {
            // Fallback to recent 30-day transactions income
            $grossMonthlyIncome = (float)($this->summary['monthlyIncome'] ?? 0);
            if ($grossMonthlyIncome <= 0) {
                $grossMonthlyIncome = 3000.0; // conservative baseline
            }
        }

        // 2. TACTICAL RUNWAY & SAFE-TO-SPEND FORMULA
        $liquidBalance = (float)($this->summary['totalBalance'] ?? 0);
        $reservedForBills = (float)($this->summary['upcomingBillsTotal'] ?? 0);
        $daysUntilPayday = (int)($this->summary['daysUntilPayday'] ?? 0);
        $dailyAllowance = $daysUntilPayday > 0 ? (($liquidBalance - $reservedForBills) / $daysUntilPayday) : 0;

        // Check for Cash Shortfall
        if ($reservedForBills > $liquidBalance) {
            $insights[] = [
                'id' => 'SHORTFALL_DETECTED',
                'type' => 'danger',
                'category' => 'runway',
                'priority' => 100,
                'title' => 'Critical Cash Shortfall',
                'message' => $this->getToneText(
                    "Your upcoming bills total is higher than your current balance. You can't spend anything right now. We can resolve this by pausing all non-essential spending immediately.",
                    "Your upcoming obligations exceed your current liquid balance. You can't spend anything. Immediate action required. Freeze all non-essential outlays."
                ),
                'actionableStep' => 'Defer non-critical bills or execute an emergency cash injection to prevent overdrafts.'
            ];
        } elseif ($daysUntilPayday > 0 && $dailyAllowance < 5.0) {
            $insights[] = [
                'id' => 'TIGHT_RUNWAY',
                'type' => 'warning',
                'category' => 'runway',
                'priority' => 90,
                'title' => 'Tight Daily Budget',
                'message' => $this->getToneText(
                    "Things are looking a bit tight this cycle. Try to keep discretionary purchases to an absolute minimum.",
                    "Daily allowance is critically low (under $5/day). Defer all non-essential spending. Maintain survival posture."
                ),
                'actionableStep' => 'Reduce your daily allowance target and log every transaction instantly to monitor compliance.'
            ];
        }

        // Calculate 3-day average spend reserve buffer
        $recentExpenses = $this->transactions->filter(fn($t) => $t->type === 'expense' && $t->date >= now()->subDays(30)->toDateString());
        $total30DayExpenses = $recentExpenses->sum('amount');
        $dailyAvgSpend = $total30DayExpenses / 30.0;
        $recommendedBuffer = $dailyAvgSpend * 3.0;

        if ($liquidBalance > 0 && $liquidBalance < $recommendedBuffer && $recommendedBuffer > 0) {
            $insights[] = [
                'id' => 'LOW_BUFFER_RESERVE',
                'type' => 'warning',
                'category' => 'runway',
                'priority' => 70,
                'title' => 'Micro-Reserve Warning',
                'message' => $this->getToneText(
                    "Your cash reserve is slightly below our recommended 3-day average buffer. Having a bit more set aside will help cover surprise costs.",
                    "Liquid cash is below the recommended 3-day average spending buffer. You are highly vulnerable to surprise micro-expenses."
                ),
                'actionableStep' => "Set aside at least " . number_format($recommendedBuffer, 2) . " as a protective liquidity floor."
            ];
        }

        // 3. DEBT-TO-INCOME (DTI) RATIO
        $activeDebts = $this->debts->where('is_paid_off', false);
        $totalDebtBalance = $activeDebts->sum('current_balance');
        $totalMonthlyDebtPayments = $activeDebts->sum('minimum_payment');

        // Extract housing costs (rent/mortgage) from active bills or transactions
        $housingCost = 0.0;
        if ($this->user) {
            $housingBills = $this->user->bills()->where('is_active', true)
                ->where(fn($q) => $q->where('name', 'like', '%rent%')
                    ->orWhere('name', 'like', '%mortgage%')
                    ->orWhere('name', 'like', '%housing%'))
                ->get();
            $housingCost = $housingBills->sum('amount');
        }
        if ($housingCost <= 0) {
            // Check transactions
            $housingCost = $recentExpenses->filter(fn($t) => 
                stripos($t->category, 'housing') !== false || 
                stripos($t->name, 'rent') !== false || 
                stripos($t->name, 'mortgage') !== false
            )->sum('amount');
        }

        $totalDebtPaymentsWithHousing = $totalMonthlyDebtPayments + $housingCost;
        $dti = $grossMonthlyIncome > 0 ? ($totalDebtPaymentsWithHousing / $grossMonthlyIncome) * 100 : 0;

        if ($dti > 0) {
            if ($dti >= 51.0) {
                $insights[] = [
                    'id' => 'TOXIC_DTI',
                    'type' => 'danger',
                    'category' => 'debt_burden',
                    'priority' => 95,
                    'title' => 'Debt Load Critical',
                    'message' => $this->getToneText(
                        "Your debt obligations consume over half of your estimated income. Let's make an aggressive plan to reduce this step-by-step.",
                        "Debt obligations consume over half your gross income (DTI: " . number_format($dti, 1) . "%). This is a severe financial emergency."
                    ),
                    'actionableStep' => 'Begin the Debt Snowball immediately and halt all new credit lines.'
                ];
            } elseif ($dti >= 44.0) {
                $insights[] = [
                    'id' => 'HIGH_DTI',
                    'type' => 'danger',
                    'category' => 'debt_burden',
                    'priority' => 85,
                    'title' => 'High Debt Burden',
                    'message' => $this->getToneText(
                        "Your debt-to-income ratio is in the warning zone. We should focus on lowering these balances to free up your cash flow.",
                        "High debt load (DTI: " . number_format($dti, 1) . "%). Standard underwriting criteria will reject new credit. Focus fully on balance reduction."
                    ),
                    'actionableStep' => 'Allocate any excess Safe-To-Spend runway cash strictly to principal debt reduction.'
                ];
            } elseif ($dti >= 36.0) {
                $insights[] = [
                    'id' => 'ELEVATED_DTI',
                    'type' => 'warning',
                    'category' => 'debt_burden',
                    'priority' => 60,
                    'title' => 'Elevated Debt Index',
                    'message' => $this->getToneText(
                        "Your debt-to-income ratio is slightly elevated. Keeping it below 35% is ideal for your financial flexibility.",
                        "Debt-to-income is elevated (DTI: " . number_format($dti, 1) . "%). Lenders will impose higher rates. Initiate debt paydowns."
                    ),
                    'actionableStep' => 'Target your highest APR debt with surplus monthly income.'
                ];
            }
        }

        // 4. HOUSING EXPENSE FRONT-END DTI
        if ($housingCost > 0) {
            $housingRatio = ($housingCost / $grossMonthlyIncome) * 100;
            if ($housingRatio >= 41.0) {
                $insights[] = [
                    'id' => 'HOUSING_CRISIS',
                    'type' => 'danger',
                    'category' => 'spending',
                    'priority' => 88,
                    'title' => 'Housing Cost Crisis',
                    'message' => $this->getToneText(
                        "Housing costs consume a very large portion of your income. We might want to look for creative ways to lower this fixed expense.",
                        "Housing costs consume " . number_format($housingRatio, 1) . "% of gross income. This is a severe financial drain. Immediate review required."
                    ),
                    'actionableStep' => 'Investigate rent renegotiation, roommate options, or refinancing to lower fixed overhead.'
                ];
            } elseif ($housingRatio >= 32.0) {
                $insights[] = [
                    'id' => 'HOUSING_DRAIN',
                    'type' => 'warning',
                    'category' => 'spending',
                    'priority' => 75,
                    'title' => 'Housing Cost Strain',
                    'message' => $this->getToneText(
                        "Your housing expense is slightly above the typical threshold. Keeping an eye on other fixed utility bills can help balance things out.",
                        "Housing expenses are elevated at " . number_format($housingRatio, 1) . "% of gross income, squeezing your discretionary limits."
                    ),
                    'actionableStep' => 'Audit home utility bills and cancel unnecessary home-related subscriptions.'
                ];
            }
        }

        // 5. EMERGENCY FUND COVERAGE MONTHS
        $savingsBalance = 0.0;
        if ($this->user) {
            $savingsBalance = (float)$this->user->accounts()->where('is_active', true)->where('type', 'savings')->sum('balance');
        }
        $avgMonthlyExpenses = $total30DayExpenses > 0 ? $total30DayExpenses : 2000.0;
        $coverageMonths = $savingsBalance / $avgMonthlyExpenses;

        if ($savingsBalance <= 0) {
            $insights[] = [
                'id' => 'NO_EMERGENCY_FUND',
                'type' => 'danger',
                'category' => 'resilience',
                'priority' => 98,
                'title' => 'No Safety Net',
                'message' => $this->getToneText(
                    "You don't have an active savings reserve set up yet. Building even a tiny starter buffer can save you from unexpected debt.",
                    "You have no emergency fund. A single surprise expense will force you into credit card debt immediately. Start today."
                ),
                'actionableStep' => 'Open a dedicated savings account and direct your next $500 of surplus cash there.'
            ];
        } elseif ($coverageMonths < 1.0) {
            $insights[] = [
                'id' => 'THIN_EMERGENCY_FUND',
                'type' => 'warning',
                'category' => 'resilience',
                'priority' => 80,
                'title' => 'Thin Safety Net',
                'message' => $this->getToneText(
                    "Your emergency fund is a great start, but it covers less than a month of typical expenses. Let's grow it to match your base needs.",
                    "Emergency reserve covers only " . number_format($coverageMonths * 30, 0) . " days of expenses. Grow this starter fund before extra debt payments."
                ),
                'actionableStep' => 'Build the reserve balance to at least $1,000 to insulate your day-to-day accounts.'
            ];
        } elseif ($coverageMonths >= 12.0) {
            $insights[] = [
                'id' => 'EXCESS_CASH',
                'type' => 'info',
                'category' => 'efficiency',
                'priority' => 45,
                'title' => 'Idle Cash Opportunity',
                'message' => $this->getToneText(
                    "You have a very strong safety net! Since you have over a year of reserves, you might want to look at investing some excess cash.",
                    "Idle capital exceeds 12 months of expenses. Inflation is eroding your purchasing power. Review investment allocations."
                ),
                'actionableStep' => 'Transfer excess cash above 6 months of expenses to high-yield accounts or index funds.'
            ];
        }

        // 6. CREDIT UTILIZATION
        $creditCardDebts = $activeDebts->filter(fn($d) => stripos($d->type, 'credit') !== false || stripos($d->type, 'card') !== false);
        $totalCreditBalance = $creditCardDebts->sum('current_balance');
        $totalCreditLimits = $creditCardDebts->sum('original_amount');

        if ($totalCreditLimits > 0) {
            $utilization = ($totalCreditBalance / $totalCreditLimits) * 100;
            if ($utilization >= 75.0) {
                $insights[] = [
                    'id' => 'MAXED_CREDIT',
                    'type' => 'danger',
                    'category' => 'credit',
                    'priority' => 92,
                    'title' => 'Cards Near Limit',
                    'message' => $this->getToneText(
                        "Your credit cards are near their maximum limits. This can impact your score, so paying these down is a top priority.",
                        "Credit card utilization is at a critical " . number_format($utilization, 1) . "%. Your credit score is actively taking damage."
                    ),
                    'actionableStep' => 'Halt credit usage and prioritize paying down the card closest to its limit.'
                ];
            } elseif ($utilization >= 30.0) {
                $insights[] = [
                    'id' => 'HIGH_UTILIZATION',
                    'type' => 'warning',
                    'category' => 'credit',
                    'priority' => 78,
                    'title' => 'High Credit Usage',
                    'message' => $this->getToneText(
                        "Your credit utilization is above 30%, which is where it begins to affect your score. Let's aim to bring it down.",
                        "Credit utilization is elevated at " . number_format($utilization, 1) . "%. Credit reporting systems penalize ratios above 30%."
                    ),
                    'actionableStep' => 'Reduce utilization by allocating cash to credit balances or requesting a limit increase.'
                ];
            }
        }

        // 7. APR TOXICITY & RULE OF 72
        foreach ($activeDebts as $debt) {
            $apr = $debt->interest_rate;
            $yearsToDouble = $apr > 0 ? (72 / $apr) : 999;
            
            if ($apr >= 30.0) {
                $insights[] = [
                    'id' => 'PREDATORY_DEBT_' . $debt->id,
                    'type' => 'danger',
                    'category' => 'debt_cost',
                    'priority' => 97,
                    'title' => 'Predatory Rate Debt: ' . $debt->name,
                    'message' => $this->getToneText(
                        "The rate on {$debt->name} is extremely high. Finding a transfer option or paying this off quickly will save you a lot of interest.",
                        "Predatory APR detected on {$debt->name} (" . number_format($apr, 1) . "%). At this rate, the liability doubles in " . number_format($yearsToDouble, 1) . " years."
                    ),
                    'actionableStep' => 'Consolidate via balance transfer or allocate 100% of discretionary surplus to destroy this balance.'
                ];
            } elseif ($apr >= 20.0) {
                $insights[] = [
                    'id' => 'HIGH_APR_' . $debt->id,
                    'type' => 'danger',
                    'category' => 'debt_cost',
                    'priority' => 87,
                    'title' => 'High-Cost Debt: ' . $debt->name,
                    'message' => $this->getToneText(
                        "Interest costs are running high on {$debt->name} (" . number_format($apr, 1) . "%). Paying this down will free up your monthly budget.",
                        "High interest drain on {$debt->name} (" . number_format($apr, 1) . "%). Compounding interest is draining your wealth potential."
                    ),
                    'actionableStep' => 'Set this account as your primary target under the Avalanche strategy.'
                ];
            } elseif ($apr >= 13.0) {
                $insights[] = [
                    'id' => 'ELEVATED_APR_' . $debt->id,
                    'type' => 'warning',
                    'category' => 'debt_cost',
                    'priority' => 65,
                    'title' => 'Above-Average Debt Rate',
                    'message' => $this->getToneText(
                        "Interest rate on {$debt->name} is moderate. It's worth keeping an eye on as we clear out higher-interest items.",
                        "Elevated interest rate on {$debt->name} (" . number_format($apr, 1) . "%). Pay it down once high-priority targets are clear."
                    ),
                    'actionableStep' => 'Pay minimums for now; roll over payments once toxic debt balances reach zero.'
                ];
            }
        }

        // 8. SAVINGS RATE
        $monthlyExpenses = (float)($this->summary['monthlyExpenses'] ?? 0);
        $netMonthlyIncome = (float)($this->summary['monthlyIncome'] ?? 0);
        if ($netMonthlyIncome <= 0) {
            $netMonthlyIncome = $grossMonthlyIncome;
        }
        $savingsAmount = max(0.0, $netMonthlyIncome - $monthlyExpenses);
        $savingsRate = $netMonthlyIncome > 0 ? ($savingsAmount / $netMonthlyIncome) * 100 : 0;

        if ($savingsRate <= 0) {
            $insights[] = [
                'id' => 'NO_SAVINGS',
                'type' => 'danger',
                'category' => 'savings',
                'priority' => 86,
                'title' => 'No Savings Rate',
                'message' => $this->getToneText(
                    "It looks like expenses took up all of your income this month. Setting up a tiny automated saving can help build a cushion.",
                    "Your actual savings rate is 0%. Every dollar generated is instantly consumed, leaving you zero defense against future surprises."
                ),
                'actionableStep' => 'Automate a $25 transfer on payday to build momentum.'
            ];
        } elseif ($savingsRate < 5.0) {
            $insights[] = [
                'id' => 'LOW_SAVINGS',
                'type' => 'warning',
                'category' => 'savings',
                'priority' => 72,
                'title' => 'Low Savings Rate',
                'message' => $this->getToneText(
                    "You're saving a little bit, but we'd love to see you hit 5-10% to build security for the future.",
                    "Savings rate is a thin " . number_format($savingsRate, 1) . "%. Capital accumulation is too slow for wealth building."
                ),
                'actionableStep' => 'Reduce discretionary wants by 10% next week to bolster cash reserves.'
            ];
        }

        // 9. SUBSCRIPTION DRAIN (MENTAL ACCOUNTING NUDGE)
        $subscriptionTotal = 0.0;
        if ($this->user) {
            $subBills = $this->user->bills()->where('is_active', true)
                ->where(fn($q) => $q->where('name', 'like', '%subscription%')
                    ->orWhere('name', 'like', '%netflix%')
                    ->orWhere('name', 'like', '%spotify%')
                    ->orWhere('name', 'like', '%hulu%')
                    ->orWhere('name', 'like', '%youtube%')
                    ->orWhere('name', 'like', '%disney%')
                    ->orWhere('name', 'like', '%amazon%')
                    ->orWhere('name', 'like', '%gym%'))
                ->get();
            $subscriptionTotal = $subBills->sum('amount');
        }
        if ($subscriptionTotal <= 0) {
            $subscriptionTotal = $recentExpenses->filter(fn($t) => 
                stripos($t->category, 'subscription') !== false || 
                stripos($t->name, 'netflix') !== false || 
                stripos($t->name, 'spotify') !== false || 
                stripos($t->name, 'gym') !== false || 
                stripos($t->name, 'prime') !== false
            )->sum('amount');
        }

        if ($subscriptionTotal > 0) {
            $subscriptionPercentage = ($subscriptionTotal / $netMonthlyIncome) * 100;
            if ($subscriptionPercentage >= 10.0) {
                $insights[] = [
                    'id' => 'SUBSCRIPTION_CRISIS',
                    'type' => 'danger',
                    'category' => 'spending',
                    'priority' => 82,
                    'title' => 'Subscription Overload',
                    'message' => $this->getToneText(
                        "Small recurring payments are adding up to over 10% of your income. Auditing these might reveal things we can cancel.",
                        "Subscriptions devour " . number_format($subscriptionPercentage, 1) . "% of net income. This is a severe recurring drain."
                    ),
                    'actionableStep' => 'Perform an immediate audit of active subscriptions and cut at least three non-essential services.'
                ];
            } elseif ($subscriptionPercentage >= 5.0) {
                $insights[] = [
                    'id' => 'SUBSCRIPTION_DRAIN',
                    'type' => 'warning',
                    'category' => 'spending',
                    'priority' => 62,
                    'title' => 'Subscription Creep',
                    'message' => $this->getToneText(
                        "Your subscription services are creeping up close to 5%. Let's review if you still use all of them regularly.",
                        "Subscription charges make up " . number_format($subscriptionPercentage, 1) . "% of income. This is an invisible discretionary leak."
                    ),
                    'actionableStep' => 'Review the Bills tab and disable auto-pay for subscriptions you did not use this week.'
                ];
            }
        }

        // 10. RECOMMEND DEBT STRATEGY (Behavioral vs Mathematical)
        if ($activeDebts->count() > 0) {
            $hasLowBalance = $activeDebts->filter(fn($d) => $d->current_balance < 1000.0)->count() > 0;
            $hasTinyBalances = $activeDebts->filter(fn($d) => $d->current_balance < 500.0)->count() >= 2;
            $hasHighApr = $activeDebts->filter(fn($d) => $d->interest_rate >= 20.0)->count() > 0;
            
            $strategyName = 'Avalanche';
            $strategyMessage = 'We recommend prioritizing debts by interest rate (Avalanche) to minimize total compounding interest cost.';
            $strategyAction = 'Sort your debts by APR descending; pay extra only on the highest-interest target.';

            if ($hasTinyBalances && $hasHighApr) {
                $strategyName = 'Hybrid';
                $strategyMessage = 'We recommend a Hybrid strategy: eliminate the smallest 1-2 balances first for quick motivation, then switch to interest-rate sorting (Avalanche).';
                $strategyAction = 'Wipe out the smallest 2 debts first, then target the remaining liabilities sorted by APR descending.';
            } elseif ($activeDebts->count() >= 3 && $hasLowBalance) {
                $strategyName = 'Snowball';
                $strategyMessage = 'We recommend the Debt Snowball strategy. Sorting debts by balance ascending triggers early psychological wins, reinforcing positive behavior.';
                $strategyAction = 'Sort your debts by current balance ascending; pay extra cash strictly to the smallest liability first.';
            }

            $insights[] = [
                'id' => 'DEBT_STRATEGY_RECOMMENDATION',
                'type' => 'success',
                'category' => 'debt',
                'priority' => 50,
                'title' => 'Optimal Debt Blueprint: ' . $strategyName,
                'message' => $this->getToneText(
                    $strategyMessage,
                    $strategyMessage
                ),
                'actionableStep' => $strategyAction
            ];
        }

        // 11. BUDGET FRAMEWORK & MODE
        $unsecuredDebt = $activeDebts->filter(fn($d) => stripos($d->type, 'mortgage') === false)->sum('current_balance');
        $annualGrossIncome = $grossMonthlyIncome * 12;
        $unsecuredDebtRatio = $annualGrossIncome > 0 ? ($unsecuredDebt / $annualGrossIncome) : 0;

        if ($unsecuredDebtRatio > 0.20) {
            $insights[] = [
                'id' => 'BUDGET_MODE_DEEP_DEBT',
                'type' => 'warning',
                'category' => 'savings',
                'priority' => 48,
                'title' => 'Active Framework: Deep Debt Mode (75/5/20)',
                'message' => $this->getToneText(
                    "Your total unsecured debt exceeds 20% of your annual income. We've updated your framework recommendations to maximize your debt payoff power.",
                    "Active parameters: Deep Debt Mode engaged (75% Needs, 5% Wants, 20% Debt). Stop all lifestyle creep immediately."
                ),
                'actionableStep' => 'Adjust your monthly discretionary allowances downwards to match wants limit.'
            ];
        } else {
            $insights[] = [
                'id' => 'BUDGET_MODE_STANDARD',
                'type' => 'success',
                'category' => 'savings',
                'priority' => 20,
                'title' => 'Active Framework: Standard 50/30/20',
                'message' => $this->getToneText(
                    "Your financial base is healthy. We suggest allocating 50% of income to Needs, 30% to Wants, and 20% to Savings & Debt.",
                    "Active parameters: Standard 50/30/20 rule. Monitor discretionary wants to stay within bounds."
                ),
                'actionableStep' => 'Divide your income into needs/wants/future buckets to track alignment.'
            ];
        }

        // 12. DETERMINISTIC PRIORITY STEP (SAFE TO INVEST HIERARCHY)
        $priorityStep = 7;
        $priorityStepTitle = "Step 7: Build Wealth and Invest";
        $priorityStepDesc = "All base reserves and liabilities are stabilized. Focus on investing and long-term asset building.";
        $priorityStepAction = "Explore index funds, tax-advantaged accounts, or estate planning.";

        if ($savingsBalance < 1000.0) {
            $priorityStep = 1;
            $priorityStepTitle = "Step 1: Starter Emergency Fund";
            $priorityStepDesc = "Your emergency savings are currently below $1,000. Building this initial buffer prevents minor emergencies from forcing you back into high-cost debt.";
            $priorityStepAction = "Transfer every extra dollar of Safe-To-Spend cash to savings until balance reaches $1,000.";
        } elseif ($activeDebts->filter(fn($d) => stripos($d->type, 'mortgage') === false)->count() > 0) {
            $priorityStep = 2;
            $priorityStepTitle = "Step 2: Eliminate Non-Mortgage Liabilities";
            $priorityStepDesc = "You have active non-mortgage liabilities. Clearing consumer debt frees up your cash flow for real wealth generation.";
            $priorityStepAction = "Execute your recommended debt blueprint and pay down target balances.";
        } elseif ($coverageMonths < 3.0) {
            $priorityStep = 3;
            $priorityStepTitle = "Step 3: Full Emergency Reserve";
            $priorityStepDesc = "Your emergency savings covers less than 3 months of expenses. Expanding this buffer is necessary to withstand major income disruptions.";
            $priorityStepAction = "Funnel your surplus cash flow into your savings account until you have 3 to 6 months of expenses.";
        }

        $insights[] = [
            'id' => 'SAFE_TO_INVEST_PRIORITY',
            'type' => 'success',
            'category' => 'savings',
            'priority' => 55,
            'title' => 'Strategic Priority | ' . $priorityStepTitle,
            'message' => $this->getToneText(
                $priorityStepDesc,
                $priorityStepDesc
            ),
            'actionableStep' => $priorityStepAction
        ];

        // Sort by priority desc
        usort($insights, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $insights;
    }

    /**
     * Answer a user question using local financial telemetry data.
     * No external API calls - entirely deterministic from ledger data.
     */
    public function askNova(string $question): array
    {
        $q = strtolower(trim($question));
        $settings = $this->user ? $this->user->setting : null;
        $paydayAmount = $settings ? (float)$settings->payday_amount : 0.0;
        $paydayFreq = $settings ? $settings->payday_freq : 'monthly';

        // Compute gross monthly income
        if ($paydayAmount > 0) {
            if ($paydayFreq === 'weekly') {
                $grossMonthlyIncome = $paydayAmount * 4.33;
            } elseif ($paydayFreq === '15_days' || $paydayFreq === 'biweekly') {
                $grossMonthlyIncome = $paydayAmount * 2;
            } else {
                $grossMonthlyIncome = $paydayAmount;
            }
        } else {
            $grossMonthlyIncome = max((float)($this->summary['monthlyIncome'] ?? 0), 3000.0);
        }

        // Local data points
        $liquidBalance = (float)($this->summary['totalBalance'] ?? 0);
        $reservedForBills = (float)($this->summary['upcomingBillsTotal'] ?? 0);
        $safeToSpend = (float)($this->summary['safeToSpend'] ?? 0);
        $daysUntilPayday = (int)($this->summary['daysUntilPayday'] ?? 0);
        $nextPayday = $this->summary['nextPayday'] ?? 'unknown';
        $monthlyExpenses = (float)($this->summary['monthlyExpenses'] ?? 0);
        $monthlyIncome = (float)($this->summary['monthlyIncome'] ?? 0);
        $totalDebt = (float)($this->summary['totalDebt'] ?? 0);
        $dailyAllowance = $daysUntilPayday > 0 ? ($safeToSpend / $daysUntilPayday) : 0;

        // Savings accounts
        $savingsBalance = 0.0;
        if ($this->user) {
            $savingsBalance = (float)$this->user->accounts()->where('is_active', true)->where('type', 'savings')->sum('balance');
        }

        // Active debts
        $activeDebts = $this->debts->where('is_paid_off', false);
        $totalDebtBalance = $activeDebts->sum('current_balance');
        $totalMinPayments = $activeDebts->sum('minimum_payment');

        // Recent transactions breakdown
        $recentExpenses = $this->transactions->filter(fn($t) => $t->type === 'expense' && $t->date >= now()->subDays(30)->toDateString());
        $recentIncome = $this->transactions->filter(fn($t) => $t->type === 'income' && $t->date >= now()->subDays(30)->toDateString());
        $total30DayExpenses = $recentExpenses->sum('amount');
        $total30DayIncome = $recentIncome->sum('amount');

        // Category spending breakdown
        $categoryBreakdown = $recentExpenses->groupBy('category')->map(fn($items) => $items->sum('amount'))->sortDesc();

        // Bills data
        $activeBills = [];
        $totalBillsCost = 0;
        if ($this->user) {
            $bills = $this->user->bills()->where('is_active', true)->get();
            $totalBillsCost = $bills->sum('amount');
            $activeBills = $bills;
        }

        // ========================
        // QUESTION INTENT MATCHING
        // ========================

        // --- SPENDING / EXPENSES ---
        if (preg_match('/spend|expense|spent|spending|where.*money.*go|where.*going|how much.*spend|cost/i', $q)) {
            $topCategories = $categoryBreakdown->take(5);
            $breakdown = "";
            foreach ($topCategories as $cat => $amount) {
                $pct = $total30DayExpenses > 0 ? round(($amount / $total30DayExpenses) * 100, 1) : 0;
                $breakdown .= "\n• **{$cat}**: $" . number_format($amount, 2) . " ({$pct}%)";
            }

            if ($total30DayExpenses <= 0) {
                return [
                    'answer' => "I don't have enough transaction data yet to analyze your spending patterns. Start logging your expenses and I'll be able to give you a full breakdown.",
                    'type' => 'info',
                    'icon' => 'analytics',
                ];
            }

            $dailyAvg = round($total30DayExpenses / 30, 2);
            return [
                'answer' => "Over the last 30 days, you've spent a total of **$" . number_format($total30DayExpenses, 2) . "** across " . $categoryBreakdown->count() . " categories. Your daily average spend is **$" . number_format($dailyAvg, 2) . "/day**.\n\nHere's where your money went:" . $breakdown,
                'type' => 'info',
                'icon' => 'pie_chart',
            ];
        }

        // --- SAFE TO SPEND / ALLOWANCE ---
        if (preg_match('/safe.*spend|allowance|how much.*left|can.*spend|afford|budget.*left|runway|survive|servive/i', $q)) {
            $answer = "Your current **Safe-to-Spend** balance is **$" . number_format($safeToSpend, 2) . "**.";
            if ($safeToSpend <= 0) {
                $answer .= "\n\n⚠️ **You can't spend anything.** Your upcoming obligations exceed your available cash.";
            } else {
                if ($daysUntilPayday > 0) {
                    $answer .= " With **{$daysUntilPayday} days** until your next payday ({$nextPayday}), your daily allowance is **$" . number_format($dailyAllowance, 2) . "/day**.";
                }
            }
            if ($reservedForBills > 0) {
                $answer .= "\n\nThis already accounts for **$" . number_format($reservedForBills, 2) . "** reserved for upcoming bills.";
            }
            $type = $safeToSpend > 0 ? ($dailyAllowance < 10 ? 'warning' : 'success') : 'danger';
            return [
                'answer' => $answer,
                'type' => $type,
                'icon' => 'account_balance_wallet',
            ];
        }

        // --- SAVINGS ---
        if (preg_match('/saving|emergency.*fund|reserve|how much.*saved|safety.*net/i', $q)) {
            $avgMonthlyExp = $total30DayExpenses > 0 ? $total30DayExpenses : 2000;
            $coverageMonths = $avgMonthlyExp > 0 ? ($savingsBalance / $avgMonthlyExp) : 0;

            $answer = "Your current savings balance is **$" . number_format($savingsBalance, 2) . "**.";
            if ($savingsBalance > 0) {
                $answer .= " This covers approximately **" . number_format($coverageMonths, 1) . " months** of your average expenses.";
                if ($coverageMonths < 3) {
                    $answer .= "\n\n⚠️ Financial experts recommend having at least **3-6 months** of expenses saved. Consider building this reserve before aggressive investing.";
                } else {
                    $answer .= "\n\n✅ You have a healthy emergency buffer. Consider putting excess savings into higher-yield options.";
                }
            } else {
                $answer .= "\n\n🚨 You don't have a savings safety net yet. Even $500 can prevent a minor emergency from spiraling into high-interest debt. Start small.";
            }
            return [
                'answer' => $answer,
                'type' => $savingsBalance > 0 ? ($coverageMonths >= 3 ? 'success' : 'warning') : 'danger',
                'icon' => 'savings',
            ];
        }

        // --- DEBT ---
        if (preg_match('/debt|owe|loan|credit.*card|liability|liabilities|interest|payoff|pay.*off|paydown/i', $q)) {
            if ($activeDebts->count() === 0) {
                return [
                    'answer' => "You currently have **no active debts** tracked. If you have any untracked liabilities, add them in the Debt Manager to get strategic payoff recommendations.",
                    'type' => 'success',
                    'icon' => 'verified',
                ];
            }

            $debtList = "";
            foreach ($activeDebts->sortByDesc('interest_rate')->take(5) as $debt) {
                $debtList .= "\n• **{$debt->name}**: $" . number_format($debt->current_balance, 2) . " @ " . number_format($debt->interest_rate, 1) . "% APR (min: $" . number_format($debt->minimum_payment, 2) . "/mo)";
            }

            $dti = $grossMonthlyIncome > 0 ? ($totalMinPayments / $grossMonthlyIncome) * 100 : 0;

            $answer = "You have **" . $activeDebts->count() . " active debts** totaling **$" . number_format($totalDebtBalance, 2) . "**. Your combined minimum payments are **$" . number_format($totalMinPayments, 2) . "/month**.";
            if ($dti > 0) {
                $answer .= " Your debt-to-income ratio is **" . number_format($dti, 1) . "%**.";
            }
            $answer .= "\n\nDebt breakdown (sorted by interest rate):" . $debtList;

            $highestApr = $activeDebts->max('interest_rate');
            if ($highestApr >= 20) {
                $answer .= "\n\n🔥 You have high-interest debt above 20% APR. Prioritize paying this down first (Avalanche strategy) to minimize interest cost.";
            }

            return [
                'answer' => $answer,
                'type' => $dti > 40 ? 'danger' : ($dti > 20 ? 'warning' : 'info'),
                'icon' => 'credit_score',
            ];
        }

        // --- BILLS ---
        if (preg_match('/bill|obligation|recurring|subscription|due|upcoming/i', $q)) {
            if (count($activeBills) === 0) {
                return [
                    'answer' => "You don't have any active bills tracked yet. Head to the Bills Manager to set up your recurring obligations so I can factor them into your runway calculations.",
                    'type' => 'info',
                    'icon' => 'receipt',
                ];
            }

            $billList = "";
            foreach ($activeBills->sortBy('next_due_date')->take(8) as $bill) {
                $billList .= "\n• **{$bill->name}**: $" . number_format($bill->amount, 2) . "/{$bill->frequency} — next due: {$bill->next_due_date}";
            }

            $answer = "You have **" . count($activeBills) . " active bills** totaling **$" . number_format($totalBillsCost, 2) . "/month equivalent**. Currently **$" . number_format($reservedForBills, 2) . "** is reserved for upcoming obligations before your next payday.";
            $answer .= "\n\nYour obligations:" . $billList;

            return [
                'answer' => $answer,
                'type' => 'info',
                'icon' => 'receipt_long',
            ];
        }

        // --- INCOME / PAYDAY ---
        if (preg_match('/income|earn|salary|paycheck|payday|pay.*day|how much.*make|wage/i', $q)) {
            $answer = "Based on your settings, your estimated **gross monthly income** is **$" . number_format($grossMonthlyIncome, 2) . "**";
            if ($paydayAmount > 0) {
                $answer .= " ($" . number_format($paydayAmount, 2) . " per {$paydayFreq} pay cycle)";
            }
            $answer .= ".";

            if ($total30DayIncome > 0) {
                $answer .= "\n\nIn the last 30 days, you've logged **$" . number_format($total30DayIncome, 2) . "** in income transactions.";
            }

            $answer .= "\n\nYour next payday is on **{$nextPayday}** ({$daysUntilPayday} days away).";

            $savingsRate = $grossMonthlyIncome > 0 ? (max(0, $grossMonthlyIncome - $monthlyExpenses) / $grossMonthlyIncome) * 100 : 0;
            if ($savingsRate > 0) {
                $answer .= " Your current savings rate is approximately **" . number_format($savingsRate, 1) . "%** of gross income.";
            }

            return [
                'answer' => $answer,
                'type' => 'info',
                'icon' => 'payments',
            ];
        }

        // --- BALANCE / OVERVIEW ---
        if (preg_match('/balance|total|overview|summary|status|how.*doing|financial.*health|situation|snapshot/i', $q)) {
            $answer = "📊 **Financial Snapshot**\n\n";
            $answer .= "• **Total liquid balance**: $" . number_format($liquidBalance, 2) . "\n";
            $answer .= "• **Safe-to-Spend**: $" . number_format($safeToSpend, 2) . "\n";
            
            if ($safeToSpend <= 0) {
                $answer .= "  ⚠️ **Warning: You can't spend anything.**\n";
            } else {
                $answer .= "• **Daily allowance**: $" . number_format($dailyAllowance, 2) . "/day ({$daysUntilPayday} days left)\n";
            }
            
            $answer .= "• **Monthly income**: $" . number_format($grossMonthlyIncome, 2) . "\n";
            $answer .= "• **Monthly expenses**: $" . number_format($monthlyExpenses, 2) . "\n";
            $answer .= "• **Active debts**: $" . number_format($totalDebtBalance, 2) . " (" . $activeDebts->count() . " accounts)\n";
            $answer .= "• **Savings reserve**: $" . number_format($savingsBalance, 2) . "\n";
            $answer .= "• **Upcoming bills**: $" . number_format($reservedForBills, 2) . " due before payday\n";

            $netWorth = $liquidBalance + $savingsBalance - $totalDebtBalance;
            $answer .= "\n**Estimated Net Worth**: $" . number_format($netWorth, 2);

            $type = 'info';
            if ($safeToSpend < 0 || $totalDebtBalance > $liquidBalance * 5) $type = 'danger';
            elseif ($dailyAllowance < 10 || $totalDebtBalance > $liquidBalance * 2) $type = 'warning';
            elseif ($savingsBalance > $total30DayExpenses * 3) $type = 'success';

            return [
                'answer' => $answer,
                'type' => $type,
                'icon' => 'monitoring',
            ];
        }

        // --- BUDGET ADVICE / TIPS ---
        if (preg_match('/tip|advice|suggest|help|recommend|what.*should|how.*can|improve|strategy|plan/i', $q)) {
            $tips = [];

            if ($safeToSpend < 0) {
                $tips[] = "🚨 **Immediate action needed**: Your upcoming bills exceed your balance. Defer non-critical payments or find an emergency cash injection.";
            }
            if ($dailyAllowance > 0 && $dailyAllowance < 15) {
                $tips[] = "💡 **Tight runway**: Limit all discretionary spending to essentials only until your next payday on {$nextPayday}.";
            }
            if ($totalDebtBalance > 0) {
                $highestAprDebt = $activeDebts->sortByDesc('interest_rate')->first();
                if ($highestAprDebt && $highestAprDebt->interest_rate >= 15) {
                    $tips[] = "🔥 **Prioritize debt**: Focus extra payments on **{$highestAprDebt->name}** ({$highestAprDebt->interest_rate}% APR) to minimize interest cost.";
                }
            }
            if ($savingsBalance < 1000) {
                $tips[] = "🛡️ **Build your safety net**: Aim to save at least $1,000 as a starter emergency fund before extra debt payments.";
            }

            $topCategory = $categoryBreakdown->keys()->first();
            if ($topCategory && $total30DayExpenses > 0) {
                $topAmount = $categoryBreakdown->first();
                $tips[] = "📉 **Highest spend category**: You spent \$" . number_format($topAmount, 2) . " on **{$topCategory}** this month. Look for ways to trim this.";
            }

            if ($monthlyExpenses > 0 && $grossMonthlyIncome > 0) {
                $savingsRate = (($grossMonthlyIncome - $monthlyExpenses) / $grossMonthlyIncome) * 100;
                if ($savingsRate < 20) {
                    $tips[] = "📊 **50/30/20 rule**: Aim to save 20% of income. Your current savings rate is " . number_format(max(0, $savingsRate), 1) . "%.";
                }
            }

            if (empty($tips)) {
                $tips[] = "✅ Your finances are looking healthy! Keep tracking expenses and maintaining your emergency fund.";
                $tips[] = "💰 Consider automating savings transfers on payday to build wealth passively.";
            }

            return [
                'answer' => "Here are personalized tips based on your current financial data:\n\n" . implode("\n\n", $tips),
                'type' => 'success',
                'icon' => 'lightbulb',
            ];
        }

        // --- CATCH-ALL: General financial summary ---
        $answer = "I analyzed your financial data to help answer that. Here's what I know:\n\n";
        $answer .= "• **Balance**: $" . number_format($liquidBalance, 2) . " across your accounts\n";
        $answer .= "• **Safe-to-Spend**: $" . number_format($safeToSpend, 2) . " until payday ({$daysUntilPayday} days)\n";
        if ($safeToSpend <= 0) {
            $answer .= "  ⚠️ **Warning: You can't spend anything.**\n";
        }
        $answer .= "• **Monthly expenses**: $" . number_format($monthlyExpenses, 2) . "\n";

        if ($activeDebts->count() > 0) {
            $answer .= "• **Active debts**: $" . number_format($totalDebtBalance, 2) . "\n";
        }

        $answer .= "\nTry asking me about specific topics like:\n";
        $answer .= "• \"Where is my money going?\"\n";
        $answer .= "• \"How much can I spend today?\"\n";
        $answer .= "• \"What's my debt situation?\"\n";
        $answer .= "• \"Give me financial advice\"\n";
        $answer .= "• \"What's my financial health overview?\"";

        return [
            'answer' => $answer,
            'type' => 'info',
            'icon' => 'psychology',
        ];
    }
}
