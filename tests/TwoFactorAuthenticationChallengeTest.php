<?php

namespace Emargareten\TwoFactor\Tests;

use Emargareten\TwoFactor\Contracts\TwoFactorProvider;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationChallengeTest extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    public function test_two_factor_challenge_can_be_passed_via_code(): void
    {
        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => $userSecret,
        ]);

        $response = $this
            ->withSession([
                'two-factor.login.id' => $user->id,
                'two-factor.login.remember' => false,
            ])
            ->post('/two-factor-challenge', [
                'code' => $validOtp,
            ]);

        $response
            ->assertRedirect('/home')
            ->assertSessionMissing('two-factor.login.id');
    }

    public function test_two_factor_challenge_fails_for_old_otp_and_zero_window(): void
    {
        //Setting window to 0 should mean any old OTP is instantly invalid
        app(TwoFactorProvider::class)->setWindow(0);

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $currentTs = $tfaEngine->getTimestamp();
        $previousOtp = $tfaEngine->oathTotp($userSecret, $currentTs - 1);

        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => $userSecret,
        ]);

        $response = $this
            ->from('/two-factor-challenge')
            ->withSession([
                'two-factor.login.id' => $user->id,
                'two-factor.login.remember' => false,
            ])
            ->post('/two-factor-challenge', [
                'code' => $previousOtp,
            ]);

        $response
            ->assertRedirect('/two-factor-challenge')
            ->assertSessionHas('two-factor.login.id')
            ->assertSessionHasErrors(['code']);
    }

    public function test_two_factor_challenge_can_be_passed_via_recovery_code(): void
    {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
            'two_factor_recovery_codes' => ['invalid-code', 'valid-code'],
        ]);

        $response = $this
            ->from('/two-factor-challenge-recovery')
            ->withSession([
                'two-factor.login.id' => $user->id,
                'two-factor.login.remember' => false,
            ])
            ->post('/two-factor-challenge-recovery', [
                'recovery_code' => 'valid-code',
            ]);

        $response
            ->assertRedirect('/home')
            ->assertSessionMissing('two-factor.login.id');

        $this->assertAuthenticated();
        $this->assertNotContains('valid-code', $user->fresh()->two_factor_recovery_codes);
    }

    public function test_two_factor_challenge_can_fail_via_recovery_code(): void
    {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret'),
            'two_factor_recovery_codes' => ['invalid-code', 'valid-code'],
        ]);

        $response = $this
            ->from('/two-factor-challenge-recovery')
            ->withSession([
                'two-factor.login.id' => $user->id,
                'two-factor.login.remember' => false,
            ])
            ->post('/two-factor-challenge-recovery', [
                'recovery_code' => 'missing-code',
            ]);

        $response
            ->assertRedirect('/two-factor-challenge-recovery')
            ->assertSessionHas('two-factor.login.id')
            ->assertSessionHasErrors(['recovery_code']);

        $this->assertGuest();
    }

    public function test_two_factor_challenge_requires_a_challenged_user(): void
    {
        $response = $this->withSession([])->postJson('/two-factor-challenge', [
            'code' => 'code',
        ]);

        $response->assertUnauthorized();

        $this->assertGuest();
    }
}
