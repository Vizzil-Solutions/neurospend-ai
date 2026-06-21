<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $accounts = $user->accounts()->orderBy('name', 'asc')->get();
        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        return view('accounts', compact('accounts', 'currency', 'locale'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'type' => 'required|string|in:checking,savings,credit,cash,investment,other',
            'balance' => 'required|numeric',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:10',
        ]);

        Auth::user()->accounts()->create([
            'name' => $request->name,
            'institution' => $request->institution,
            'type' => $request->type,
            'balance' => $request->balance,
            'color' => $request->color ?? '#8B5CF6',
            'icon' => $request->icon ?? '🏦',
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Liquidity stream initialized.');
    }

    public function update(Request $request, Account $account)
    {
        $this->authorizeUser($account);

        $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'type' => 'required|string',
            'balance' => 'required|numeric',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:10',
        ]);

        $account->update($request->only(['name', 'institution', 'type', 'balance', 'color', 'icon']));

        return redirect()->back()->with('success', 'Account re-calibrated.');
    }

    public function destroy(Account $account)
    {
        $this->authorizeUser($account);
        $account->delete();

        return redirect()->back()->with('success', 'Liquidity node terminated.');
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|integer',
            'to_account_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'fee' => 'nullable|numeric|min:0',
            'memo' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $source = $user->accounts()->find($request->from_account_id);
        $dest = $user->accounts()->find($request->to_account_id);

        if (!$source || !$dest) {
            return redirect()->back()->withErrors(['transfer' => 'Failed to resolve transaction trajectory nodes.']);
        }

        if ($source->id === $dest->id) {
            return redirect()->back()->withErrors(['transfer' => 'Cannot transfer to the same account.']);
        }

        $amount = (float)$request->amount;
        $fee = (float)($request->fee ?? 0);
        $totalDeduction = $amount + $fee;

        if ($source->balance < $totalDeduction) {
            return redirect()->back()->withErrors(['transfer' => 'Insufficient liquidity in source node.']);
        }

        \DB::transaction(function () use ($source, $dest, $amount, $fee, $request) {
            $source->decrement('balance', $totalDeduction);
            $dest->increment('balance', $amount);

            if ($fee > 0) {
                $source->transactions()->create([
                    'user_id' => Auth::id(),
                    'amount' => $fee,
                    'type' => 'expense',
                    'category' => 'Fees & Charges',
                    'description' => 'Transfer Fee: ' . ($request->memo ?? 'Internal Transfer'),
                    'date' => now()->toDateString(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Internal transfer executed successfully.');
    }

    private function authorizeUser(Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
