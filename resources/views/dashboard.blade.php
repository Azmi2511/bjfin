@extends('layouts.app')

@section('title', 'Executive Dashboard - BJFin BUMDesa Kuala Alam')

@section('header_title', 'Executive Dashboard Keuangan')

@section('content')

@php
    $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
@endphp

<!-- Mobile-Inspired Crimson Header Banner (Directly Matching bj_fin_mobile) -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#C62828] to-[#8B1515] p-5 sm:p-6 text-white shadow-md">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Left: Logo & Identity -->
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 p-1 bg-white rounded-full border-2 border-amber-400 shadow-sm flex items-center justify-center shrink-0">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo BUMDesa" class="w-8 h-8 object-contain">
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-extrabold tracking-tight">BJFin BUMDesa Kuala Alam</h2>
                </div>
                <p class="text-xs text-red-100 font-medium">
                    Selamat Datang, {{ Auth::user()->nama ?? Auth::user()->name ?? 'Bendahara' }}: Sistem Keuangan Digital BUMDesa
                </p>
            </div>
        </div>

        <!-- Right: Badge Strip -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/20 text-xs text-white">
            <i data-lucide="shield-check" class="w-4 h-4 text-amber-300"></i>
            <span class="font-medium text-[11px]">Standar Pembukuan Resmi: Laporan Keuangan Otomatis & Terverifikasi</span>
        </div>

    </div>
</div>

<!-- Header Section & Filter Control Bar -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#E5E0D8] shadow-xs">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#FFEBEE] border border-red-200 flex items-center justify-center text-[#C62828] shrink-0">
            <i data-lucide="line-chart" class="w-5 h-5"></i>
        </div>
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Ringkasan Finansial BUMDesa</h2>
            <p class="text-xs text-slate-500 font-medium">
                {{ $bulan ? 'Periode ' . ($namaBulan[$bulan] ?? '') . ' ' . $tahun : 'Konsolidasi Tahunan (' . $tahun . ')' }}
            </p>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2 text-xs">
        <!-- Filter Tahun -->
        <div class="flex items-center gap-1.5 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl px-3 py-1.5">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#C62828]"></i>
            <span class="text-slate-600 font-medium">Tahun:</span>
            <select name="tahun" onchange="this.form.submit()" class="bg-transparent font-bold text-slate-900 border-none p-0 focus:ring-0 cursor-pointer text-xs">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Bulan -->
        <div class="flex items-center gap-1.5 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl px-3 py-1.5">
            <i data-lucide="filter" class="w-3.5 h-3.5 text-[#C62828]"></i>
            <span class="text-slate-600 font-medium">Bulan:</span>
            <select name="bulan" onchange="this.form.submit()" class="bg-transparent font-bold text-slate-900 border-none p-0 focus:ring-0 cursor-pointer text-xs">
                <option value="all" {{ is_null($bulan) ? 'selected' : '' }}>Semua Bulan (Setahun)</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ $namaBulan[$m] }}
                    </option>
                @endfor
            </select>
        </div>

        <!-- Filter Unit Usaha -->
        <div class="flex items-center gap-1.5 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl px-3 py-1.5">
            <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#C62828]"></i>
            <span class="text-slate-600 font-medium">Unit:</span>
            <select name="id_unit" onchange="this.form.submit()" class="bg-transparent font-bold text-slate-900 border-none p-0 focus:ring-0 cursor-pointer text-xs">
                <option value="all" {{ is_null($selectedUnitId) ? 'selected' : '' }}>Semua Unit Usaha</option>
                @foreach($units as $u)
                    <option value="{{ $u->id_unit }}" {{ $selectedUnitId == $u->id_unit ? 'selected' : '' }}>
                        {{ $u->nama_unit }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedUnitId || $bulan)
            <a href="{{ route('dashboard') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition text-xs border border-[#E5E0D8]">
                Reset Filter
            </a>
        @endif
    </form>
</div>

