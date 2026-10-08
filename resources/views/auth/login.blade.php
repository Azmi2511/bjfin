<!DOCTYPE html>
<html lang="id" class="h-full bg-background">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Autentikasi BJFin - BUMDes Kuala Alam</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-background font-body-md text-on-surface min-h-screen flex items-center justify-center p-margin antialiased">

    <main
        class="w-full max-w-lg bg-surface-container-lowest rounded-xl shadow-[0_12px_32px_rgba(15,23,42,0.08)] p-space-xl border border-surface-container">
        <div class="flex flex-col w-full">

            <!-- Brand Header -->
            <div class="flex flex-col items-center text-center mb-space-lg">
                <div class="relative mb-space-md">
                    <div
                        class="w-20 h-20 rounded-full bg-surface-container-high p-2 shadow-md flex items-center justify-center overflow-hidden ring-2 ring-primary/20 bg-white">
                        <img src="{{ asset('assets/logo.png') }}" alt="Logo BUMDesa"
                            class="w-full h-full object-contain">
                    </div>
                    <span
                        class="absolute -bottom-1 -right-1 bg-secondary text-on-secondary rounded-full p-1 shadow-sm flex items-center justify-center">
                        <span class="material-symbols-outlined text-xs">check</span>
                    </span>
                </div>

                <span
                    class="bg-surface-container-high text-primary px-3 py-1 rounded-full font-label-sm text-label-sm uppercase tracking-wider mb-2 font-bold inline-flex items-center gap-1">
                    BJFin BUMDesa
                </span>

                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mb-1 font-bold">
                    BUMDes KUALA ALAM
                </h1>

                <p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm">
                    Sistem Pengelolaan Keuangan Terpadu
                </p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-space-md">
                @csrf

                <!-- Email Input -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between items-center">
                        <label for="email" class="font-label-md text-label-md text-on-surface font-semibold">
                            Alamat Email atau Nama Pengguna
                        </label>
                    </div>

                    <div class="relative flex items-center">
                        <span
                            class="material-symbols-outlined absolute left-3 text-on-surface-variant text-lg pointer-events-none">
                            alternate_email
                        </span>

                        <input id="email" name="email" type="text"
                            value="{{ old('email', 'bendahara.umum@kualaalam.desa.id') }}" required autofocus
                            placeholder="Email atau username (contoh: bendahara.umum / direktur)"
                            class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md pl-10 pr-3 py-2.5 rounded-lg border border-outline-variant/60 focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">
                    </div>

                    @error('email')
                        <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between items-center">
                        <label for="password" class="font-label-md text-label-md text-on-surface font-semibold">
                            Kata Sandi
                        </label>
                    </div>

                    <div class="relative">
                        <!-- Lock Icon -->
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">
                            lock
                        </span>

                        <!-- Password Input -->
                        <input id="password" name="password" type="password" required placeholder="••••••••••••"
                            class="w-full h-[44px] bg-surface-container-low text-on-surface font-body-md text-body-md pl-10 pr-12 rounded-lg border border-outline-variant/60 focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">

                        <!-- Toggle Password -->
                        <button type="button" id="togglePassword" aria-label="Tampilkan kata sandi"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-md text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">
                            <span id="passwordIcon" class="material-symbols-outlined text-[20px] leading-none">
                                visibility
                            </span>
                        </button>
                    </div>

                    @error('password')
                        <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded text-primary focus:ring-0 accent-primary cursor-pointer">
                        <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">
                            Ingat Saya
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full mt-2 py-3 px-space-lg rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-md text-headline-md font-bold shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                    <span>MASUK</span>
                </button>

                <!-- Quick Account Selector (Pilihan Cepat Masuk) -->
                <div class="mt-4 pt-3 border-t border-outline-variant/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                            Pilih Cepat Akun Masuk:
                        </span>
                        <span class="text-[10px] text-primary font-semibold">Sandi: password123</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="setQuickLogin('bendahara.umum@kualaalam.desa.id', 'password123')"
                            class="p-2 bg-surface-container-high hover:bg-primary/10 hover:border-primary border border-outline-variant/60 rounded-lg text-left transition font-semibold text-on-surface flex items-center gap-1.5 cursor-pointer">
                            <span>💼</span>
                            <span class="truncate">Bendahara Umum</span>
                        </button>
                        <button type="button" onclick="setQuickLogin('direktur@kualaalam.desa.id', 'password123')"
                            class="p-2 bg-surface-container-high hover:bg-primary/10 hover:border-primary border border-outline-variant/60 rounded-lg text-left transition font-semibold text-on-surface flex items-center gap-1.5 cursor-pointer">
                            <span>🏛️</span>
                            <span class="truncate">Direktur BUMDesa</span>
                        </button>
                        <button type="button" onclick="setQuickLogin('bendahara.wifi@kualaalam.desa.id', 'password123')"
                            class="p-2 bg-surface-container-high hover:bg-primary/10 hover:border-primary border border-outline-variant/60 rounded-lg text-left transition font-semibold text-on-surface flex items-center gap-1.5 cursor-pointer">
                            <span>📶</span>
                            <span class="truncate">Bendahara Wifi</span>
                        </button>
                        <button type="button" onclick="setQuickLogin('bendahara.usp@kualaalam.desa.id', 'password123')"
                            class="p-2 bg-surface-container-high hover:bg-primary/10 hover:border-primary border border-outline-variant/60 rounded-lg text-left transition font-semibold text-on-surface flex items-center gap-1.5 cursor-pointer">
                            <span>💰</span>
                            <span class="truncate">Bendahara USP</span>
                        </button>
                    </div>
                </div>
            </form>

            <div
                class="mt-space-lg pt-space-md border-t border-surface-container text-center text-xs text-on-surface-variant">
                <p>© {{ date('Y') }} BUMDesa Kuala Alam. Sistem Pengelolaan Keuangan Terpadu.</p>
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