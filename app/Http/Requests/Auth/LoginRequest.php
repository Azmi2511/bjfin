<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $input = trim((string) $this->input('email'));
        $password = (string) $this->input('password');

        // Dukung alias populer seperti bendahara@kualaalam.desa.id -> bendahara.umum
        if ($input === 'bendahara@kualaalam.desa.id' || strtolower($input) === 'bendahara') {
            $input = 'bendahara.umum@kualaalam.desa.id';
        }

        // Cari akun pengguna secara fleksibel (email resmi, username tanpa domain, role, atau nama pengurus)
        $user = \App\Models\User::where('email', $input)->first()
            ?? \App\Models\User::where('email', $input . '@kualaalam.desa.id')->first()
            ?? \App\Models\User::where('role', str_replace(['.', '-'], '_', strtolower($input)))->first()
            ?? \App\Models\User::where('nama', 'like', "%{$input}%")->first();

        $credentials = [
            'email' => $user ? $user->email : $input,
            'password' => $password,
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Alamat email / nama pengguna atau kata sandi tidak cocok dengan data pembukuan.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
