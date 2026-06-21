<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use App\Services\LocalAIEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachController extends Controller
{
    private FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->setting || !$user->setting->has_completed_onboarding) {
            return redirect('/onboarding');
        }

        $personality = $request->query('personality', 'tough_love');
        if (!in_array($personality, ['encouraging', 'tough_love'])) {
            $personality = 'tough_love';
        }

        $summary = $this->financeService->getSummary($user);
        $debts = $user->debts()->get();
        $transactions = $user->transactions()->orderBy('date', 'desc')->limit(500)->get();

        $engine = new LocalAIEngine($summary, $debts, $transactions, $personality);
        $insights = $engine->generateInsights();

        $settings = $user->setting;
        $currency = $settings->currency ?? 'USD';
        $locale = $settings->locale ?? 'en-US';

        return view('coach', compact(
            'summary',
            'debts',
            'insights',
            'personality',
            'currency',
            'locale'
        ));
    }

    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $personality = $request->input('personality', 'tough_love');
        if (!in_array($personality, ['encouraging', 'tough_love'])) {
            $personality = 'tough_love';
        }

        $summary = $this->financeService->getSummary($user);
        $debts = $user->debts()->get();
        $transactions = $user->transactions()->orderBy('date', 'desc')->limit(500)->get();

        $engine = new LocalAIEngine($summary, $debts, $transactions, $personality);
        $result = $engine->askNova($request->input('question'));

        return response()->json($result);
    }
}
