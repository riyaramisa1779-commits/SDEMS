<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Handles 2FA secret generation, QR codes, and verification.
 */
class TwoFactorService
{
    public function __construct(protected Google2FA $google2fa) {}

    /**
     * Generate a new secret key for the user (not yet confirmed).
     */
    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();

        $user->update([
            'two_factor_secret'           => encrypt($secret),
            'two_factor_recovery_codes'   => encrypt(json_encode($this->generateRecoveryCodes())),
            'two_factor_confirmed_at'     => null,
        ]);

        return $secret;
    }

    /**
     * Get the QR code SVG for the user's 2FA setup.
     */
    public function getQrCodeSvg(User $user): string
    {
        $secret = decrypt($user->two_factor_secret);

        $url = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return $this->generateQrSvg($url);
    }

    /**
     * Confirm 2FA setup by verifying the provided OTP code.
     */
    public function confirm(User $user, string $code): bool
    {
        $secret = decrypt($user->two_factor_secret);

        $valid = $this->google2fa->verifyKey($secret, $code);

        if ($valid) {
            $user->update(['two_factor_confirmed_at' => now()]);
        }

        return $valid;
    }

    /**
     * Verify an OTP code for an already-confirmed 2FA user.
     */
    public function verify(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        $secret = decrypt($user->two_factor_secret);

        return (bool) $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);
    }

    /**
     * Get decrypted recovery codes as a collection.
     */
    public function getRecoveryCodes(User $user): Collection
    {
        if (! $user->two_factor_recovery_codes) {
            return collect();
        }

        return collect(json_decode(decrypt($user->two_factor_recovery_codes), true));
    }

    /**
     * Generate 8 random recovery codes.
     */
    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5)) . '-' . Str::upper(Str::random(5)))
            ->all();
    }

    /**
     * Generate a simple QR code SVG using BaconQrCode.
     */
    private function generateQrSvg(string $url): string
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );

        return (new \BaconQrCode\Writer($renderer))->writeString($url);
    }
}
