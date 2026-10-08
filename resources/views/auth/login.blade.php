<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF8F5]">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Autentikasi BJFin - BUMDesa Kuala Alam</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FAF8F5] min-h-screen flex items-center justify-center p-4 antialiased text-slate-800 font-sans">

    <main class="w-full max-w-lg bg-white rounded-2xl shadow-sm p-6 sm:p-8 border border-[#E5E0D8]">
        <div class="flex flex-col w-full">

            <!-- Brand Header -->
            <div class="flex flex-col items-center text-center mb-6">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-white p-2 shadow-xs flex items-center justify-center overflow-hidden border-2 border-amber-500 ring-4 ring-amber-50">
                        <img src="{{ asset('assets/logo.png') }}" alt="Logo BUMDesa" class="w-full h-full object-contain">
                    </div>
                    <span class="absolute -bottom-1 -right-1 bg-[#15803D] text-white rounded-full p-1 shadow-xs flex items-center justify-center">
                        <span class="material-symbols-outlined text-xs">check</span>
                    </span>
                </div>

                <span class="bg-[#FFEBEE] text-[#C62828] px-3.5 py-1 rounded-full text-xs uppercase tracking-wider mb-2 font-bold inline-flex items-center gap-1 border border-red-200">
                    BJFin BUMDesa
                </span>

                <h1 class="text-xl sm:text-2xl text-slate-900 tracking-tight mb-1 font-extrabold">
                    BUMDesa KUALA ALAM
                </h1>

                <p class="text-xs text-slate-500 max-w-sm">
                    Sistem Pengelolaan Keuangan Desa Terpadu
                </p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
                @csrf

                <!-- Email Input -->
                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-xs font-semibold text-slate-700">
                        Alamat Email atau Nama Pengguna
                    </label>

                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-slate-400 text-lg pointer-events-none">
                            alternate_email
                        </span>

                        <input id="email" name="email" type="text"
                            value="{{ old('email', 'bendahara.umum@kualaalam.desa.id') }}" required autofocus
                            placeholder="Email atau username (contoh: bendahara.umum / direktur)"
                            class="w-full bg-[#FAF8F5] text-slate-900 text-xs pl-10 pr-3 py-2.5 rounded-xl border border-[#E5E0D8] focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828] focus:outline-none transition-all">
                    </div>

                    @error('email')
                        <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-xs font-semibold text-slate-700">
                        Kata Sandi
                    </label>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">
                            lock
                        </span>

                        <input id="password" name="password" type="password" required placeholder="••••••••••••"
                            class="w-full h-[42px] bg-[#FAF8F5] text-slate-900 text-xs pl-10 pr-12 rounded-xl border border-[#E5E0D8] focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828] focus:outline-none transition-all">

                        <button type="button" id="togglePassword" aria-label="Tampilkan kata sandi"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-[#C62828] hover:bg-slate-100 transition-colors">
                            <span id="passwordIcon" class="material-symbols-outlined text-[18px] leading-none">
                                visibility
                            </span>
                        </button>
                    </div>

                    @error('password')
                        <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded text-[#C62828] focus:ring-0 accent-[#C62828] cursor-pointer">
                        <span class="text-xs text-slate-600 font-medium">
                            Ingat Saya
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full mt-2 py-3 px-6 rounded-xl bg-[#C62828] hover:bg-[#9A1B1B] text-white font-bold text-xs shadow-xs hover:shadow-md transition-all flex items-center justify-center gap-2">
                    <span>MASUK KE SISTEM</span>
                </button>

                <!-- Quick Account Selector (Pilihan Cepat Masuk) -->
                <div class="mt-4 pt-3 border-t border-[#E5E0D8]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            Pilih Cepat Akun Masuk:
                        </span>
                        <span class="text-[10px] text-[#C62828] font-bold">Sandi: password123</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="setQuickLogin('bendahara.umum@kualaalam.desa.id', 'password123')"
                            class="p-2.5 bg-[#FAF8F5] hover:bg-[#FFEBEE] hover:border-[#C62828] border border-[#E5E0D8] rounded-xl text-left transition font-semibold text-slate-800 flex items-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-sm text-[#C62828]">account_balance_wallet</span>
                            <span class="truncate">Bendahara Umum</span>
                        </button>
                        <button type="button" onclick="setQuickLogin('direktur@kualaalam.desa.id', 'password123')"
                            class="p-2.5 bg-[#FAF8F5] hover:bg-[#FFEBEE] hover:border-[#C62828] border border-[#E5E0D8] rounded-xl text-left transition font-semibold text-slate-800 flex items-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-sm text-amber-600">corporate_fare</span>
                            <span class="truncate">Direktur BUMDes</span>
                        </button>
                        <button type="button" onclick="setQuickLogin('bendahara.wifi@kualaalam.desa.id', 'password123')"
                            class="p-2.5 bg-[#FAF8F5] hover:bg-[#FFEBEE] hover:border-[#C62828] border border-[#E5E0D8] rounded-xl text-left transition font-semibold text-slate-800 flex items-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-sm text-blue-600">wifi</span>
                            <span class="truncate">Bendahara Wifi</span>
                        </button>
                        <button type="button" onclick="setQuickLogin('bendahara.usp@kualaalam.desa.id', 'password123')"
                            class="p-2.5 bg-[#FAF8F5] hover:bg-[#FFEBEE] hover:border-[#C62828] border border-[#E5E0D8] rounded-xl text-left transition font-semibold text-slate-800 flex items-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-sm text-[#15803D]">savings</span>
                            <span class="truncate">Bendahara USP</span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-[#E5E0D8] text-center text-xs text-slate-400">
                <p>© {{ date('Y') }} BUMDesa Kuala Alam. Sistem Pengelolaan Keuangan Desa.</p>
            </div>

        </div>
    </main>

    <script>
        function setQuickLogin(email, password) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = password;
                emailInput.focus();
            }
        }

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            passwordIcon.textContent = isPassword ? 'visibility_off' : 'visibility';

            togglePassword.setAttribute(
                'aria-label',
                isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
            );
        });
    </script>

</body>

</html>