<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteController extends Controller
{
    /**
     * Allowed social authentication providers.
     */
    protected array $allowedProviders = ['google', 'github'];

    /**
     * Redirect to the specified OAuth provider.
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->allowedProviders, true)) {
            return redirect()->route('login')->withErrors(['social' => 'Provider login tidak didukung.']);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the OAuth provider.
     */
    public function handleProviderCallback(string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->allowedProviders, true)) {
            return redirect()->route('login')->withErrors(['social' => 'Provider login tidak didukung.']);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors(['social' => 'Gagal melakukan autentikasi dengan ' . ucfirst($provider) . '. ' . $e->getMessage()]);
        }

        // Find user by provider ID or email
        $user = User::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        if ($user) {
            // Update provider detail and avatar if needed
            $user->update([
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        } else {
            // Create new user
            $user = User::create([
                'nama' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Pengguna ' . ucfirst($provider),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(24)),
                'role' => 'bendahara_unit',
                'avatar' => $socialUser->getAvatar(),
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        // Check 2FA
        if ($user->hasTwoFactorEnabled()) {
            session(['auth.2fa.user_id' => $user->id]);
            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
