<?php

namespace Emargareten\TwoFactor;

use Emargareten\TwoFactor\Contracts\TwoFactorProvider as TwoFactorProviderContract;
use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorProvider implements TwoFactorProviderContract
{
    protected int $window;

    /**
     * Create a new two-factor authentication provider instance.
     *
     * @return void
     */
    public function __construct(protected Google2FA $engine, protected ?Repository $cache = null) {}

    /**
     * Generate a new secret key.
     */
    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    /**
     * Get the current one time password for a key.
     */
    public function getCurrentOtp(string $secret): string
    {
        return $this->engine->getCurrentOtp($secret);
    }

    /**
     * Get the two-factor authentication QR code URL.
     */
    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    /**
     * Verify the given code.
     */
    public function verify(string $secret, string $code): bool
    {
        if (is_int($window = $this->getWindow())) {
            $this->engine->setWindow($window);
        }

        $timestamp = $this->engine->verifyKeyNewer(
            $secret, $code, $this->cache?->get($key = 'two-factor.codes.'.md5($code))
        );

        if ($timestamp !== false) {
            if ($timestamp === true) {
                $timestamp = $this->engine->getTimestamp();
            }

            $this->cache?->put($key, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

            return true;
        }

        return false;
    }

    /**
     * Set the window.
     */
    public function setWindow(int $window): self
    {
        $this->window = $window;

        return $this;
    }

    /**
     * Get the window.
     */
    public function getWindow(): ?int
    {
        return $this->window ?? config('two-factor.window');
    }
}
