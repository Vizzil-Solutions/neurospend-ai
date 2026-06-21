<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalysisController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        $today = Carbon::today();
        
        // Date Ranges
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $startOfPrevMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfPrevMonth = Carbon::now()->subMonth()->endOfMonth();

        // Fetch Transactions
        $currentMonthTx = $user->transactions()
            ->where('date', '>=', $startOfMonth->toDateString())
            ->where('date', '<=', $endOfMonth->toDateString())
            ->get();

        $prevMonthTx = $user->transactions()
            ->where('date', '>=', $startOfPrevMonth->toDateString())
            ->where('date', '<=', $endOfPrevMonth->toDateString())
            ->get();

        // Expense & Income Splits
        $currentExpenses = $currentMonthTx->where('type', 'expense');
        $prevExpenses = $prevMonthTx->where('type', 'expense');
        $currentIncomes = $currentMonthTx->where('type', 'income');
        $prevIncomes = $prevMonthTx->where('type', 'income');

        $currentSpendTotal = $currentExpenses->sum('amount');
        $prevSpendTotal = $prevExpenses->sum('amount');
        $currentIncomeTotal = $currentIncomes->sum('amount');
        $prevIncomeTotal = $prevIncomes->sum('amount');

        // Month-over-Month Change
        $momChange = $prevSpendTotal > 0 ? (($currentSpendTotal - $prevSpendTotal) / $prevSpendTotal) * 100 : 0;
        $isSpendingUp = $momChange > 0;
        
        $todayDate = $today->day;
        $dailyAverage = $currentSpendTotal / max(1, $todayDate);

        // Category Breakdown
        $categoryBreakdown = [];
        $breakdownGrouped = $currentExpenses->groupBy('category');
        foreach ($breakdownGrouped as $catName => $items) {
            $categoryBreakdown[] = [
                'name' => $catName,
                'value' => $items->sum('amount')
            ];
        }
        usort($categoryBreakdown, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        // Comparison Data
        $comparisonData = [
            ['name' => 'Last Month', 'Income' => $prevIncomeTotal, 'Expense' => $prevSpendTotal],
            ['name' => 'This Month', 'Income' => $currentIncomeTotal, 'Expense' => $currentSpendTotal],
        ];

        // Trend Data
        $daysInMonth = $today->daysInMonth;
        $trendData = [];
        $cumulativeCurrent = 0;
        $cumulativePrev = 0;

        $currentDayMaps = [];
        foreach ($currentExpenses as $t) {
            $d = Carbon::parse($t->date)->day;
            $currentDayMaps[$d] = ($currentDayMaps[$d] ?? 0) + $t->amount;
        }

        $prevDayMaps = [];
        foreach ($prevExpenses as $t) {
            $d = Carbon::parse($t->date)->day;
            $prevDayMaps[$d] = ($prevDayMaps[$d] ?? 0) + $t->amount;
        }

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $cumulativeCurrent += ($currentDayMaps[$i] ?? 0);
            $cumulativePrev += ($prevDayMaps[$i] ?? 0);
            $isFuture = $i > $todayDate;
            
            $trendData[] = [
                'day' => $i,
                'This Month' => $isFuture ? null : $cumulativeCurrent,
                'Last Month' => $cumulativePrev
            ];
        }

        return view('analysis', compact(
            'currency',
            'locale',
            'currentSpendTotal',
            'prevSpendTotal',
            'currentIncomeTotal',
            'prevIncomeTotal',
            'momChange',
            'isSpendingUp',
            'dailyAverage',
            'categoryBreakdown',
            'comparisonData',
            'trendData',
            'todayDate'
        ));
    }
}
