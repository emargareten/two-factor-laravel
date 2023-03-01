<?php

namespace Emargareten\TwoFactor\Http\Controllers;

use Emargareten\TwoFactor\Contracts\TwoFactorChallengeRecoveryViewResponse;
use Emargareten\TwoFactor\Contracts\TwoFactorChallengeViewResponse;
use Emargareten\TwoFactor\Events\RecoveryCodeReplaced;
use Emargareten\TwoFactor\Http\Requests\TwoFactorChallengeRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class TwoFactorChallengeRecoveryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected StatefulGuard $guard)
    {
    }

    /**
     * Show the two-factor authentication challenge view.
     */
    public function create(): TwoFactorChallengeViewResponse
    {
        $model = $this->guard->getProvider()->getModel();

        $challengedUser = session()->has('two-factor.login.id') && $model::find(session()->get('two-factor.login.id'));

        throw_unless($challengedUser, AuthenticationException::class);

        return app(TwoFactorChallengeRecoveryViewResponse::class);
    }

    /**
     * Attempt to authenticate a new session using the two-factor authentication code.
     */
    public function store(TwoFactorChallengeRequest $request): RedirectResponse
    {
        $user = $request->challengedUser();

        $user->replaceRecoveryCode($request->input('recovery_code'));

        event(new RecoveryCodeReplaced($user, $request->input('recovery_code')));

        $this->guard->login($user, $request->remember());

        $request->session()->regenerate();

        return redirect()->intended(config('two-factor.home'));
    }
}
