<?php

namespace Emargareten\TwoFactor\Http\Controllers;

use Emargareten\TwoFactor\Contracts\TwoFactorChallengeViewResponse;
use Emargareten\TwoFactor\Http\Requests\TwoFactorChallengeRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class TwoFactorChallengeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected StatefulGuard $guard) {}

    /**
     * Show the two-factor authentication challenge view.
     */
    public function create(): TwoFactorChallengeViewResponse
    {
        $model = $this->guard->getProvider()->getModel();

        $challengedUser = session()->has('two-factor.login.id') && $model::find(session()->get('two-factor.login.id'));

        throw_unless($challengedUser, AuthenticationException::class);

        return app(TwoFactorChallengeViewResponse::class);
    }

    /**
     * Attempt to authenticate a new session using the two-factor authentication code.
     */
    public function store(TwoFactorChallengeRequest $request): RedirectResponse
    {
        $user = $request->challengedUser();

        $this->guard->login($user, $request->remember());

        $request->session()->regenerate();

        return redirect()->intended(config('two-factor.home'));
    }
}
