<?php

namespace Emargareten\TwoFactor;

use Emargareten\TwoFactor\Contracts\TwoFactorChallengeRecoveryViewResponse;
use Emargareten\TwoFactor\Contracts\TwoFactorChallengeViewResponse;
use Emargareten\TwoFactor\Http\Responses\SimpleViewResponse;

class TwoFactor
{
    /**
     * Indicates if two-factor authentication routes will be registered.
     */
    public static bool $registersRoutes = true;

    /**
     * Get the username used for authentication.
     */
    public static function username(): string
    {
        return config('two-factor.username', 'email');
    }

    /**
     * Specify which view should be used as the two-factor authentication challenge view.
     */
    public static function challengeView(callable|string $view): void
    {
        app()->singleton(TwoFactorChallengeViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the two-factor authentication challenge recovery view.
     */
    public static function challengeRecoveryView(callable|string $view): void
    {
        app()->singleton(TwoFactorChallengeRecoveryViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }
}
