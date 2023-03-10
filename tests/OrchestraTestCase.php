<?php

namespace Emargareten\TwoFactor\Tests;

use App\Models\User;
use Emargareten\TwoFactor\ServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class OrchestraTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        require_once __DIR__.'/User.php';

        $app['migrator']->path(__DIR__.'/../database/migrations');

        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
