<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BJFin BUMDesa Kuala Alam') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-800 bg-slate-900 antialiased min-h-screen flex flex-col justify-between selection:bg-emerald-700 selection:text-white">

    <!-- Ambient Background Radial Glow -->
    <div class="fixed inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-slate-900 to-slate-950 pointer-events-none"></div>

    <!-- Center Card Container -->
    <main class="flex-1 flex flex-col items-center justify-center px-4 sm:px-6 py-10 w-full relative z-10">
        <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-8 space-y-6">
            {{ $slot }}
        </div>
    </main>

    <!-- Dignified Footer -->
    <footer class="w-full max-w-[1720px] mx-auto px-4 sm:px-6 py-4 text-center text-xs text-slate-400 relative z-10 flex flex-col sm:flex-row items-center justify-between gap-2 border-t border-slate-800">
        <div class="flex items-center gap-2">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo BUMDesa" class="w-4 h-4 object-contain">
            <p>© {{ date('Y') }} BJFin BUMDesa Kuala Alam. Sistem Pengelolaan Keuangan Desa.</p>
        </div>
        <p class="text-[11px] text-slate-500">Kecamatan Bengkalis, Kabupaten Bengkalis, Riau</p>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
