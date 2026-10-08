<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Autentikasi Dua Faktor (2FA)') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Tingkatkan keamanan akun Anda menggunakan aplikasi authenticator seperti Google Authenticator atau Authy.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        @if (Auth::user()->hasTwoFactorEnabled())
            <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="font-semibold text-sm">Autentikasi Dua Faktor Aktif</p>
                    <p class="text-xs text-green-700">Akun Anda telah dilindungi dengan verifikasi keamanan dua langkah.</p>
                </div>
            </div>

            <!-- Display Recovery Codes -->
            @if (Auth::user()->two_factor_recovery_codes)
                <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h3 class="font-semibold text-sm text-gray-800 mb-2">Kode Pemulihan (Recovery Codes)</h3>
                    <p class="text-xs text-gray-600 mb-3">Simpan kode-kode pemulihan darurat ini di tempat aman. Setiap kode hanya dapat digunakan 1 kali jika Anda kehilangan akses ke aplikasi authenticator Anda.</p>
                    <div class="grid grid-cols-2 gap-2 font-mono text-xs bg-white p-3 border border-gray-300 rounded text-gray-700">
                        @foreach (Auth::user()->two_factor_recovery_codes as $code)
                            <div>{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Disable 2FA Form -->
            <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4 space-y-4">
                @csrf
                @method('DELETE')

                @if (Auth::user()->password)
                    <div>
                        <x-input-label for="disable_2fa_password" value="Konfirmasi Password untuk Nonaktifkan 2FA" />
                        <x-text-input id="disable_2fa_password" name="password" type="password" class="mt-1 block w-3/4" placeholder="Password Anda" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                @else
                    <input type="hidden" name="password" value="social_login_bypass" />
                @endif

                <x-danger-button>
                    {{ __('Nonaktifkan 2FA') }}
                </x-danger-button>
            </form>

        @elseif (Auth::user()->two_factor_secret && !Auth::user()->two_factor_confirmed_at)
            <!-- Setup & Confirm 2FA -->
            <div class="p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg space-y-4">
                <p class="font-semibold text-sm">Langkah Akhir: Pindai Kode QR dan Konfirmasi OTP</p>
                <p class="text-xs text-yellow-700">Buka Google Authenticator atau Authy di ponsel Anda, lalu pindai kode QR di bawah ini:</p>

                <div class="p-3 bg-white border border-gray-200 rounded inline-block">
                    {!! Auth::user()->two_factor_QrCodeSvg() !!}
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="confirm_code" value="Masukkan 6-Digit Kode OTP dari Aplikasi" />
                        <x-text-input id="confirm_code" name="code" type="text" inputmode="numeric" class="mt-1 block w-1/2 text-center font-mono text-lg tracking-widest" placeholder="123456" required autofocus />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>
                            {{ __('Konfirmasi & Aktifkan 2FA') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

        @else
            <!-- Enable 2FA Initial Button -->
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <x-primary-button>
                    {{ __('Aktifkan Autentikasi 2FA') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</section>
