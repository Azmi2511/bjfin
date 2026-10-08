<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-black text-slate-900 tracking-tight">Konfirmasi Keamanan</h2>
        <p class="text-xs text-slate-500 mt-1">Ini adalah area aman aplikasi BJFin. Harap konfirmasikan kata sandi Anda sebelum melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" />
            <input id="password" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="current-password"
                   placeholder="••••••••"
                   class="mt-1 w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 rounded-xl shadow-xs text-sm text-slate-900" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                <span>Konfirmasi Akses</span>
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </button>
        </div>
    </form>
</x-guest-layout>
