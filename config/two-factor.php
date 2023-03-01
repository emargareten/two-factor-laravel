<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Auth Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard should be used while
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | This value defines which model attribute should be considered as your
    | application's "username" field. Typically, this might be the email
    | address of the users, but you are free to change this value here.
    |
    */

    'username' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Home Path
    |--------------------------------------------------------------------------
    |
    | Here you may configure the path where users will get redirected after
    | successful authentication when the operations are successful and
    | the user is authenticated. You are free to change this value.
    |
    */

    'home' => '/home',

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Routes Prefix / Subdomain
    |--------------------------------------------------------------------------
    |
    | Here you may specify which prefix should be assigned to all two-factor
    | routes registered with the application. You may also change the
    | subdomain under which all the two-factor routes will be accessed.
    |
    */

    'prefix' => '',

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware should be assigned to the routes
    | that it registers with the application. If necessary, you may change
    | these middleware but typically this provided default is preferred.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Here you may specify the validation messages / translation keys that
    | should appear when an invalid code was submitted. You should not
    | use the translation methods as it is being used under the hood.
    |
    */
    'validation_messages' => [
        'invalid_code' => 'The provided code is invalid.',
        'invalid_recovery_code' => 'The provided recovery code is invalid.',
        'throttle' => 'Too many attempts. Please wait before retrying.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Here you may enable rate limiting when submitting the two-factor code.
    | If necessary, you may change the max attempts amount per minute or
    | turn off the rate-limiting entirely by setting limiter to false.
    |
    */

    'limiter' => true,

    'max_attempts' => 5,

    /*
    |--------------------------------------------------------------------------
    | Window
    |--------------------------------------------------------------------------
    | To avoid problems with clocks that are slightly out of sync, we do not
    | check against the current key only but also consider window keys each
    | from the past and future, each window key is a 30 second timespan.
    | Here you can set the window, 0 means no window, 1 means 1 key in
    | the past and 1 in the future, 2 means 2 keys in the past and 2
    | in the future, etc.
    |
    */

    // 'window' => 0,

];
