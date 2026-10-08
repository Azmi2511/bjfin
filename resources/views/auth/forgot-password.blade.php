<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-black text-stone-900 tracking-tight">Atur Ulang Kata Sandi</h2>
        <p class="text-xs text-stone-500 mt-1">Masukkan email terdaftar Anda untuk menerima tautan pemulihan kata sandi.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-xs font-semibold p-3 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       placeholder="nama@kualaalam.desa.id"
                       class="w-full pl-9 pr-3 py-2 border border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 rounded-xl shadow-xs text-sm text-slate-900 placeholder:text-slate-400" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                <span>Kirim Tautan Atur Ulang</span>
                <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline">
                Kembali ke halaman masuk
            </a>
        </div>
    </form>
</x-guest-layout>