<!-- Friendly Summary Card (Directly Matching Mobile _buildFriendlySummaryCard) -->
<div class="bg-white p-6 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-[#FFEBEE] text-[#C62828] flex items-center justify-center shrink-0">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Saldo Kas & Bank (Tersedia)</h3>
                <p class="text-xs text-slate-500">Total uang kas tunai dan saldo di rekening bank BUMDesa</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#FFEBEE] text-[#9A1B1B] text-xs font-bold w-fit">
            <i data-lucide="badge-check" class="w-3.5 h-3.5 text-[#C62828]"></i> Saldo Kas Terverifikasi
        </span>
    </div>

    <!-- Main Large Saldo Number in Crimson Red -->
    <div>
        <span class="text-3xl sm:text-4xl font-extrabold text-[#C62828] font-mono tracking-tight">
            Rp {{ number_format(($saldoKasBank ?? ($totalUangMasuk - $totalUangKeluar)), 0, ',', '.') }}
        </span>
    </div>

    <hr class="border-[#E5E0D8]">

    <!-- 3 Sub-Cards: Uang Masuk, Uang Keluar, Surplus SHU -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        
        <!-- Sub-Card 1: Uang Masuk -->
        <div class="p-4 rounded-xl bg-[#F0FDF4] border border-[#BBF7D0] space-y-1">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                    <i data-lucide="arrow-down-left" class="w-4 h-4 text-[#15803D]"></i> Uang Masuk
                </span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#15803D] bg-emerald-100/60 px-2 py-0.5 rounded-full">Penerimaan</span>
            </div>
            <p class="text-lg font-bold font-mono text-[#15803D]">
                Rp {{ number_format($totalUangMasuk ?? $income['total_pendapatan'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-slate-500 font-mono">{{ $totalTxCount }} Transaksi Pembukuan</p>
        </div>

        <!-- Sub-Card 2: Uang Keluar -->
        <div class="p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] space-y-1">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-[#DC2626]"></i> Uang Keluar
                </span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#DC2626] bg-red-100/60 px-2 py-0.5 rounded-full">Pengeluaran</span>
            </div>
            <p class="text-lg font-bold font-mono text-[#DC2626]">
                Rp {{ number_format($totalUangKeluar ?? $income['total_beban'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-slate-500">Biaya & Operasional</p>
        </div>

        <!-- Sub-Card 3: Surplus / SHU (Warm Gold Accent) -->
        <div class="p-4 rounded-xl bg-[#FFFBEB] border border-[#FDE68A] space-y-1">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                    <i data-lucide="trending-up" class="w-4 h-4 text-[#D97706]"></i> Hasil Usaha Bersih
                </span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#D97706] bg-amber-100/60 px-2 py-0.5 rounded-full">SHU</span>
            </div>
            <p class="text-lg font-bold font-mono {{ ($hasilUsahaBersih ?? $income['laba_bersih'] ?? 0) >= 0 ? 'text-[#15803D]' : 'text-[#DC2626]' }}">
                Rp {{ number_format($hasilUsahaBersih ?? $income['laba_bersih'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] font-semibold {{ ($hasilUsahaBersih ?? $income['laba_bersih'] ?? 0) >= 0 ? 'text-[#15803D]' : 'text-[#DC2626]' }}">
                {{ ($hasilUsahaBersih ?? $income['laba_bersih'] ?? 0) >= 0 ? 'Surplus Operasional' : 'Defisit Operasional' }}
            </p>
        </div>

    </div>
</div>

<!-- Quick Action Buttons Row -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 2xl:gap-5">
    <a href="{{ route('transaksi.umum-input') }}" 
       class="flex items-center justify-center gap-2 p-3.5 2xl:p-4 rounded-xl bg-[#C62828] hover:bg-[#9A1B1B] text-white font-bold text-xs shadow-xs transition focus:ring-2 focus:ring-[#C62828]">
        <i data-lucide="plus-circle" class="w-4 h-4"></i>
        <span>Catat Kas Pusat</span>
    </a>

    <a href="{{ url('/validation') }}" 
       class="flex items-center justify-center gap-2 p-3.5 2xl:p-4 rounded-xl bg-white hover:bg-[#FAF8F5] text-slate-800 font-bold text-xs border border-[#E5E0D8] shadow-xs transition hover:border-[#C62828]">
        <i data-lucide="check-square" class="w-4 h-4 text-[#D97706]"></i>
        <span>Persetujuan Transaksi</span>
        @if(($pendingValidationCount ?? 0) > 0)
            <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] bg-amber-400 text-slate-950 font-mono font-bold">
                {{ $pendingValidationCount }}
            </span>
        @endif
    </a>

    <a href="{{ url('/reports') }}" 
       class="flex items-center justify-center gap-2 p-3.5 2xl:p-4 rounded-xl bg-white hover:bg-[#FAF8F5] text-slate-800 font-bold text-xs border border-[#E5E0D8] shadow-xs transition hover:border-[#15803D]">
        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#15803D]"></i>
        <span>Laporan Keuangan Resmi</span>
    </a>
</div>

<!-- Performance per Unit Usaha Breakdown -->
<div class="space-y-3.5">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="building-2" class="w-4 h-4 text-[#C62828]"></i>
            Kinerja Finansial Per Unit Usaha ({{ $tahun }})
        </h3>
        <span class="text-xs text-slate-500 font-medium">3 Unit Usaha Aktif BUMDesa Kuala Alam</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 2xl:gap-6">
        @foreach($unitPerformances as $kode => $uData)
            <div class="bg-white p-5 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-3 {{ $uData['is_selected'] ? 'ring-2 ring-[#C62828] bg-red-50/10' : '' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kode: {{ $kode }}</span>
                        <h4 class="text-sm font-bold text-slate-900">{{ $uData['nama'] }}</h4>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $uData['surplus'] >= 0 ? 'bg-[#F0FDF4] text-[#15803D] border border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]' }}">
                        {{ $uData['surplus'] >= 0 ? 'Surplus' : 'Defisit' }}
                    </span>
                </div>

                <div class="space-y-2 pt-2 border-t border-[#E5E0D8] text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Penerimaan:</span>
                        <span class="font-mono font-bold text-[#15803D]">Rp {{ number_format($uData['penerimaan'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Pengeluaran:</span>
                        <span class="font-mono font-semibold text-[#DC2626]">Rp {{ number_format($uData['pengeluaran'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-900 font-bold pt-2 border-t border-slate-100">
                        <span>Hasil Usaha Bersih:</span>
                        <span class="font-mono {{ $uData['surplus'] >= 0 ? 'text-[#15803D]' : 'text-[#DC2626]' }}">
                            Rp {{ number_format($uData['surplus'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between text-[11px]">
                    <span class="font-mono text-slate-400">{{ $uData['total_transaksi'] }} Transaksi</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#FAF8F5] border border-[#E5E0D8] text-slate-600 font-semibold text-[10px]">
                        <i data-lucide="smartphone" class="w-3 h-3 text-[#C62828]"></i>
                        Diinput via Mobile
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Grid 2 Kolom: Antrean Validasi & Status Pembukuan -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 2xl:gap-8">

    <!-- Kolom Kiri: Antrean Validasi Transaksi Terbaru -->
    <div class="xl:col-span-8 bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden flex flex-col">
        <div class="p-4 sm:p-5 border-b border-[#E5E0D8] bg-[#FAF8F5] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-[#D97706]"></i>
                <h3 class="text-sm font-bold text-slate-900">Daftar Transaksi Unit (Menunggu Persetujuan Bendahara)</h3>
            </div>
            <a href="{{ url('/validation') }}" class="text-xs text-[#C62828] hover:text-[#9A1B1B] font-bold inline-flex items-center gap-1">
                Lihat Semua <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="overflow-x-auto flex-1">
            @if($pendingTransactions->count() > 0)
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-[#E5E0D8] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4">Keterangan</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingTransactions as $p)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $p->unit->kode_unit ?? 'UNIT' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-slate-900 line-clamp-1">{{ $p->keterangan }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">Akun: {{ $p->kode_akun }} - {{ $p->coa->nama_akun ?? '-' }}</p>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $p->jenis_transaksi === 'masuk' ? 'text-[#15803D]' : 'text-[#DC2626]' }}">
                                    {{ $p->jenis_transaksi === 'masuk' ? '+' : '-' }} Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="handleApprove({{ $p->id_transaksi }})" 
                                                class="px-2.5 py-1 bg-[#15803D] hover:bg-emerald-800 text-white font-semibold rounded-lg text-[11px] shadow-xs transition inline-flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i> Setujui
                                        </button>
                                        <button onclick="handleReject({{ $p->id_transaksi }})" 
                                                class="px-2.5 py-1 bg-white hover:bg-red-50 text-slate-700 hover:text-[#C62828] font-semibold rounded-lg text-[11px] border border-[#E5E0D8] transition inline-flex items-center gap-1">
                                            <i data-lucide="x" class="w-3 h-3"></i> Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-8 text-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-[#F0FDF4] text-[#15803D] mx-auto flex items-center justify-center border border-[#BBF7D0]">
                        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-900">Semua Transaksi Telah Terverifikasi</p>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Tidak ada transaksi unit yang menunggu persetujuan dari Bendahara Umum untuk periode ini.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan: Status Pembukuan & Unduh Laporan -->
    <div class="xl:col-span-4 space-y-4">
        
        <!-- Status Pembukuan Card -->
        <div class="bg-white p-5 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-[#15803D]"></i>
                    <h3 class="text-sm font-bold text-slate-900">Status Pembukuan Kas</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#F0FDF4] text-[#15803D] border border-[#BBF7D0]">
                    Seimbang
                </span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="p-3 bg-[#FAF8F5] rounded-xl border border-[#E5E0D8] space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keseimbangan Pembukuan</span>
                    <p class="font-mono font-bold text-slate-900 text-sm">
                        Rp {{ number_format(($balance['total_aset'] ?? 0) + ($income['total_pendapatan'] ?? 0), 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-[#15803D] font-semibold flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Pembukuan Selesai & Seimbang
                    </p>
                </div>

                <div class="space-y-1 text-slate-600 text-[11px]">
                    <div class="flex justify-between">
                        <span>Pengesahan Direktur:</span>
                        <span class="font-semibold text-slate-900">
                            {{ $latestReport && $latestReport->is_locked ? 'Disahkan' : 'Draft Belum Dikunci' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span>Format Laporan Resmi:</span>
                        <span class="font-semibold text-[#15803D]">18 Sheet Lengkap</span>
                    </div>
                </div>
            </div>

            <div class="pt-2 space-y-2">
                <a href="{{ route('reports.download-excel', ['tahun' => $tahun, 'bulan' => $bulan]) }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-[#15803D] hover:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-xs transition">
                    <i data-lucide="download" class="w-4 h-4"></i> Download Excel 18 Sheet
                </a>
                <a href="{{ url('/reports') }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-xl text-xs transition border border-[#E5E0D8]">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-600"></i> Buka Detail Laporan
                </a>
            </div>
        </div>

        <!-- Quick Info BUMDesa Kuala Alam Card -->
        <div class="bg-white p-5 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-2.5">
            <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                <i data-lucide="info" class="w-4 h-4 text-[#D97706]"></i>
                <span>Sistem Digital BUMDesa Kuala Alam</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed font-medium">
                Aplikasi pencatatan keuangan terpadu untuk Unit Usaha Wifi Internet, USP Simpan Pinjam, dan Perkebunan Nanas Madu. Menjamin transparansi publik dan akuntabilitas keuangan desa.
            </p>
        </div>

    </div>

</div>

<!-- Hidden Forms for SweetAlert Approval & Rejection -->
<form id="approve-form" method="POST" class="hidden">
    @csrf
</form>

<form id="reject-form" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="alasan" id="reject-reason">
</form>

@endsection

@push('scripts')
<script>
function handleApprove(id) {
    window.BumdesAlert.confirmApprove(() => {
        const form = document.getElementById('approve-form');
        form.action = `/validation/${id}/approve`;
        form.submit();
    });
}

function handleReject(id) {
    window.BumdesAlert.confirmReject((reason) => {
        const form = document.getElementById('reject-reason');
        form.action = `/validation/${id}/reject`;
        document.getElementById('reject-reason').value = reason;
        form.submit();
    });
}
</script>
@endpush
