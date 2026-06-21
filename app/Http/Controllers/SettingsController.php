<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Account;
use App\Models\Category;
use App\Models\Bill;
use App\Models\Transaction;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function showOnboarding()
    {
        $user = Auth::user();
        $setting = $user->setting;

        if ($setting && $setting->has_completed_onboarding) {
            return redirect('/');
        }

        return view('onboarding', compact('setting'));
    }

    public function storeOnboarding(Request $request)
    {
        $user = Auth::user();
        $setting = $user->setting;

        $step = (int)$request->input('step');

        if ($step === 2) {
            $request->validate([
                'account_name' => 'required|string|max:255',
                'account_balance' => 'required|numeric',
            ]);

            $user->accounts()->create([
                'name' => trim($request->account_name),
                'type' => 'checking',
                'balance' => $request->account_balance,
                'institution' => 'Primary Bank',
                'currency' => $setting->currency ?? 'USD',
                'color' => '#8B5CF6',
                'icon' => '🏦',
                'is_active' => true,
            ]);

            return response()->json(['success' => true]);
        } elseif ($step === 3) {
            $request->validate([
                'payday_freq' => 'required|string|in:monthly,weekly,15_days',
                'payday_date' => 'required|string',
                'payday_amount' => 'required|numeric|min:0',
            ]);

            $setting->update([
                'payday_freq' => $request->payday_freq,
                'payday_date' => $request->payday_date,
                'payday_amount' => $request->payday_amount,
            ]);

            return response()->json(['success' => true]);
        } elseif ($step === 4) {
            $setting->update([
                'has_completed_onboarding' => true
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Invalid step.'], 400);
    }

    public function showSettings()
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $settings = $user->setting;
        $currencies = [
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar'],
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound'],
            ['code' => 'PKR', 'symbol' => '₨', 'name' => 'Pakistani Rupee'],
            ['code' => 'INR', 'symbol' => '₹', 'name' => 'Indian Rupee'],
            ['code' => 'JPY', 'symbol' => '¥', 'name' => 'Japanese Yen'],
            ['code' => 'AUD', 'symbol' => 'A$', 'name' => 'Australian Dollar'],
            ['code' => 'CAD', 'symbol' => 'C$', 'name' => 'Canadian Dollar'],
            ['code' => 'AED', 'symbol' => 'د.إ', 'name' => 'UAE Dirham'],
            ['code' => 'SGD', 'symbol' => 'S$', 'name' => 'Singapore Dollar'],
        ];

        return view('settings', compact('settings', 'currencies'));
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $settings = $user->setting;

        if ($request->has('currency')) {
            $request->validate(['currency' => 'required|string|max:10']);
            $settings->update(['currency' => $request->currency]);
        }

        if ($request->has('payday_freq')) {
            $request->validate([
                'payday_freq' => 'required|string|in:monthly,weekly,15_days',
                'payday_date' => 'required|string',
                'payday_amount' => 'required|numeric|min:0',
            ]);

            $settings->update([
                'payday_freq' => $request->payday_freq,
                'payday_date' => $request->payday_date,
                'payday_amount' => $request->payday_amount,
            ]);
        }

        return redirect()->back()->with('success', 'System configuration updated successfully.');
    }

    public function saveOverride(Request $request)
    {
        $request->validate([
            'payday_override' => 'required|date'
        ]);

        Auth::user()->setting->update([
            'payday_override' => $request->payday_override
        ]);

        return redirect()->back()->with('success', 'Payday override saved.');
    }

    public function clearOverride()
    {
        Auth::user()->setting->update([
            'payday_override' => null
        ]);

        return redirect()->back()->with('success', 'Payday override cleared.');
    }

    public function nuke(Request $request)
    {
        $request->validate([
            'confirm' => 'required|string|in:NUKE',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($user) {
            $user->transactions()->delete();
            $user->accounts()->delete();
            $user->bills()->delete();
            $user->debts()->delete();
            
            $user->setting()->update([
                'currency' => 'USD',
                'locale' => 'en-US',
                'theme' => 'dark',
                'payday_freq' => 'monthly',
                'payday_date' => '1',
                'payday_amount' => 0,
                'payday_override' => null,
                'has_completed_onboarding' => false,
            ]);
        });

        return redirect('/onboarding');
    }

    public function exportBackup()
    {
        $user = Auth::user();
        
        $backup = [
            'settings' => [$user->setting],
            'accounts' => $user->accounts()->get(),
            'transactions' => $user->transactions()->get(),
            'categories' => $user->categories()->get(),
            'debts' => $user->debts()->get(),
            'bills' => $user->bills()->get(),
            'timestamp' => Carbon::now()->toIso8601String(),
        ];

        return response()->json($backup, 200, [
            'Content-Disposition' => 'attachment; filename="neurospend_backup_' . time() . '.json"'
        ]);
    }

    public function importBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json,txt',
        ]);

        $fileContent = file_get_contents($request->file('backup_file')->getRealPath());
        $data = json_decode($fileContent, true);

        if (!$data || !isset($data['transactions'])) {
            return redirect()->back()->withErrors(['backup_file' => 'Invalid backup file structure.']);
        }

        $user = Auth::user();

        DB::transaction(function () use ($user, $data) {
            // Nuke existing
            $user->transactions()->delete();
            $user->accounts()->delete();
            $user->bills()->delete();
            $user->debts()->delete();

            // Restore Settings
            if (isset($data['settings'][0])) {
                $s = $data['settings'][0];
                $user->setting()->update([
                    'currency' => $s['currency'] ?? 'USD',
                    'locale' => $s['locale'] ?? 'en-US',
                    'theme' => $s['theme'] ?? 'dark',
                    'payday_freq' => $s['payday_freq'] ?? 'monthly',
                    'payday_date' => $s['payday_date'] ?? '1',
                    'payday_amount' => $s['payday_amount'] ?? 0,
                    'payday_override' => $s['payday_override'] ?? null,
                    'has_completed_onboarding' => $s['has_completed_onboarding'] ?? false,
                ]);
            }

            // Restore Accounts
            if (isset($data['accounts'])) {
                foreach ($data['accounts'] as $acc) {
                    $user->accounts()->create([
                        'id' => $acc['id'] ?? null,
                        'name' => $acc['name'],
                        'institution' => $acc['institution'] ?? 'Imported',
                        'type' => $acc['type'] ?? 'checking',
                        'balance' => $acc['balance'] ?? 0,
                        'color' => $acc['color'] ?? '#8B5CF6',
                        'icon' => $acc['icon'] ?? '🏦',
                        'is_active' => $acc['is_active'] ?? true,
                    ]);
                }
            }

            // Restore Categories
            if (isset($data['categories'])) {
                foreach ($data['categories'] as $cat) {
                    $user->categories()->updateOrCreate(
                        ['name' => $cat['name'], 'user_id' => $user->id],
                        [
                            'icon' => $cat['icon'] ?? 'Category',
                            'color' => $cat['color'] ?? '#8B5CF6',
                            'type' => $cat['type'] ?? 'expense',
                            'sort_order' => $cat['sort_order'] ?? 0,
                        ]
                    );
                }
            }

            // Restore Bills
            if (isset($data['bills'])) {
                foreach ($data['bills'] as $bill) {
                    $user->bills()->create([
                        'id' => $bill['id'] ?? null,
                        'name' => $bill['name'],
                        'amount' => $bill['amount'],
                        'category' => $bill['category'],
                        'frequency' => $bill['frequency'] ?? 'monthly',
                        'due_day' => $bill['due_day'] ?? 1,
                        'is_auto_pay' => $bill['is_auto_pay'] ?? false,
                        'is_paid' => $bill['is_paid'] ?? false,
                        'is_variable' => $bill['is_variable'] ?? false,
                        'next_due_date' => $bill['next_due_date'] ?? null,
                        'is_active' => $bill['is_active'] ?? true,
                    ]);
                }
            }

            // Restore Debts
            if (isset($data['debts'])) {
                foreach ($data['debts'] as $debt) {
                    $user->debts()->create([
                        'id' => $debt['id'] ?? null,
                        'name' => $debt['name'],
                        'type' => $debt['type'] ?? 'credit_card',
                        'original_amount' => $debt['original_amount'] ?? 0,
                        'current_balance' => $debt['current_balance'] ?? 0,
                        'interest_rate' => $debt['interest_rate'] ?? 0,
                        'minimum_payment' => $debt['minimum_payment'] ?? 0,
                        'due_day' => $debt['due_day'] ?? 15,
                        'start_date' => $debt['start_date'] ?? null,
                        'exclude_from_balance' => $debt['exclude_from_balance'] ?? false,
                        'is_paid_off' => $debt['is_paid_off'] ?? false,
                    ]);
                }
            }

            // Restore Transactions
            if (isset($data['transactions'])) {
                foreach ($data['transactions'] as $tx) {
                    $user->transactions()->create([
                        'account_id' => $tx['account_id'],
                        'bill_id' => $tx['bill_id'] ?? null,
                        'amount' => $tx['amount'],
                        'type' => $tx['type'],
                        'category' => $tx['category'],
                        'date' => $tx['date'],
                        'description' => $tx['description'] ?? '',
                    ]);
                }
            }
        });

        return redirect('/')->with('success', 'Backups successfully restored.');
    }
}
