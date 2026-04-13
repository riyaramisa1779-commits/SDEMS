<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function __construct(protected PasswordService $passwordService) {}

    /**
     * Update the user's password (Breeze route: PUT /password).
     * Enforces strong policy + history tracking.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user   = $request->user();
        $errors = $this->passwordService->validate($validated['password'], $user);

        if (! empty($errors)) {
            return back()->withErrors(['password' => $errors], 'updatePassword');
        }

        $hashed = Hash::make($validated['password']);
        $user->update(['password' => $hashed]);
        $this->passwordService->recordPasswordChange($user, $hashed);

        activity('profile')->causedBy($user)->log('Password changed');

        return back()->with('status', 'password-updated');
    }
}
