<?php

namespace Emargareten\TwoFactor\Contracts;

interface TwoFactorProvider
{
    /**
     * Generate a new secret key.
     */
    public function generateSecretKey(): string;

    /**
     * Get the current one time password for a key.
     */
    public function getCurrentOtp(string $secret): string;

    /**
     * Get the two factor authentication QR code URL.
     */
    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string;

    /**
     * Verify the given token.
     */
    public function verify(string $secret, string $code): bool;

    /**
     * Set the window.
     */
    public function setWindow(int $window): self;

    /**
     * Get the window.
     */
    public function getWindow(): ?int;
}
