<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable 2FA for the authenticated user (generate secret).
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (empty($user->two_factor_secret)) {
            $secret = $this->google2fa->generateSecretKey();
            $user->forceFill([
                'two_factor_secret' => encrypt($secret),
            ])->save();

            $user->generateTwoFactorRecoveryCodes();
        }

        return back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Confirm 2FA using code entered by the user.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        if (empty($user->two_factor_secret)) {
            return back()->withErrors(['code' => 'Autentikasi dua faktor belum diaktifkan.']);
        }

        $secret = decrypt($user->two_factor_secret);
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => ['Kode OTP yang dimasukkan tidak valid.'],
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('status', 'two-factor-authentication-confirmed');
    }

    /**
     * Disable 2FA for the authenticated user.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if ($user->password && !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password yang dimasukkan tidak cocok.'],
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-authentication-disabled');
    }

    /**
     * Show the 2FA challenge login view.
     */
    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (!session()->has('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Store and verify 2FA challenge input during login.
     */
    public function storeChallenge(Request $request): RedirectResponse
    {
        $userId = session('auth.2fa.user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $code = $request->input('code');
        $recoveryCode = $request->input('recovery_code');

        if (!empty($code)) {
            $secret = decrypt($user->two_factor_secret);
            $valid = $this->google2fa->verifyKey($secret, $code);

            if (!$valid) {
                throw ValidationException::withMessages([
                    'code' => ['Kode 2FA yang dimasukkan tidak valid.'],
                ]);
            }
        } elseif (!empty($recoveryCode)) {
            $codes = $user->two_factor_recovery_codes ?? [];
            if (!in_array($recoveryCode, $codes, true)) {
                throw ValidationException::withMessages([
                    'recovery_code' => ['Kode pemulihan (recovery code) tidak valid.'],
                ]);
            }

            // Remove used recovery code
            $user->forceFill([
                'two_factor_recovery_codes' => array_values(array_diff($codes, [$recoveryCode])),
            ])->save();
        } else {
            throw ValidationException::withMessages([
                'code' => ['Silakan masukkan kode 2FA atau kode pemulihan.'],
            ]);
        }

        $remember = session('auth.2fa.remember', false);
        session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
