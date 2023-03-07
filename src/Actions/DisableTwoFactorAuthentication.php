<?php

namespace Emargareten\TwoFactor\Actions;

use Emargareten\TwoFactor\Events\TwoFactorAuthenticationDisabled;
use Emargareten\TwoFactor\Events\TwoFactorAuthenticationDisabling;

class DisableTwoFactorAuthentication
{
    /**
     * Disable two-factor authentication for the user.
     *
     * @param  \App\Models\User  $user
     */
    public function __invoke($user): void
    {
        TwoFactorAuthenticationDisabling::dispatch($user);

        if (
            ! is_null($user->two_factor_secret)
            || ! is_null($user->two_factor_recovery_codes)
            || ! is_null($user->two_factor_confirmed_at)
            || ! is_null($user->two_factor_method)
        ) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_method' => null,
            ])->save();

            TwoFactorAuthenticationDisabled::dispatch($user);
        }
    }
}
