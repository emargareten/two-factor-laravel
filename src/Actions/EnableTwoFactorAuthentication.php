<?php

namespace Emargareten\TwoFactor\Actions;

use Emargareten\TwoFactor\Contracts\TwoFactorProvider;
use Emargareten\TwoFactor\Events\TwoFactorAuthenticationEnabled;
use Emargareten\TwoFactor\Events\TwoFactorAuthenticationEnabling;
use Emargareten\TwoFactor\RecoveryCode;
use Illuminate\Support\Collection;

class EnableTwoFactorAuthentication
{
    /**
     * Create a new action instance.
     *
     * @return void
     */
    public function __construct(protected TwoFactorProvider $provider) {}

    /**
     * Enable two-factor authentication for the user.
     *
     * @param  \App\Models\User  $user
     */
    public function __invoke($user): void
    {
        TwoFactorAuthenticationEnabling::dispatch($user);

        $user->forceFill([
            'two_factor_secret' => $this->provider->generateSecretKey(),
            'two_factor_recovery_codes' => Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all(),
        ])->save();

        TwoFactorAuthenticationEnabled::dispatch($user);
    }
}
