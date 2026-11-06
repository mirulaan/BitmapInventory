<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request using username.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Check if user exists and if active
        $user = User::where('username', $credentials['username'])->first();

        if ($user && ! $user->is_active) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'This account has been deactivated. Please contact an administrator.',
                ]);
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('username', 'remember'))
            ->withErrors([
                'username' => 'These credentials do not match our records.',
            ]);
    }

    /**
     * Show the reset password using code form.
     */
    public function showResetPasswordForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password');
    }

    /**
     * Reset password using single-use superadmin-generated reset code.
     */
    public function resetPasswordWithCode(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'code' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('username', trim($request->username))->first();

        if (! $user) {
            return back()
                ->withInput($request->only('username', 'code'))
                ->withErrors(['username' => 'No account found with this username.']);
        }

        if (! $user->is_active) {
            return back()
                ->withInput($request->only('username', 'code'))
                ->withErrors(['username' => 'This account is deactivated. Please contact an administrator.']);
        }

        $inputCode = strtoupper(trim($request->code));
        $savedCode = strtoupper(trim((string) $user->password_reset_code));

        if (empty($savedCode) || $savedCode !== $inputCode) {
            return back()
                ->withInput($request->only('username', 'code'))
                ->withErrors(['code' => 'Invalid reset code. Please contact superadmin to generate a new reset code.']);
        }

        if (! $user->password_reset_code_expires_at || $user->password_reset_code_expires_at->isPast()) {
            return back()
                ->withInput($request->only('username', 'code'))
                ->withErrors(['code' => 'This reset code has expired. Reset codes are valid for 24 hours. Please contact superadmin.']);
        }

        // Update password and invalidate code (strictly single-use)
        $user->password = Hash::make($request->password);
        $user->password_reset_code = null;
        $user->password_reset_code_expires_at = null;
        $user->save();

        return redirect()->route('login')->with('status', 'Your password has been reset successfully. Please sign in with your new password.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out successfully.');
    }
}
