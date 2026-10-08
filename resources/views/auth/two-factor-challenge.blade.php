<x-guest-layout>
    <div class="mb-6 text-center">
        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-200 shadow-2xs">
            <i data-lucide="shield-alert" class="w-6 h-6"></i>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">Verifikasi Dua Faktor (2FA)</h2>
        <p class="text-xs text-slate-500 mt-1">Pengamanan Ekstra Akun Keuangan BJFin</p>
    </div>

    <div x-data="{ recovery: false }">
        <div class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 leading-relaxed" x-show="! recovery">
            Silakan masukkan 6-digit kode autentikasi OTP dari aplikasi pengautentikasi Anda (Google Authenticator / Authy).
        </div>

        <div class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 leading-relaxed" x-show="recovery" x-cloak>
            Silakan masukkan salah satu kode pemulihan (recovery code) darurat Anda untuk memulihkan akses.
        </div>

        <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="space-y-4">
            @csrf

            <!-- 6-Digit OTP Code -->
            <div x-show="! recovery">
                <x-input-label for="code" value="Kode 2FA / OTP" />
                <input id="code" 
                       type="text" 
                       inputmode="numeric" 
                       name="code" 
                       autofocus 
                       autocomplete="one-time-code" 
                       placeholder="123456" 
                       class="mt-1 w-full text-center tracking-widest text-2xl font-mono py-2.5 border border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 rounded-xl shadow-xs" />
                <x-input-error :messages="$errors->get('code')" class="mt-1.5 text-xs text-red-600 font-medium" />
            </div>

            <!-- Recovery Code -->
            <div x-show="recovery" x-cloak>
                <x-input-label for="recovery_code" value="Kode Pemulihan Darurat" />
                <input id="recovery_code" 
                       type="text" 
                       name="recovery_code" 
                       autocomplete="off" 
                       placeholder="xxxx-xxxx-xxxx" 
                       class="mt-1 w-full font-mono text-center text-sm py-2.5 border border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 rounded-xl shadow-xs" />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-1.5 text-xs text-red-600 font-medium" />
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="button" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline cursor-pointer"
                        x-show="! recovery"
                        x-on:click="recovery = true; $nextTick(() => { document.getElementById('recovery_code')?.focus() })">
                    Gunakan Kode Pemulihan
                </button>

                <button type="button" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline cursor-pointer"
                        x-show="recovery"
                        x-cloak
                        x-on:click="recovery = false; $nextTick(() => { document.getElementById('code')?.focus() })">
                    Gunakan Kode 2FA / OTP
                </button>

                <button type="submit" class="py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <span>Verifikasi</span>
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
