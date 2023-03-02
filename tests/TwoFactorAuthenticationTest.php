<?php

namespace Emargareten\TwoFactor\Tests;

class TwoFactorAuthenticationTest extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    public function test_two_factor_authentication_can_be_enabled(): void
    {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
        ]);

        $user->enableTwoFactorAuthentication();

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_two_factor_authentication_can_be_confirmed(): void
    {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
        ]);

        $user->enableTwoFactorAuthentication();

        $user->confirmTwoFactorAuthentication($user->getCurrentOtp());

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    public function test_two_factor_authentication_can_be_confirmed_with_method(): void
    {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
        ]);

        $user->enableTwoFactorAuthentication();

        $user->confirmTwoFactorAuthentication($user->getCurrentOtp(), 'SMS');

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertEquals('SMS', $user->two_factor_method);
    }

    public function test_two_factor_authentication_can_be_disabled(): void
    {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'foo',
            'two_factor_recovery_codes' => ['bar', 'baz'],
            'two_factor_confirmed_at' => now(),
            'two_factor_method' => 'SMS',
        ]);

        $user->disableTwoFactorAuthentication();

        $user->refresh();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_method);
    }
}
