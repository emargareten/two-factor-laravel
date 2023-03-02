<?php

namespace Emargareten\TwoFactor\Actions;

use Emargareten\TwoFactor\Contracts\TwoFactorProvider;
use Emargareten\TwoFactor\Events\TwoFactorAuthenticationConfirmed;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    /**
     * Create a new action instance.
     *
     * @return void
     */
    public function __construct(protected TwoFactorProvider $provider)
    {
    }

    /**
     * Confirm the two-factor authentication configuration for the user.
     *
     * @param  \App\Models\User  $user
     */
    public function __invoke($user, string $code, ?string $method = null): void
    {
        if (empty($user->two_factor_secret)
            || empty($code)
            || ! $this->provider->verify($user->two_factor_secret, $code)
        ) {
            throw ValidationException::withMessages([
                'code' => [__(config('two-factor.validation_messages.invalid_code'))],
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_method' => $method,
        ])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
