<?php

namespace Emargareten\TwoFactor;

use Emargareten\TwoFactor\Contracts\TwoFactorProvider as TwoFactorProviderContract;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TwoFactorProviderContract::class, function ($app) {
            return new TwoFactorProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });

        $this->app->bind(StatefulGuard::class, function () {
            return Auth::guard(config('two-factor.guard'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/two-factor.php', 'two-factor');

        $this->configurePublishing();
        $this->configureRateLimiting();
        $this->configureRoutes();

        $this->registerEventListeners();
    }

    /**
     * Configure the publishable resources offered by the package.
     */
    protected function configurePublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/two-factor.php' => config_path('two-factor.php'),
            ], 'two-factor-config');

            $this->publishes([
                __DIR__.'/../database/migrations/add_two_factor_columns_to_users_table.php' => database_path('migrations/'.date('Y_m_d_His').'_add_two_factor_columns_to_users_table.php'),
            ], 'two-factor-migrations');
        }
    }

    /**
     * Configure the routes offered by the application.
     */
    protected function configureRoutes(): void
    {
        if (TwoFactor::$registersRoutes) {
            Route::group([
                'domain' => config('two-factor.domain'),
                'prefix' => config('two-factor.prefix'),
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
            });
        }
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        if (! config('two-factor.limiter')) {
            return;
        }

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(config('two-factor.max_attempts'))
                ->by('two-factor:'.$request->session()->get('two-factor.login.id'))
                ->response(function (Request $request, array $headers) {
                    if ($request->wantsJson()) {
                        throw new ThrottleRequestsException('Too Many Attempts.', null, $headers);
                    }

                    $message = __(config('two-factor.validation_messages.throttle'));

                    throw ValidationException::withMessages([
                        'code' => [$message],
                        'recovery_code' => [$message],
                    ]);
                });
        });
    }

    protected function registerEventListeners(): void
    {
        Event::listen(Login::class, function () {
            $this->app['session']->forget('two-factor.login.id');
            $this->app['session']->forget('two-factor.login.remember');
        });
    }
}
