<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\SurvivalController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\SettingsController;

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    Route::get('/auth/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/auth/register', [AuthController::class, 'register']);
    
    Route::get('/auth/forgot-password', [AuthController::class, 'showForgotPassword']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    
    Route::get('/auth/reset-password', [AuthController::class, 'showResetPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

// Auth Routes (Authenticated)
Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Onboarding Flow
    Route::get('/onboarding', [SettingsController::class, 'showOnboarding']);
    Route::post('/onboarding', [SettingsController::class, 'storeOnboarding']);

    // Main App (requires completed onboarding)
    Route::get('/', [DashboardController::class, 'index']);

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::post('/accounts/transfer', [AccountController::class, 'transfer']);
    Route::put('/accounts/{account}', [AccountController::class, 'update']);
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);
    
    // CSV Import
    Route::get('/import', [TransactionController::class, 'importView']);
    Route::post('/import', [TransactionController::class, 'importStore']);

    // Bills
    Route::get('/bills', [BillController::class, 'index']);
    Route::post('/bills', [BillController::class, 'store']);
    Route::put('/bills/{bill}', [BillController::class, 'update']);
    Route::patch('/bills/{bill}/auto-pay', [BillController::class, 'toggleAutoPay']);
    Route::post('/bills/{bill}/pay', [BillController::class, 'pay']);
    Route::delete('/bills/{bill}', [BillController::class, 'destroy']);

    // Debts
    Route::get('/debt', [DebtController::class, 'index']);
    Route::post('/debt', [DebtController::class, 'store']);
    Route::put('/debt/{debt}', [DebtController::class, 'update']);
    Route::post('/debt/{debt}/pay', [DebtController::class, 'pay']);
    Route::delete('/debt/{debt}', [DebtController::class, 'destroy']);

    // Survival Command
    Route::get('/survival', [SurvivalController::class, 'index']);
    Route::post('/settings/override', [SettingsController::class, 'saveOverride']);
    Route::post('/settings/override/clear', [SettingsController::class, 'clearOverride']);

    // Analysis
    Route::get('/analysis', [AnalysisController::class, 'index']);

    // AI Coach
    Route::get('/coach', [CoachController::class, 'index']);
    Route::post('/coach/ask', [CoachController::class, 'ask']);

    // System Settings
    Route::get('/settings', [SettingsController::class, 'showSettings']);
    Route::post('/settings', [SettingsController::class, 'updateSettings']);
    Route::post('/settings/nuke', [SettingsController::class, 'nuke']);
    Route::get('/settings/export', [SettingsController::class, 'exportBackup']);
    Route::post('/settings/import', [SettingsController::class, 'importBackup']);
});
