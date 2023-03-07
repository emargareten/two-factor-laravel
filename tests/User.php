<?php

namespace App\Models;

use Emargareten\TwoFactor\TwoFactorAuthenticatable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
 * @property string|null $two_factor_method
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 *
 * @method static User create(array $attributes = [])
 */
class User extends Authenticatable
{
    use TwoFactorAuthenticatable;

    protected $table = 'users';

    protected $guarded = [];
}
