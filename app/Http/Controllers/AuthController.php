<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->setting && $user->setting->has_completed_onboarding) {
                return redirect()->intended('/');
            }

            return redirect('/onboarding');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create default settings
            Setting::create([
                'user_id' => $user->id,
                'currency' => 'USD',
                'locale' => 'en-US',
                'theme' => 'dark',
                'has_completed_onboarding' => false,
            ]);

            // Seed default categories
            $defaultCategories = [
                ['name' => 'Housing', 'icon' => 'Home', 'color' => '#ef4444', 'type' => 'expense', 'sort_order' => 1],
                ['name' => 'Groceries', 'icon' => 'ShoppingCart', 'color' => '#f59e0b', 'type' => 'expense', 'sort_order' => 2],
                ['name' => 'Transportation', 'icon' => 'Car', 'color' => '#3b82f6', 'type' => 'expense', 'sort_order' => 3],
                ['name' => 'Dining', 'icon' => 'Utensils', 'color' => '#10b981', 'type' => 'expense', 'sort_order' => 4],
                ['name' => 'Utilities', 'icon' => 'Zap', 'color' => '#8b5cf6', 'type' => 'expense', 'sort_order' => 5],
                ['name' => 'Payroll', 'icon' => 'Briefcase', 'color' => '#10b981', 'type' => 'income', 'sort_order' => 6],
            ];

            foreach ($defaultCategories as $cat) {
                Category::create(array_merge($cat, [
                    'user_id' => $user->id,
                    'is_default' => true
                ]));
            }

            Auth::login($user);
        });

        return redirect('/onboarding');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/auth/login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        // Mocking for standard flow compatibility
        return back()->with('message', 'Reset link transmitted. Check your quantum mail node.');
    }

    public function showResetPassword()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        // Mock success
        return redirect('/auth/login')->with('message', 'Password decrypted and recalibrated. Sign in.');
    }
}
