<?php

namespace Emargareten\TwoFactor\Actions;

use Emargareten\TwoFactor\Events\RecoveryCodesGenerated;
use Emargareten\TwoFactor\RecoveryCode;
use Illuminate\Support\Collection;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param  \App\Models\User  $user
     */
    public function __invoke($user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all(),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
