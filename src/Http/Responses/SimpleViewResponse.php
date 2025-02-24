<?php

namespace Emargareten\TwoFactor\Http\Responses;

use Emargareten\TwoFactor\Contracts\TwoFactorChallengeRecoveryViewResponse;
use Emargareten\TwoFactor\Contracts\TwoFactorChallengeViewResponse;
use Illuminate\Contracts\Support\Responsable;

class SimpleViewResponse implements TwoFactorChallengeRecoveryViewResponse, TwoFactorChallengeViewResponse
{
    /**
     * Create a new response instance.
     *
     * @param  callable|string  $view
     * @return void
     */
    public function __construct(protected $view) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toResponse($request)
    {
        if (! is_callable($this->view) || is_string($this->view)) {
            return view($this->view, ['request' => $request]);
        }

        $response = call_user_func($this->view, $request);

        if ($response instanceof Responsable) {
            return $response->toResponse($request);
        }

        return $response;
    }
}
