<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\PasswordService;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected PasswordService $passwordService,
        protected TwoFactorService $twoFactorService
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $qrCodeSvg     = null;
        $recoveryCodes = collect();

        // If 2FA secret exists but not yet confirmed, show QR
        if ($user->two_factor_secret && ! $user->hasTwoFactorEnabled()) {
            $qrCodeSvg = $this->twoFactorService->getQrCodeSvg($user);
        }

        if ($user->hasTwoFactorEnabled()) {
            $recoveryCodes = $this->twoFactorService->getRecoveryCodes($user);
        }

        $sessions = $user->loginDevices()->limit(5)->get();

        return view('profile.edit', compact('user', 'qrCodeSvg', 'recoveryCodes', 'sessions'));
    }

    /**
     * Update profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        activity('profile')
            ->causedBy($request->user())
            ->log('Profile updated');

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Change password with policy enforcement.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|confirmed',
        ]);

        $user   = $request->user();
        $errors = $this->passwordService->validate($request->password, $user);

        if (! empty($errors)) {
            return back()->withErrors(['password' => $errors])->withInput();
        }

        $hashed = Hash::make($request->password);

        $user->update(['password' => $hashed]);

        $this->passwordService->recordPasswordChange($user, $hashed);

        activity('profile')
            ->causedBy($user)
            ->log('Password changed');

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Initiate 2FA setup — generate secret and show QR.
     */
    public function enableTwoFactor(Request $request): RedirectResponse
    {
        $this->twoFactorService->generateSecret($request->user());

        return Redirect::route('profile.edit')->with('status', '2fa-setup');
    }

    /**
     * Confirm 2FA with OTP code.
     */
    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $confirmed = $this->twoFactorService->confirm($request->user(), $request->code);

        if (! $confirmed) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        activity('profile')
            ->causedBy($request->user())
            ->log('Two-factor authentication enabled');

        return Redirect::route('profile.edit')->with('status', '2fa-enabled');
    }

    /**
     * Disable 2FA.
     */
    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);

        $this->twoFactorService->disable($request->user());

        activity('profile')
            ->causedBy($request->user())
            ->log('Two-factor authentication disabled');

        return Redirect::route('profile.edit')->with('status', '2fa-disabled');
    }

    /**
     * Revoke a specific session/device.
     */
    public function revokeSession(Request $request, int $deviceId): RedirectResponse
    {
        $device = $request->user()->loginDevices()->findOrFail($deviceId);

        // Invalidate the session in the sessions table
        \DB::table('sessions')->where('id', $device->session_id)->delete();

        $device->delete();

        activity('profile')
            ->causedBy($request->user())
            ->withProperties(['session_id' => $device->session_id])
            ->log('Session revoked');

        return back()->with('status', 'session-revoked');
    }

    /**
     * Delete account (soft delete).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
