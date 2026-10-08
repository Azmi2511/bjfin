@extends('layouts.app')

@section('title', 'Profil Pengguna & Keamanan Akun - BJFin BUMDes Kuala Alam')

@section('header_title', 'Pengaturan Profil & Keamanan')

@section('content')

<div class="space-y-6">
    
    <!-- BUMDesa Crimson Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#C62828] to-[#8B1515] p-5 sm:p-6 text-white shadow-md">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 p-1 bg-white rounded-full border-2 border-amber-400 shadow-sm flex items-center justify-center shrink-0 text-[#C62828]">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-extrabold tracking-tight">Profil Pengguna & Keamanan Akun</h2>
                    </div>
                    <p class="text-xs text-red-100 font-medium">
                        Pengaturan akun pengguna, kata sandi, dan perlindungan keamanan bendahara.
                    </p>
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/20 text-xs text-white">
                <i data-lucide="lock" class="w-4 h-4 text-amber-300"></i>
                <span class="font-medium text-[11px]">Perlindungan Akun Resmi Pengurus BUMDesa</span>
            </div>
        </div>
    </div>

    <!-- Main Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 2xl:gap-8">
        
        <!-- LEFT: INFORMASI PROFIL & PASSWORD (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Profil Form Card -->
            <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-[#E5E0D8] bg-[#FAF8F5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-[#C62828]"></i>
                        <h2 class="text-sm font-bold text-slate-900">Informasi Profil Pengguna</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="p-5 space-y-4 text-xs">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap Petugas *</label>
                            <input type="text" name="name" value="{{ old('name', Auth::user()->name ?? Auth::user()->nama) }}" required 
                                   class="w-full px-3 py-2 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Email Kedinasan *</label>
                            <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required 
                                   class="w-full px-3 py-2 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-[#C62828] hover:bg-[#9A1B1B] text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- UPDATE KATA SANDI -->
            <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-[#E5E0D8] bg-[#FAF8F5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="key" class="w-4 h-4 text-[#C62828]"></i>
                        <h2 class="text-sm font-bold text-slate-900">Ganti Kata Sandi</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="p-5 space-y-4 text-xs">
                    @csrf
                    @method('put')

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kata Sandi Saat Ini *</label>
                        <input type="password" name="current_password" required 
                               class="w-full px-3 py-2 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kata Sandi Baru *</label>
                            <input type="password" name="password" required 
                                   class="w-full px-3 py-2 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru *</label>
                            <input type="password" name="password_confirmation" required 
                                   class="w-full px-3 py-2 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-[#1E293B] hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- RIGHT: KEAMANAN 2FA (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-[#D97706]"></i>
                        <h3 class="text-sm font-bold text-slate-900">Autentikasi Dua Langkah (2FA)</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#FFFBEB] text-[#D97706] border border-[#FDE68A] text-[10px] font-bold">
                        Rekomendasi
                    </span>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                    Perlindungan tambahan untuk akun pengurus menggunakan kode verifikasi dari aplikasi HP (seperti Google Authenticator).
                </p>

                <div class="p-3.5 bg-[#FAF8F5] rounded-xl border border-[#E5E0D8] space-y-2 text-xs">
                    <div class="flex items-center gap-2 text-[#15803D] font-bold">
                        <i data-lucide="check-circle" class="w-4 h-4 text-[#15803D]"></i>
                        <span>Status 2FA: {{ Auth::user()->two_factor_secret ? 'Aktif' : 'Belum Aktif' }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Setiap login dari perangkat baru akan memvalidasi kode keamanan agar akun terlindungi dari akses tanpa izin.
                    </p>
                </div>
            </div>

            <!-- Identitas BUMDesa Info Box -->
            <div class="bg-white p-5 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-2.5">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo BUMDesa" class="w-5 h-5 object-contain">
                    <span>BUMDesa Kuala Alam</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">
                    Aplikasi pembukuan keuangan resmi BUMDesa Kuala Alam, Kecamatan Bengkalis, Kabupaten Bengkalis, Riau.
                </p>
            </div>

        </div>

    </div>

</div>

@endsection
