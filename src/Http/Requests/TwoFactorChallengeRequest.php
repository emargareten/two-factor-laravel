<?php

namespace Emargareten\TwoFactor\Http\Requests;

use Closure;
use Emargareten\TwoFactor\Contracts\TwoFactorProvider;
use Emargareten\TwoFactor\Events\TwoFactorAuthenticationVerified;
use Emargareten\TwoFactor\Events\TwoFactorAuthenticationVerifying;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
{
    /**
     * The user attempting the two factor challenge.
     *
     * @var \App\Models\User|null
     */
    protected $challengedUser = null;

    /**
     * Indicates if the user wished to be remembered after login.
     */
    protected bool $remember;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if ($this->routeIs('two-factor-challenge-recovery.store')) {
            return [
                'recovery_code' => ['required', 'string', $this->hasValidRecoveryCode()],
            ];
        }

        return [
            'code' => ['required', 'string', $this->hasValidCode()],
        ];
    }

    /**
     * Determine if the request has a valid two-factor code.
     */
    public function hasValidCode(): Closure
    {
        return function ($attribute, $value, $fail) {
            if (! $value) {
                return;
            }

            TwoFactorAuthenticationVerifying::dispatch($this->challengedUser());

            $valid = app(TwoFactorProvider::class)->verify(
                $this->challengedUser()->two_factor_secret, $value
            );

            if (! $valid) {
                $fail(__(config('two-factor.validation_messages.invalid_code')));
            }

            TwoFactorAuthenticationVerified::dispatch($this->challengedUser());
        };
    }

    /**
     * Determine if the request has a valid two-factor recovery code.
     */
    public function hasValidRecoveryCode(): Closure
    {
        return function ($attribute, $value, $fail) {
            if (! $value) {
                return;
            }

            $valid = in_array($value, $this->challengedUser()->two_factor_recovery_codes);

            if (! $valid) {
                $fail(__(config('two-factor.validation_messages.invalid_recovery_code')));
            }
        };
    }

    /**
     * Get the user that is attempting the two factor challenge.
     *
     * @return \App\Models\User
     */
    public function challengedUser()
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        $model = app(StatefulGuard::class)->getProvider()->getModel();

        if (! $this->session()->has('two-factor.login.id') || ! $user = $model::find($this->session()->get('two-factor.login.id'))) {
            throw new AuthenticationException;
        }

        return $this->challengedUser = $user;
    }

    /**
     * Determine if the user wanted to be remembered after login.
     */
    public function remember(): bool
    {
        if (! isset($this->remember)) {
            $this->remember = $this->session()->pull('two-factor.login.remember', false);
        }

        return $this->remember;
    }
}
