@extends('layouts.app')

@section('title', 'Kelola Profil & Keamanan 2FA - BJFin BUMDes Kuala Alam')

@section('content')

<div class="flex flex-col w-full space-y-space-xl">
    
    <!-- Top Breadcrumb & Status Banner -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md bg-surface-container-low p-space-lg rounded-xl shadow-sm">
        <div class="flex items-center gap-space-md">
            <div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-on-primary shadow-sm">
                <span class="material-symbols-outlined text-[28px]">shield_person</span>
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-space-xs">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Sistem Finansial BJFin</span>
                    <span class="text-on-surface-variant font-label-sm">•</span>
                    <span class="font-label-sm text-label-sm text-primary font-semibold">SAK ETAP Tier 2 Security</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-bold">KELOLA PROFIL USER & KEAMANAN 2FA</h1>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Pengaturan Kredensial, Otorisasi Finansial, dan Kunci Enkripsi Akun Bendahara</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-space-sm">
            <div class="flex items-center gap-space-xs px-space-md py-space-xs rounded bg-surface-container-lowest text-on-surface shadow-sm">
                <span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Status Akun</span>
                    <span class="font-tabular-mono text-tabular-mono font-semibold text-secondary">TEROTORISASI</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg">
        
        <!-- LEFT: INFORMASI PROFIL PENGGUNA (7 Cols) -->
        <div class="lg:col-span-7 flex flex-col space-y-space-lg">
            
            <div class="bg-surface-container-lowest rounded-xl p-space-xl shadow-sm relative overflow-hidden border border-surface-container">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-primary"></div>
                
                <div class="flex items-center justify-between pb-space-md border-b border-surface-container">
                    <div class="flex items-center gap-space-sm">
                        <span class="material-symbols-outlined text-primary text-[22px]">badge</span>
                        <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Informasi Profil Pengguna</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-space-md mt-space-lg">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div>
                            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 font-semibold">Nama Lengkap Petugas *</label>
                            <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required class="w-full px-space-md py-2 text-body-md font-body-md bg-surface-container-low text-on-surface rounded-lg border border-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                        </div>
                        <div>
                            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 font-semibold">Email Kedinasan *</label>
                            <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required class="w-full px-space-md py-2 text-body-md font-body-md bg-surface-container-low text-on-surface rounded-lg border border-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                        </div>
                    </div>

                    <div class="pt-space-md flex justify-end">
                        <button type="submit" class="px-space-lg py-2.5 bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md font-bold rounded-lg shadow-sm transition-all flex items-center gap-space-xs">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- UPDATE KATA SANDI -->
            <div class="bg-surface-container-lowest rounded-xl p-space-xl shadow-sm relative overflow-hidden border border-surface-container">
                <div class="flex items-center gap-space-sm pb-space-md border-b border-surface-container">
                    <span class="material-symbols-outlined text-primary text-[22px]">lock_reset</span>
                    <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Pembaruan Kata Sandi Otoritas</h2>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-space-md mt-space-lg">
                    @csrf
                    @method('put')

                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant mb-1 font-semibold">Kata Sandi Saat Ini *</label>
                        <input type="password" name="current_password" required class="w-full px-space-md py-2 text-body-md font-body-md bg-surface-container-low text-on-surface rounded-lg border border-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div>
                            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 font-semibold">Kata Sandi Baru *</label>
                            <input type="password" name="password" required class="w-full px-space-md py-2 text-body-md font-body-md bg-surface-container-low text-on-surface rounded-lg border border-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                        </div>
                        <div>
                            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 font-semibold">Konfirmasi Kata Sandi Baru *</label>
                            <input type="password" name="password_confirmation" required class="w-full px-space-md py-2 text-body-md font-body-md bg-surface-container-low text-on-surface rounded-lg border border-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                        </div>
                    </div>

                    <div class="pt-space-md flex justify-end">
                        <button type="submit" class="px-space-lg py-2.5 bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md font-bold rounded-lg shadow-sm transition-all flex items-center gap-space-xs">
                            <span class="material-symbols-outlined text-[18px]">key</span>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- RIGHT: KEAMANAN 2FA (5 Cols) -->
        <div class="lg:col-span-5 flex flex-col space-y-space-lg">
            
            <div class="bg-surface-container-lowest rounded-xl p-space-xl shadow-sm border border-surface-container space-y-space-md">
                <div class="flex items-center justify-between border-b border-surface-container pb-space-sm">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-secondary text-[22px]">phonelink_lock</span>
                        <h3 class="font-headline-md text-headline-md text-on-surface font-bold">Autentikasi Dua Faktor (2FA)</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold">Direkomendasikan</span>
                </div>

                <p class="font-body-sm text-body-sm text-on-surface-variant">
                    Proteksi tingkat tinggi untuk akun otorisator laporan keuangan SAK ETAP menggunakan kode OTP berbasis aplikasi Authenticator.
                </p>

                <div class="p-space-md bg-surface-container-low rounded-xl space-y-2">
                    <div class="flex items-center gap-2 text-secondary font-bold text-sm">
                        <span class="material-symbols-outlined text-[18px]">verified_user</span>
                        <span>Status 2FA: {{ Auth::user()->two_factor_secret ? 'Aktif' : 'Belum Aktif' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@endsection
