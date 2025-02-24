<?php

namespace Emargareten\TwoFactor\Events;

use Illuminate\Foundation\Events\Dispatchable;

abstract class TwoFactorAuthenticationEvent
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function __construct(public $user) {}
}
