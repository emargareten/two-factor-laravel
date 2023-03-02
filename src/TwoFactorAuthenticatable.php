<?php

namespace Emargareten\TwoFactor;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Emargareten\TwoFactor\Actions\ConfirmTwoFactorAuthentication;
use Emargareten\TwoFactor\Actions\DisableTwoFactorAuthentication;
use Emargareten\TwoFactor\Actions\EnableTwoFactorAuthentication;
use Emargareten\TwoFactor\Actions\GenerateNewRecoveryCodes;
use Emargareten\TwoFactor\Contracts\TwoFactorProvider;

trait TwoFactorAuthenticatable
{
    /**
     * Initialize two-factor authenticatable trait for an instance.
     */
    public function initializeTwoFactorAuthenticatable(): void
    {
        $this->mergeCasts([
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
        ]);
    }

    /**
     * Determine if two-factor authentication has been enabled.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Replace the given recovery code with a new one in the user's stored codes.
     */
    public function replaceRecoveryCode(string $code): void
    {
        $this->forceFill([
            'two_factor_recovery_codes' => array_map(
                fn ($recoveryCode) => $recoveryCode == $code ? RecoveryCode::generate() : $recoveryCode,
                $this->two_factor_recovery_codes
            ),
        ])->save();
    }

    /**
     * Get the QR code SVG of the user's two-factor authentication QR code URL.
     */
    public function twoFactorQrCodeSvg(): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd
            )
        ))->writeString($this->twoFactorQrCodeUrl());

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /**
     * Get the two-factor authentication QR code URL.
     */
    public function twoFactorQrCodeUrl(): string
    {
        return app(TwoFactorProvider::class)->qrCodeUrl(
            config('app.name'),
            $this->{TwoFactor::username()},
            $this->two_factor_secret
        );
    }

    /**
     * Get the current one time password for the user.
     */
    public function getCurrentOtp(): string
    {
        return app(TwoFactorProvider::class)->getCurrentOtp($this->two_factor_secret);
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enableTwoFactorAuthentication(): void
    {
        app(EnableTwoFactorAuthentication::class)($this);
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disableTwoFactorAuthentication(): void
    {
        app(DisableTwoFactorAuthentication::class)($this);
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactorAuthentication(string $code, ?string $method = null): void
    {
        app(ConfirmTwoFactorAuthentication::class)($this, $code, $method);
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function generateNewRecoveryCodes(): void
    {
        app(GenerateNewRecoveryCodes::class)($this);
    }
}
