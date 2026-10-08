<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialiteAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_social_login_redirects_to_provider(): void
    {
        $response = $this->get(route('social.redirect', 'google'));

        $response->assertRedirect();
    }

    public function test_user_can_login_via_social_provider(): void
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('123456789');
        $abstractUser->shouldReceive('getEmail')->andReturn('social@kualaalam.desa.id');
        $abstractUser->shouldReceive('getName')->andReturn('Social User');
        $abstractUser->shouldReceive('getNickname')->andReturn('socialuser');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'social@kualaalam.desa.id',
            'provider_name' => 'google',
            'provider_id' => '123456789',
        ]);
    }
}
