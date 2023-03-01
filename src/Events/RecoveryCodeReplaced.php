<?php

namespace Emargareten\TwoFactor\Events;

use Illuminate\Queue\SerializesModels;

class RecoveryCodeReplaced
{
    use SerializesModels;

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
