<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $strategy = $request->query('strategy', 'avalanche');
        $debts = $user->debts()->orderBy('name', 'asc')->get();
        $accounts = $user->accounts()->orderBy('name', 'asc')->get();
        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        return view('debt', compact('debts', 'accounts', 'strategy', 'currency', 'locale'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:credit_card,loan,car_loan,mortgage',
            'original_amount' => 'required|numeric|min:0.01',
            'current_balance' => 'required|numeric|min:0.00',
            'interest_rate' => 'required|numeric|min:0.00',
            'minimum_payment' => 'required|numeric|min:0.00',
            'due_day' => 'required|integer|min:1|max:31',
            'exclude_from_balance' => 'nullable|boolean',
        ]);

        $exclude = $request->has('exclude_from_balance') || $request->exclude_from_balance;

        DB::transaction(function () use ($request, $exclude) {
            $user = Auth::user();
            $currency = $user->setting->currency ?? 'USD';

            $debt = $user->debts()->create([
                'name' => trim($request->name),
                'type' => $request->type,
                'original_amount' => $request->original_amount,
                'current_balance' => $request->current_balance,
                'interest_rate' => $request->interest_rate,
                'minimum_payment' => $request->minimum_payment,
                'due_day' => $request->due_day,
                'start_date' => Carbon::now()->toDateString(),
                'currency' => $currency,
                'exclude_from_balance' => $exclude,
                'is_paid_off' => $request->current_balance <= 0,
            ]);

            if (!$exclude) {
                $user->accounts()->create([
                    'name' => trim($request->name),
                    'type' => 'credit',
                    'balance' => $request->current_balance,
                    'institution' => 'Liability System',
                    'currency' => $currency,
                    'color' => '#8B5CF6',
                    'icon' => '💳',
                    'is_active' => $request->current_balance > 0,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Liability recorded successfully.');
    }

    public function pay(Request $request, Debt $debt)
    {
        if ($debt->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $sourceAcc = Account::findOrFail($request->account_id);
        if ($sourceAcc->user_id !== Auth::id()) {
            abort(403);
        }

        $amountToPay = (float)$request->payment_amount;

        DB::transaction(function () use ($debt, $sourceAcc, $amountToPay) {
            // 1. Update source account balance
            $sourceAcc->decrement('balance', $amountToPay);

            // 2. Log transaction
            $desc = $amountToPay >= $debt->current_balance ? "Full Payoff: {$debt->name}" : "Partial Payment: {$debt->name}";
            Auth::user()->transactions()->create([
                'account_id' => $sourceAcc->id,
                'amount' => $amountToPay,
                'type' => 'expense',
                'category' => 'Debt Repayment',
                'description' => $desc,
                'date' => Carbon::now()->toDateString(),
                'is_recurring' => false,
            ]);

            // 3. Update debt balance
            $newBalance = max(0.0, $debt->current_balance - $amountToPay);
            $debt->update([
                'current_balance' => $newBalance,
                'is_paid_off' => $newBalance <= 0,
            ]);

            // 4. Update corresponding credit account if it exists
            $creditAcc = Auth::user()->accounts()
                ->where('name', $debt->name)
                ->where('type', 'credit')
                ->first();

            if ($creditAcc) {
                $creditAcc->update([
                    'balance' => $newBalance,
                    'is_active' => $newBalance > 0
                ]);
            }
        });

        return redirect()->back()->with('success', 'Payment executed and liability balance reduced.');
    }

    public function update(Request $request, Debt $debt)
    {
        if ($debt->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:credit_card,loan,car_loan,mortgage',
            'original_amount' => 'required|numeric|min:0.01',
            'current_balance' => 'required|numeric|min:0.00',
            'interest_rate' => 'required|numeric|min:0.00',
            'minimum_payment' => 'required|numeric|min:0.00',
            'due_day' => 'required|integer|min:1|max:31',
            'exclude_from_balance' => 'nullable|boolean',
        ]);

        $exclude = $request->has('exclude_from_balance') || $request->exclude_from_balance;

        DB::transaction(function () use ($request, $debt, $exclude) {
            $oldName = $debt->name;
            
            $debt->update([
                'name' => trim($request->name),
                'type' => $request->type,
                'original_amount' => $request->original_amount,
                'current_balance' => $request->current_balance,
                'interest_rate' => $request->interest_rate,
                'minimum_payment' => $request->minimum_payment,
                'due_day' => $request->due_day,
                'exclude_from_balance' => $exclude,
                'is_paid_off' => $request->current_balance <= 0,
            ]);

            // Sync with corresponding credit account if it exists
            $creditAcc = Auth::user()->accounts()
                ->where('name', $oldName)
                ->where('type', 'credit')
                ->first();

            if ($exclude) {
                if ($creditAcc) {
                    $creditAcc->delete();
                }
            } else {
                if ($creditAcc) {
                    $creditAcc->update([
                        'name' => trim($request->name),
                        'balance' => $request->current_balance,
                        'is_active' => $request->current_balance > 0
                    ]);
                } else {
                    // Create if not existed previously
                    Auth::user()->accounts()->create([
                        'name' => trim($request->name),
                        'type' => 'credit',
                        'balance' => $request->current_balance,
                        'institution' => 'Liability System',
                        'currency' => $debt->currency ?? 'USD',
                        'color' => '#8B5CF6',
                        'icon' => '💳',
                        'is_active' => $request->current_balance > 0,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Liability updated successfully.');
    }

    public function destroy(Debt $debt)
    {
        if ($debt->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($debt) {
            // Delete corresponding credit account if it exists
            $creditAcc = Auth::user()->accounts()
                ->where('name', $debt->name)
                ->where('type', 'credit')
                ->first();

            if ($creditAcc) {
                $creditAcc->delete();
            }

            $debt->delete();
        });

        return redirect()->back()->with('success', 'Liability removed.');
    }
}
