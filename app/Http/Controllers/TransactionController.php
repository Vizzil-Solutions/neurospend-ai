<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $accounts = $user->accounts()->orderBy('name', 'asc')->get();
        $transactions = $user->transactions()
            ->orderBy('date', 'desc')
            ->limit(100)
            ->get();
        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        return view('transactions', compact('accounts', 'transactions', 'currency', 'locale'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|string|in:income,expense',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $account = Account::findOrFail($request->account_id);
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($request, $account) {
            Auth::user()->transactions()->create([
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'type' => $request->type,
                'category' => trim($request->category),
                'date' => $request->date,
                'description' => trim($request->description ?? ''),
            ]);

            // Update account balance
            if ($request->type === 'income') {
                $account->increment('balance', $request->amount);
            } else {
                $account->decrement('balance', $request->amount);
            }
        });

        return redirect()->back()->with('success', 'Transaction successfully logged.');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $account = Account::findOrFail($transaction->account_id);

        DB::transaction(function () use ($transaction, $account) {
            // Reverse account balance change
            if ($transaction->type === 'income') {
                $account->decrement('balance', $transaction->amount);
            } else {
                $account->increment('balance', $transaction->amount);
            }

            $transaction->delete();
        });

        return redirect()->back()->with('success', 'Transaction deleted.');
    }

    public function importView()
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $accounts = $user->accounts()->orderBy('name', 'asc')->get();
        // Get existing transactions for duplicate check
        $transactions = $user->transactions()->get();
        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        return view('import', compact('accounts', 'transactions', 'currency', 'locale'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'transactions' => 'required|array',
            'transactions.*.account_id' => 'required|exists:accounts,id',
            'transactions.*.amount' => 'required|numeric|min:0.01',
            'transactions.*.type' => 'required|string|in:income,expense',
            'transactions.*.category' => 'required|string|max:255',
            'transactions.*.date' => 'required|date',
            'transactions.*.description' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->transactions as $item) {
                $account = Account::findOrFail($item['account_id']);
                if ($account->user_id !== Auth::id()) {
                    continue;
                }

                Auth::user()->transactions()->create([
                    'account_id' => $item['account_id'],
                    'amount' => $item['amount'],
                    'type' => $item['type'],
                    'category' => $item['category'],
                    'date' => $item['date'],
                    'description' => $item['description'],
                ]);

                if ($item['type'] === 'income') {
                    $account->increment('balance', $item['amount']);
                } else {
                    $account->decrement('balance', $item['amount']);
                }
            }
        });

        return response()->json(['message' => 'Import completed successfully.']);
    }
}
