<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'BJFin BUMDesa Kuala Alam'))</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans selection:bg-emerald-700 selection:text-white" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-linear duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-900/80 backdrop-blur-xs z-50 lg:hidden" x-cloak></div>

    <!-- MAIN SIDEBAR NAVIGATION (Dark Slate #0F172A Theme with Logo Green & Gold Accents) -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 transition-transform duration-300 transform lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Sidebar Header / Brand Logo with Golden Accent Border matching logo outer rim -->
        <div class="h-16 px-5 flex items-center justify-between border-b border-slate-800/80 bg-slate-950/40">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 p-1 bg-white rounded-lg border-2 border-amber-400 shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo BUMDesa" class="w-7 h-7 object-contain">
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm text-white tracking-tight flex items-center gap-1 font-display">
                        BJFin <span class="text-amber-400 font-extrabold">BUMDesa</span>
                    </span>
                    <span class="text-[11px] text-emerald-400 font-medium tracking-wide">Kuala Alam Ledger</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 focus:outline-none">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- System SAK ETAP Badge -->
        <div class="px-4 py-3 bg-slate-950/40 border-b border-slate-800/60 flex items-center justify-between text-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Standar Akuntansi</span>
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                SAK ETAP
            </span>
        </div>

        <!-- Navigation Menu Items -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-xs font-medium">

            <!-- Section 1: Utama -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Utama</p>
                
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white font-semibold border-l-4 border-emerald-500 shadow-xs' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                    <span class="flex-1">Executive Dashboard</span>
                </a>

                <a href="{{ url('/validation') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ request()->is('validation*') ? 'bg-slate-800 text-white font-semibold border-l-4 border-emerald-500 shadow-xs' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-square" class="w-4 h-4 {{ request()->is('validation*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                        <span>Validasi Transaksi</span>
                    </div>
                    @if(($pendingValidationCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-950 font-mono shadow-xs">
                            {{ $pendingValidationCount }}
                        </span>
                    @endif
                </a>
            </div>

            <!-- Section 2: Pencatatan Keuangan -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Pencatatan Keuangan</p>
                
                <a href="{{ route('transaksi.unit-input') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ request()->routeIs('transaksi.unit*') ? 'bg-slate-800 text-white font-semibold border-l-4 border-emerald-500 shadow-xs' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i data-lucide="layers" class="w-4 h-4 {{ request()->routeIs('transaksi.unit*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                    <span class="flex-1">Input Unit Lapangan</span>
                </a>

                <a href="{{ route('transaksi.umum-input') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ request()->routeIs('transaksi.umum*') ? 'bg-slate-800 text-white font-semibold border-l-4 border-emerald-500 shadow-xs' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i data-lucide="book-open" class="w-4 h-4 {{ request()->routeIs('transaksi.umum*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                    <span class="flex-1">Kas & Memorial Pusat</span>
                </a>
            </div>

            <!-- Section 3: Laporan & Ekspor -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Laporan & Audit</p>
                
                <a href="{{ url('/reports') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ request()->is('reports*') ? 'bg-slate-800 text-white font-semibold border-l-4 border-emerald-500 shadow-xs' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 {{ request()->is('reports*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                        <span>Laporan Keuangan</span>
                    </div>
                </a>
            </div>

            <!-- Section 4: Pengaturan & Profil -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Pengaturan</p>
                
                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ request()->routeIs('profile*') ? 'bg-slate-800 text-white font-semibold border-l-4 border-emerald-500 shadow-xs' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i data-lucide="shield-check" class="w-4 h-4 {{ request()->routeIs('profile*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                    <span class="flex-1">Profil & Keamanan 2FA</span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Footer: User Info & Logout -->
        <div class="p-3 border-t border-slate-800/80 bg-slate-950/50">
            @auth
                <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded bg-emerald-700 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs border border-emerald-600/40">
                            {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-white truncate">{{ Auth::user()->nama ?? Auth::user()->name }}</span>
                            <span class="text-[10px] text-slate-400 capitalize truncate">{{ str_replace('_', ' ', Auth::user()->role ?? 'Bendahara') }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Keluar dari sistem" class="p-1.5 rounded-md text-slate-400 hover:text-red-400 hover:bg-slate-800 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-md text-xs transition shadow-xs">
                    <i data-lucide="log-in" class="w-4 h-4"></i> Masuk Sistem
                </a>
            @endauth
        </div>

    </aside>

    <!-- MAIN BODY CONTENT AREA -->
    <div class="lg:pl-64 flex flex-col min-h-screen">

        <!-- Top Header Navigation Bar -->
        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 px-4 sm:px-6 flex items-center justify-between shadow-2xs">
            
            <!-- Left: Mobile Toggle & Page Title -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-md text-slate-600 hover:bg-slate-100 transition focus:outline-none">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                        @yield('header_title', 'Sistem Keuangan BUMDesa Kuala Alam')
                    </h1>
                    <span class="hidden md:inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        SAK ETAP Compliant
                    </span>
                </div>
            </div>

            <!-- Right: Quick Actions & Profile Dropdown -->
            <div class="flex items-center gap-3">
                
                @if(($pendingValidationCount ?? 0) > 0)
                    <a href="{{ url('/validation') }}" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-900 border border-amber-300 rounded-md text-xs font-semibold hover:bg-amber-100 transition">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>{{ $pendingValidationCount }} Menunggu Validasi</span>
                    </a>
                @endif

                <a href="{{ route('transaksi.unit-input') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-md text-xs font-semibold shadow-2xs transition focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Catat Transaksi</span>
                </a>

                @auth
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-1 rounded-md hover:bg-slate-100 transition focus:outline-none">
                            <div class="w-8 h-8 rounded bg-slate-900 text-amber-400 font-bold flex items-center justify-center text-xs border border-slate-700 shadow-2xs">
                                {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'U', 0, 2)) }}
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-500"></i>
                        </button>

                        <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-cloak
                             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/50">
                                <p class="font-bold text-slate-900 truncate">{{ Auth::user()->nama ?? Auth::user()->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> Profil & Keamanan
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 font-medium text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

            </div>
        </header>

        <!-- Main Workspace Area -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 max-w-7xl w-full mx-auto space-y-6">
            @include('sweetalert::alert')

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Footer Bar -->
        <footer class="bg-white border-t border-slate-200 text-slate-500 text-xs py-4 px-4 sm:px-6 mt-auto">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo BUMDesa" class="w-4 h-4 object-contain">
                    <p>© {{ date('Y') }} BJFin BUMDesa Kuala Alam. SAK ETAP Financial Management System.</p>
                </div>
                <p class="text-[11px] text-slate-400">Kecamatan Bengkalis, Kabupaten Bengkalis, Riau</p>
            </div>
        </footer>

    </div>

    <!-- Lucide Icons & SweetAlert System -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            background: '#0F172A',
            color: '#F8FAFC',
            customClass: {
                popup: 'rounded-lg shadow-xl border border-slate-800 text-xs font-sans px-4 py-3',
                timerProgressBar: 'bg-emerald-500'
            }
        });

        window.BumdesAlert = {
            toastSuccess: function(msg) {
                Toast.fire({ icon: 'success', iconColor: '#10B981', title: msg });
            },
            toastError: function(msg) {
                Toast.fire({ icon: 'error', iconColor: '#EF4444', title: msg });
            },
            confirmApprove: function(callback) {
                Swal.fire({
                    title: 'Otorisasi Transaksi?',
                    text: 'Transaksi akan divalidasi dan jurnal umum seimbang (Double-Entry) dicatat otomatis.',
                    icon: 'question',
                    iconColor: '#059669',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Ya, Setujui Transaksi',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-xl shadow-2xl border border-slate-200 font-sans p-6 text-xs bg-white text-slate-900',
                        title: 'text-base font-bold text-slate-900',
                        confirmButton: 'rounded-md text-xs font-semibold px-4 py-2',
                        cancelButton: 'rounded-md text-xs font-semibold px-4 py-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed && typeof callback === 'function') {
                        callback();
                    }
                });
            },
            confirmReject: function(callback) {
                Swal.fire({
                    title: 'Tolak Transaksi Unit?',
                    text: 'Tuliskan catatan perbaikan untuk Bendahara Unit:',
                    input: 'textarea',
                    inputPlaceholder: 'Contoh: Bukti fisik nota kurang jelas / nominal tidak sesuai...',
                    icon: 'warning',
                    iconColor: '#DC2626',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Tolak Transaksi',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value || !value.trim()) {
                            return 'Catatan alasan penolakan wajib diisi!';
                        }
                    },
                    customClass: {
                        popup: 'rounded-xl shadow-2xl border border-slate-200 font-sans p-6 text-xs bg-white text-slate-900',
                        title: 'text-base font-bold text-slate-900',
                        input: 'rounded-md text-xs border border-slate-300 font-sans focus:border-emerald-600 focus:ring-emerald-600',
                        confirmButton: 'rounded-md text-xs font-semibold px-4 py-2',
                        cancelButton: 'rounded-md text-xs font-semibold px-4 py-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed && typeof callback === 'function') {
                        callback(result.value);
                    }
                });
            }
        };
    </script>

    @stack('scripts')
</body>
</html>
