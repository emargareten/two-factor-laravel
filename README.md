# Two-Factor-Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/emargareten/two-factor-laravel.svg?style=flat-square)](https://packagist.org/packages/emargareten/two-factor-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/emargareten/two-factor-laravel/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/emargareten/two-factor-laravel/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/emargareten/two-factor-laravel/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/emargareten/two-factor-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/emargareten/two-factor-laravel.svg?style=flat-square)](https://packagist.org/packages/emargareten/two-factor-laravel)

Two-Factor-Laravel is a package that implements two-factor authentication for your Laravel apps.

If enabled, the user will be required to enter a six digit numeric token during the authentication process. This token is generated using a time-based one-time password (TOTP) that can be retrieved from any TOTP compatible mobile authentication application such as Google Authenticator.

You can also retrieve the current one-time password and send it to the user via SMS/email.

## Installation

First, install the package into your project using composer:

```bash
composer require emargareten/two-factor-laravel
```

Next, you should publish the configuration and migration files using the `vendor:publish` Artisan command:

```bash
php artisan vendor:publish --provider="Emargareten\TwoFactor\ServiceProvider"
```

Finally, you should run your application's database migrations. This will add the two-factor columns to the `users` table:

```bash
php artisan migrate
```

### Configuration

After publishing the assets, you may review the `config/two-factor.php` configuration file. This file contains several options that allow you to customize the behavior of the two-factor authentication features.

## Usage

To start using two-factor authentication, you should first add the `TwoFactorAuthenticatable` trait to your `User` model:

```php
use Emargareten\TwoFactor\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use TwoFactorAuthenticatable;
}
```

### Enabling Two-Factor Authentication

This package provides the logic for authenticating users using two-factor authentication. However, it is up to you to provide the user interface and controllers for enabling and disabling two-factor authentication.

To enable two-factor authentication for a user, you should call the `enableTwoFactorAuthentication` method on the user model. This will generate a secret key and recovery codes for the user and store them in the database (encrypted):

```php
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two-factor authentication for the user.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return back()->with('status', 'Two-factor authentication is already enabled');
        }

        $user->enableTwoFactorAuthentication();

        return redirect()->route('account.two-factor-authentication.confirm.show');
    }
}
```

### Confirming Two-Factor Authentication

After enabling two-factor authentication, the user must still "confirm" their two-factor authentication configuration by providing a valid two-factor authentication code. You should provide a way for the user to do this. For example, you could provide a view that displays the QR code and secret key for the user to scan into their authenticator app:
```php
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorAuthenticationConfirmationController extends Controller
{
    /**
     * Get the two-factor authentication confirmation view.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return back()->with('status', 'Two-factor authentication is already enabled');
        }

        if (! $user->two_factor_secret) {
            return back()->with('status', 'Two-factor authentication is not enabled');
        }

        return view('account.two-factor-confirmation.show', [
            'qrCodeSvg' => $user->twoFactorQrCodeSvg(),
            'setupKey' => $user->two_factor_secret,
        ]);
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $request->user()->confirmTwoFactorAuthentication($request->code);

        return redirect()
            ->route('account.two-factor-authentication.recovery-codes.index')
            ->with('status', 'Two-factor authentication successfully confirmed');
    }
}
```

If you prefer to use a different method for receiving the one-time password, i.e. SMS/email, you can use the `getCurrentOtp` method on the user model to retrieve the current one-time password:

```php
$user->getCurrentOtp();
```

> **Note**
> When sending the one-time-password via SMS/email, you should set the window to a higher value, to allow the user to enter the one-time password after it has been sent.

The `confirmTwoFactorAuthentication` method takes an optional second parameter to specify the two-factor method, this is totally optional, it can be useful if you have multiple methods for receiving the one-time password.

### Disabling Two-Factor Authentication

You should also provide a way for the user to disable two-factor authentication. This can be done by calling the `disableTwoFactorAuthentication` method on the user model:

```php
/**
 * Disable two-factor authentication for the user.
 */
public function destroy(Request $request): RedirectResponse
{
    $request->user()->disableTwoFactorAuthentication();

    return back()->with('status', 'Two-factor authentication disabled successfully');
}
```

Once the user has confirmed enabling two-factor authentication, each time they log in, they will be redirected to a page where they will be asked to enter a one-time password generated by their authenticator app.

```php
use Emargareten\TwoFactor\Actions\TwoFactorRedirector;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

public function login(Request $request, TwoFactorRedirector $redirector): Response
{
    // do login stuff...

    return $redirector->redirect($request);
}
```

This will redirect the user to the `two-factor-challenge.create` route.

The `TwoFactorAuthenticationChallenged` event will be fired if the user is being redirected to the two-factor challenge page, you can listen to this event to add additional logic, for example, you could send the one-time password via SMS/email:

```php
public function handle(TwoFactorAuthenticationChallenged $event): void
{
    $event->user->notify(new CompleteSignInOTP);
}
```

You will need to provide a view for the `two-factor-challenge.create` route. This view should contain a form where the user can enter the one-time password, you should bind the view in the `register` method of your `AppServiceProvider` by calling the `TwoFactor::challengeView()` method:

```php
/**
 * Register any application services.
 */
public function register(): void
{
    TwoFactor::challengeView('two-factor-challenge.create');
}
```

Or use a closure to generate a custom response:

```php
TwoFactor::challengeView(function (Request $request)  {
    return Inertia::render('TwoFactorChallenge/Create');
});
```

The form should be submitted to the `two-factor-challenge.store` route.

Once the user has entered a valid one-time password, he will be redirected to the intended URL (or to the home route defined in the config file if no intended URL was set).

### Recovery Codes

This package also provides the logic for generating and using recovery codes. Recovery codes can be used to access the application in case the user loses access to their authenticator app.

After enabling two-factor authentication, you should redirect the user to a page where they can view their recovery codes. You can also generate a fresh set of recovery codes by calling the `generateNewRecoveryCodes` method on the user model:

```php
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorAuthenticationRecoveryCodeController extends Controller
{
    /**
     * Get the two-factor authentication recovery codes for authenticated user.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasEnabledTwoFactorAuthentication()) {
            return back()->with('status', 'Two-factor authentication is disabled');
        }

        return view('two-factor-recovery-codes.index', [
            'recoveryCodes' => $request->user()->two_factor_recovery_codes,
        ]);
    }

    /**
     * Generate a fresh set of two-factor authentication recovery codes.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->hasEnabledTwoFactorAuthentication()) {
            return back()->with('status', 'Two-factor authentication is disabled');
        }

        $request->user()->generateNewRecoveryCodes();

        return redirect()->route('account.two-factor-authentication.recovery-codes.index');
    }
}
```

To use the recovery codes, you should add a view for the `two-factor-challenge-recovery.create` route. This view should contain a form where the user can enter a recovery code. You should bind the view in the `register` method of your `AppServiceProvider` by calling the `TwoFactor::challengeRecoveryView()` method:

The form should be submitted to the `two-factor-challenge-recovery.store` route.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [emargareten](https://github.com/emargareten)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
