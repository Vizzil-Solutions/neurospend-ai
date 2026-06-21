<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class FinanceService
{
    public function getSummary(User $user): array
    {
        $accounts = $user->accounts()->where('is_active', true)->get();
        $bills = $user->bills()->where('is_active', true)->get();
        $debts = $user->debts()->where('is_paid_off', false)->get();
        $settings = $user->setting;

        if (!$settings) {
            // Default settings structure
            $settings = (object)[
                'currency' => 'USD',
                'locale' => 'en-US',
                'payday_freq' => 'monthly',
                'payday_date' => '1',
                'payday_override' => null,
            ];
        }

        $now = Carbon::now();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        
        $transactions = $user->transactions()
            ->where('date', '>=', $startOfMonth)
            ->get();

        $totalBalance = $accounts->sum('balance');
        
        $monthlyIncome = $transactions->where('type', 'income')->sum('amount');
        $monthlyExpenses = $transactions->where('type', 'expense')->sum('amount');
        $totalDebt = $debts->sum('current_balance');

        // Calculate Next Payday and Days Until Payday
        $daysUntilPayday = 0;
        $nextPayday = Carbon::now();
        $startOfToday = Carbon::now()->startOfDay();

        if (!empty($settings->payday_override) && Carbon::parse($settings->payday_override)->gte($startOfToday)) {
            $overrideDate = Carbon::parse($settings->payday_override)->startOfDay();
            $nextPayday = $overrideDate;
            $daysUntilPayday = (int)ceil($startOfToday->diffInDays($overrideDate, false));
        } else {
            $freq = $settings->payday_freq ?? 'monthly';
            $paydayDate = $settings->payday_date ?? '1';

            if ($freq === 'weekly') {
                $targetDay = (int)$paydayDate; // 1 = Monday, ..., 0 = Sunday
                $currentDay = $now->dayOfWeek; // Carbon day of week: 0 (Sunday) to 6 (Saturday)
                
                $daysToWait = $targetDay - $currentDay;
                if ($daysToWait <= 0) {
                    $daysToWait += 7;
                }
                $nextPayday = Carbon::now()->addDays($daysToWait)->startOfDay();
            } elseif ($freq === '15_days') {
                $day1 = (int)$paydayDate;
                $day2 = $day1 + 15 > 28 ? ($day1 - 15 > 0 ? $day1 - 15 : 28) : $day1 + 15;
                
                $d1 = Carbon::now()->day($day1)->startOfDay();
                if ($d1->lt($startOfToday)) {
                    $d1->addMonth();
                }
                
                $d2 = Carbon::now()->day($day2)->startOfDay();
                if ($d2->lt($startOfToday)) {
                    $d2->addMonth();
                }
                
                $nextPayday = $d1->lt($d2) ? $d1 : $d2;
            } else {
                // monthly
                $day = (int)$paydayDate;
                $target = Carbon::now()->day(min($day, 28))->startOfDay();
                if ($target->lt($startOfToday)) {
                    $target->addMonth();
                }
                $nextPayday = $target;
            }
            $daysUntilPayday = (int)ceil($startOfToday->diffInDays($nextPayday, false));
        }

        $nextPaydayStr = $nextPayday->toDateString();

        // Calculate upcoming bills due before next payday
        $upcomingBillsList = [];
        $upcomingBillsTotal = 0;

        foreach ($bills as $bill) {
            if ($bill->is_paid) {
                continue;
            }

            if ($bill->frequency === 'one_time') {
                $billDue = Carbon::parse($bill->next_due_date)->startOfDay();
            } elseif ($bill->frequency === 'yearly') {
                // Yearly bills: only count as obligation in their due month
                if ($bill->due_month && Carbon::now()->month !== $bill->due_month) {
                    continue;
                }
                $billDue = Carbon::create(Carbon::now()->year, $bill->due_month ?? Carbon::now()->month, min($bill->due_day, 28))->startOfDay();
                if ($billDue->lt($startOfToday)) {
                    $billDue->addYear();
                }
            } else {
                // Simple due date prediction: due_day in current or next month
                $billDue = Carbon::now()->day(min($bill->due_day, 28))->startOfDay();
                if ($billDue->lt($startOfToday)) {
                    $billDue->addMonth();
                }
            }

            if ($billDue->lt($nextPayday)) {
                $upcomingBillsList[] = $bill;
                $upcomingBillsTotal += $bill->amount;
            }
        }

        $safeToSpend = $totalBalance - $upcomingBillsTotal;

        return [
            'totalBalance' => $totalBalance,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpenses' => $monthlyExpenses,
            'upcomingBillsTotal' => $upcomingBillsTotal,
            'upcomingBills' => count($upcomingBillsList),
            'upcomingBillsList' => $upcomingBillsList,
            'safeToSpend' => $safeToSpend,
            'totalDebt' => $totalDebt,
            'activeDebts' => count($debts),
            'daysUntilPayday' => max(0, $daysUntilPayday),
            'nextPayday' => $nextPaydayStr,
        ];
    }
}
