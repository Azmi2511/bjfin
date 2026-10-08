<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-black text-slate-900 tracking-tight">Perbarui Kata Sandi</h2>
        <p class="text-xs text-slate-500 mt-1">Masukkan kata sandi baru Anda untuk akun BJ-Fin</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" />
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email', $request->email) }}" 
                   required 
                   autofocus 
                   autocomplete="username"
                   class="mt-1 w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 rounded-xl shadow-xs text-sm text-slate-900" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi Baru')" />
            <input id="password" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="new-password"
                   placeholder="Minimal 8 karakter"
                   class="mt-1 w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 rounded-xl shadow-xs text-sm text-slate-900" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" />
            <input id="password_confirmation" 
                   type="password" 
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password"
                   placeholder="Ulangi kata sandi baru"
                   class="mt-1 w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 rounded-xl shadow-xs text-sm text-slate-900" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                <span>Simpan Kata Sandi Baru</span>
                <i data-lucide="check" class="w-4 h-4"></i>
            </button>
        </div>
    </form>
</x-guest-layout>
