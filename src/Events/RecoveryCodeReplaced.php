<?php

namespace Emargareten\TwoFactor\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RecoveryCodeReplaced
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function __construct(public $user, public string $code)
    {
    }
}
