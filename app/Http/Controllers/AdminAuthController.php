<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Enter your password.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The email or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        $destination = match (true) {
            $user->isSuperAdmin() || $user->hasPermission('dashboard') => route('dashboard'),
            $user->hasPermission('products') => route('admin.products.index'),
            $user->hasPermission('orders') => route('admin.orders.index', 'all'),
            $user->hasPermission('fake_orders') => route('admin.fake-orders.index', 'all'),
            $user->hasPermission('incomplete_orders') => route('admin.incomplete-orders.index', 'all'),
            $user->hasPermission('site_settings') => route('admin.landing.index'),
            $user->hasPermission('site_tracking') => route('admin.tracking.edit', 'visitors'),
            default => route('home'),
        };

        return redirect()->intended($destination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
