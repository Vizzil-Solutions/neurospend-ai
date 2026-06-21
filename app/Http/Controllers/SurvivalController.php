<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SurvivalController extends Controller
{
    private FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $summary = $this->financeService->getSummary($user);
        $settings = $user->setting;
        
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        $safeToSpend = $summary['safeToSpend'] ?? 0;
        $daysLeft = max(1, $summary['daysUntilPayday'] ?? 0);
        $dailyAllowance = max(0.0, $safeToSpend / $daysLeft);

        $progressPercentage = max(0, min(100, ((30 - $daysLeft) / 30) * 100));

        // Filter upcoming bills that are due before the next payday and not paid
        $nextPayday = Carbon::parse($summary['nextPayday']);
        $upcomingBills = $user->bills()
            ->where('is_active', true)
            ->where('is_paid', false)
            ->where('next_due_date', '<=', $nextPayday->toDateString())
            ->get();

        return view('survival', compact(
            'summary',
            'settings',
            'currency',
            'locale',
            'safeToSpend',
            'daysLeft',
            'dailyAllowance',
            'progressPercentage',
            'upcomingBills'
        ));
    }
}
