<?php

namespace Emargareten\TwoFactor\Tests;

use Emargareten\TwoFactor\TwoFactorAuthenticatable;
use Illuminate\Foundation\Auth\User;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property array|null $two_factor_recovery_codes
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 *
 * @method static TestUser create(array $attributes = [])
 */
class TestUser extends User
{
    use TwoFactorAuthenticatable;

    protected $table = 'users';

    protected $guarded = [];
}
