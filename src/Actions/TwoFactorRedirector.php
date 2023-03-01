<?php

namespace Emargareten\TwoFactor\Actions;

use Emargareten\TwoFactor\Events\TwoFactorAuthenticationChallenged;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorRedirector
{
    protected bool $isRedirectingToChallenge = false;

    /**
     * Redirect to two-factor view.
     */
    public function redirect(Request $request): ?Response
    {
        $user = $request->user();

        if (! $user?->hasEnabledTwoFactorAuthentication()) {
            return redirect()->intended(config('two-factor.home'));
        }

        $this->isRedirectingToChallenge = true;

        return $this->twoFactorChallengeResponse($request, $user);
    }

    /**
     * Get the two-factor authentication enabled response.
     *
     * @param  \App\Models\User  $user
     */
    protected function twoFactorChallengeResponse(Request $request, $user): Response
    {
        app(StatefulGuard::class)->logout();

        $request->session()->put([
            'two-factor.login.id' => $user->getKey(),
            'two-factor.login.remember' => $request->boolean('remember'),
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect()->route('two-factor-challenge.create');
    }

    /**
     * Check whether redirect is redirecting to challenge
     */
    public function isRedirectingToChallenge(): bool
    {
        return $this->isRedirectingToChallenge;
    }
}
