<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('two-factor.enable'));

        $response->assertSessionHas('status', 'two-factor-authentication-enabled');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_user_can_confirm_two_factor_authentication(): void
    {
        $user = User::factory()->create();
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
        ])->save();

        $validOtpCode = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($user)->post(route('two-factor.confirm'), [
            'code' => $validOtpCode,
        ]);

        $response->assertSessionHas('status', 'two-factor-authentication-confirmed');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    public function test_user_with_2fa_enabled_is_prompted_for_challenge_during_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertEquals($user->id, session('auth.2fa.user_id'));
    }

    public function test_user_can_authenticate_using_2fa_otp_code(): void
    {
        $user = User::factory()->create();
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->withSession(['auth.2fa.user_id' => $user->id]);

        $otpCode = $google2fa->getCurrentOtp($secret);

        $response = $this->post(route('two-factor.challenge.store'), [
            'code' => $otpCode,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }
}
