<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginDevice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Check if account is locked before attempting auth
        $user = User::where('email', $request->email)->first();

        if ($user && $user->isLocked()) {
            $minutes = (int) now()->diffInMinutes($user->locked_until, false);

            return back()->withErrors([
                'email' => "Account locked. Try again in {$minutes} minute(s).",
            ])->onlyInput('email');
        }

        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Contact an administrator.',
            ])->onlyInput('email');
        }

        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Increment failed login counter
            if ($user) {
                $user->incrementFailedLogins();

                activity('auth')
                    ->causedBy($user)
                    ->withProperties(['ip' => $request->ip()])
                    ->log('Failed login attempt');
            }

            throw $e;
        }

        $request->session()->regenerate();

        /** @var User $authUser */
        $authUser = Auth::user();

        // Reset failed login counter on success
        $authUser->resetFailedLogins();

        // Track device/session
        LoginDevice::updateOrCreate(
            ['session_id' => $request->session()->getId()],
            [
                'user_id'        => $authUser->id,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'device_name'    => LoginDevice::parseDeviceName($request->userAgent() ?? ''),
                'last_active_at' => now(),
            ]
        );

        activity('auth')
            ->causedBy($authUser)
            ->withProperties(['ip' => $request->ip(), 'device' => $request->userAgent()])
            ->log('User logged in');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            // Remove device record for this session
            $user->loginDevices()
                ->where('session_id', $request->session()->getId())
                ->delete();

            activity('auth')
                ->causedBy($user)
                ->withProperties(['ip' => $request->ip()])
                ->log('User logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
