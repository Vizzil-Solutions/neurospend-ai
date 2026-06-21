<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $bills = $user->bills()->orderBy('name', 'asc')->get();
        $accounts = $user->accounts()->orderBy('name', 'asc')->get();
        
        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        // Add history and avg helper computations directly to each bill object
        foreach ($bills as $bill) {
            $history = $user->transactions()
                ->where('bill_id', $bill->id)
                ->orderBy('date', 'desc')
                ->get();
            
            $bill->history = $history;
            $bill->average = $history->count() > 0 ? $history->avg('amount') : $bill->amount;
        }

        return view('bills', compact('bills', 'accounts', 'currency', 'locale'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'frequency' => 'required|string|in:monthly,weekly,yearly,one_time',
            'due_day' => 'nullable|integer|min:1|max:31',
            'due_date' => 'nullable|date',
            'due_month' => 'nullable|integer|min:1|max:12',
            'is_auto_pay' => 'nullable|boolean',
            'is_variable' => 'nullable|boolean',
        ]);

        $frequency = $request->frequency;
        
        if ($frequency === 'one_time') {
            $nextDueDate = Carbon::parse($request->due_date ?? Carbon::now()->toDateString())->startOfDay();
            $dueDay = $nextDueDate->day;
            $dueMonth = $nextDueDate->month;
        } else {
            $dueDay = (int)$request->due_day;
            $dueMonth = $frequency === 'yearly' ? (int)($request->due_month ?? 1) : null;

            if ($frequency === 'yearly' && $dueMonth) {
                // For yearly bills: set next due to specific month + day
                $nextDueDate = Carbon::create(Carbon::now()->year, $dueMonth, min($dueDay, 28))->startOfDay();
                if ($nextDueDate->lt(Carbon::now()->startOfDay())) {
                    $nextDueDate->addYear();
                }
            } else {
                $nextDueDate = Carbon::now()->day(min($dueDay, 28))->startOfDay();
                if ($nextDueDate->lt(Carbon::now()->startOfDay())) {
                    $nextDueDate->addMonth();
                }
            }
        }

        Auth::user()->bills()->create([
            'name' => trim($request->name),
            'amount' => $request->amount,
            'category' => trim($request->category),
            'frequency' => $frequency,
            'due_day' => $dueDay,
            'due_month' => $dueMonth,
            'is_auto_pay' => $request->has('is_auto_pay') || $request->is_auto_pay,
            'is_variable' => $request->has('is_variable') || $request->is_variable,
            'is_paid' => false,
            'next_due_date' => $nextDueDate->toDateString(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Obligation recorded successfully.');
    }

    public function update(Request $request, Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'frequency' => 'required|string',
            'due_day' => 'required|integer|min:1|max:31',
            'due_month' => 'nullable|integer|min:1|max:12',
        ]);

        $frequency = $request->frequency;
        $dueMonth = $frequency === 'yearly' ? (int)($request->due_month ?? 1) : null;

        $bill->update([
            'name' => trim($request->name),
            'amount' => $request->amount,
            'category' => trim($request->category),
            'frequency' => $frequency,
            'due_day' => $request->due_day,
            'due_month' => $dueMonth,
            'is_auto_pay' => $request->has('is_auto_pay'),
            'is_variable' => $request->has('is_variable'),
        ]);

        return redirect()->back()->with('success', 'Obligation updated.');
    }

    public function toggleAutoPay(Request $request, Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }

        $bill->update([
            'is_auto_pay' => !$bill->is_auto_pay
        ]);

        return response()->json(['success' => true]);
    }

    public function pay(Request $request, Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'pay_amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'pay_period' => 'nullable|string|max:255',
        ]);

        $account = Account::findOrFail($request->account_id);
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }

        $payAmount = (float)$request->pay_amount;

        DB::transaction(function () use ($bill, $account, $payAmount, $request) {
            // 1. Create transaction in ledger
            Auth::user()->transactions()->create([
                'account_id' => $account->id,
                'bill_id' => $bill->id,
                'amount' => $payAmount,
                'type' => 'expense',
                'category' => $bill->category,
                'description' => "Payment for {$bill->name}" . ($request->pay_period ? " ({$request->pay_period})" : ""),
                'date' => Carbon::now()->toDateString(),
                'is_recurring' => true,
            ]);

            // 2. Decrement account balance
            $account->decrement('balance', $payAmount);

            // 3. Recalculate planned amount for variable bills
            $updatedPlannedAmount = $bill->amount;
            if ($bill->is_variable) {
                $history = Auth::user()->transactions()
                    ->where('bill_id', $bill->id)
                    ->get();
                $allAmounts = $history->pluck('amount')->push($payAmount);
                $updatedPlannedAmount = $allAmounts->avg();
            }

            if ($bill->frequency === 'one_time') {
                $bill->update([
                    'is_paid' => true,
                    'is_active' => false,
                    'last_paid_date' => Carbon::now()->toDateString(),
                ]);
            } else {
                // 4. Update next due date
                $currentDue = Carbon::parse($bill->next_due_date);
                if ($bill->frequency === 'yearly') {
                    $nextDue = $currentDue->addYear()->toDateString();
                } else {
                    $nextDue = $currentDue->addMonth()->toDateString();
                }

                $bill->update([
                    'is_paid' => true,
                    'amount' => $updatedPlannedAmount,
                    'last_paid_date' => Carbon::now()->toDateString(),
                    'next_due_date' => $nextDue,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Payment logged and average recalculated.');
    }

    public function destroy(Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }

        $bill->delete();

        return redirect()->back()->with('success', 'Obligation removed from cycle.');
    }
}
