<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
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
        $recentTransactions = $user->transactions()
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Calculate 30-day chart data
        $chartData = [];
        $today = Carbon::today();
        $thirtyDaysAgo = Carbon::today()->subDays(29);

        // Fetch all transactions in past 30 days
        $txs = $user->transactions()
            ->where('date', '>=', $thirtyDaysAgo->toDateString())
            ->where('type', 'expense')
            ->get();

        // Group expenses by date
        $daysMap = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $daysMap[$d->toDateString()] = 0;
        }

        foreach ($txs as $tx) {
            $txDate = Carbon::parse($tx->date)->toDateString();
            if (isset($daysMap[$txDate])) {
                $daysMap[$txDate] += $tx->amount;
            }
        }

        foreach ($daysMap as $date => $amount) {
            $carbonDate = Carbon::parse($date);
            $chartData[] = [
                'displayDate' => $carbonDate->format('M d'),
                'spend' => $amount
            ];
        }

        $debts = $user->debts()->get();
        $transactions = $user->transactions()->orderBy('date', 'desc')->limit(500)->get();
        $engine = new \App\Services\LocalAIEngine($summary, $debts, $transactions, 'tough_love');
        $insights = $engine->generateInsights();
        $topInsight = count($insights) > 0 ? $insights[0] : null;

        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';
        $accounts = $user->accounts()->orderBy('name', 'asc')->get();

        return view('dashboard', compact(
            'summary',
            'recentTransactions',
            'chartData',
            'currency',
            'locale',
            'topInsight',
            'accounts'
        ));
    }
}
