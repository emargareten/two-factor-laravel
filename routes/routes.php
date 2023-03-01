<?php

use Emargareten\TwoFactor\Contracts\TwoFactorChallengeRecoveryViewResponse;
use Emargareten\TwoFactor\Contracts\TwoFactorChallengeViewResponse;
use Emargareten\TwoFactor\Http\Controllers\TwoFactorChallengeController;
use Emargareten\TwoFactor\Http\Controllers\TwoFactorChallengeRecoveryController;
use Illuminate\Support\Facades\Route;

$middleware = [
    ...config('two-factor.middleware', ['web']),
    'guest:'.config('two-factor.guard'),
];

Route::group(['middleware' => $middleware], function () {
    $challengeMiddleware = config('two-factor.limiter') ? 'throttle:two-factor' : null;

    if (app()->bound(TwoFactorChallengeViewResponse::class)) {
        Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
            ->name('two-factor-challenge.create');
    }

    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
        ->middleware($challengeMiddleware)
        ->name('two-factor-challenge.store');

    if (app()->bound(TwoFactorChallengeRecoveryViewResponse::class)) {
        Route::get('/two-factor-challenge-recovery', [TwoFactorChallengeRecoveryController::class, 'create'])
            ->name('two-factor-challenge-recovery.create');
    }

    Route::post('/two-factor-challenge-recovery', [TwoFactorChallengeRecoveryController::class, 'store'])
        ->middleware($challengeMiddleware)
        ->name('two-factor-challenge-recovery.store');
});
