@extends('layouts.app')

@section('title', 'Laporan Keuangan BUMDesa - BJFin BUMDes Kuala Alam')

@section('header_title', 'Laporan Keuangan BUMDesa')

@section('content')

@php
    $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
@endphp

<div class="space-y-6" x-data="{ reportTab: 'laba_rugi' }">
    
    <!-- BUMDesa Crimson Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#C62828] to-[#8B1515] p-5 sm:p-6 text-white shadow-md">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 p-1 bg-white rounded-full border-2 border-amber-400 shadow-sm flex items-center justify-center shrink-0 text-[#C62828]">
                    <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-extrabold tracking-tight">Laporan Keuangan BUMDesa</h2>
                    </div>
                    <p class="text-xs text-red-100 font-medium">
                        Laporan Laba Rugi, Posisi Keuangan (Neraca), Arus Kas, dan Ekspor Excel Resmi Format Desa.
                    </p>
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/20 text-xs text-white">
                <i data-lucide="shield-check" class="w-4 h-4 text-amber-300"></i>
                <span class="font-medium text-[11px]">Format Laporan Standar Keuangan Desa Terpadu</span>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 lg:p-6 rounded-2xl border border-[#E5E0D8] shadow-xs">
        <form method="GET" action="{{ url('/reports') }}" id="reportFilterForm" class="flex flex-col md:flex-row md:items-end justify-between gap-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1 max-w-xl">
                <div>
                    <label for="tahun" class="block font-semibold text-slate-700 mb-1">Tahun Buku *</label>
                    <select id="tahun" name="tahun" onchange="this.form.submit()" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828] font-bold cursor-pointer bg-white">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="bulan" class="block font-semibold text-slate-700 mb-1">Bulan Laporan *</label>
                    <select id="bulan" name="bulan" onchange="this.form.submit()" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828] font-bold cursor-pointer bg-white">
                        <option value="all" {{ is_null($bulan) ? 'selected' : '' }}>Semua Bulan (Setahun)</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ $namaBulan[$m] }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="flex items-end">
                <a href="{{ route('reports.download-excel', ['tahun' => $tahun, 'bulan' => $bulan ?? 'all']) }}" 
                   class="w-full md:w-auto px-5 py-2.5 bg-[#15803D] hover:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-1.5 min-h-[42px]">
                    <i data-lucide="download" class="w-4 h-4"></i> Download Excel 18 Sheet
                </a>
            </div>
        </form>
    </div>

    <!-- Report Tabs Toggle (Mobile Choice Chips) -->
    <div class="flex items-center gap-2 border-b border-[#E5E0D8] pb-3 text-xs overflow-x-auto">
        <button @click="reportTab = 'laba_rugi'" 
                :class="reportTab === 'laba_rugi' ? 'bg-[#FFEBEE] text-[#C62828] font-bold border-2 border-[#C62828] shadow-xs' : 'bg-white text-slate-700 hover:bg-[#FAF8F5] border border-[#E5E0D8]'"
                class="px-4 py-2 rounded-full transition flex items-center gap-2 font-medium shrink-0">
            <i data-lucide="line-chart" class="w-4 h-4"></i> Laporan Laba Rugi
        </button>
        <button @click="reportTab = 'neraca'" 
                :class="reportTab === 'neraca' ? 'bg-[#FFEBEE] text-[#C62828] font-bold border-2 border-[#C62828] shadow-xs' : 'bg-white text-slate-700 hover:bg-[#FAF8F5] border border-[#E5E0D8]'"
                class="px-4 py-2 rounded-full transition flex items-center gap-2 font-medium shrink-0">
            <i data-lucide="scale" class="w-4 h-4"></i> Neraca Keuangan
        </button>
        <button @click="reportTab = 'arus_kas'" 
                :class="reportTab === 'arus_kas' ? 'bg-[#FFEBEE] text-[#C62828] font-bold border-2 border-[#C62828] shadow-xs' : 'bg-white text-slate-700 hover:bg-[#FAF8F5] border border-[#E5E0D8]'"
                class="px-4 py-2 rounded-full transition flex items-center gap-2 font-medium shrink-0">
            <i data-lucide="arrow-left-right" class="w-4 h-4"></i> Laporan Arus Kas
        </button>
    </div>

    <!-- Main Report Canvas Card -->
    <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs p-6 lg:p-8 2xl:p-10 space-y-6">
        
        <!-- Document Title -->
        <div class="text-center space-y-1.5 border-b border-[#E5E0D8] pb-4">
            <h2 class="text-lg font-bold text-slate-900">Laporan Keuangan BUMDesa Kuala Alam</h2>
            <h3 class="text-xs font-bold text-[#C62828] tracking-wider uppercase">LAPORAN PEMBUKUAN KEUANGAN RESMI BUMDESA</h3>
            <p class="text-xs text-slate-500 font-medium">
                {{ $bulan ? 'Periode ' . ($namaBulan[$bulan] ?? '') . ' ' . $tahun : 'Tahun Buku ' . $tahun }}
            </p>
        </div>

        <!-- 1. LABA RUGI TAB CONTENT -->
        <div x-show="reportTab === 'laba_rugi'" class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-xs">
                <h4 class="font-bold text-slate-900">I. LAPORAN LABA RUGI KONSOLIDASI</h4>
                <span class="font-mono text-[#15803D] font-bold">Dalam Rupiah (Rp)</span>
            </div>

            <table class="w-full text-left text-xs border-collapse">
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-[#FAF8F5] font-bold text-slate-800">
                        <td class="py-2.5 px-3" colspan="2">PENDAPATAN OPERASIONAL USAHA</td>
                    </tr>
                    @foreach(($income['pendapatan'] ?? []) as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 px-6 font-mono text-slate-600">{{ $p['kode_akun'] ?? '' }} - {{ $p['nama_akun'] ?? '' }}</td>
                            <td class="py-2 px-6 text-right font-mono font-bold text-[#15803D]">Rp {{ number_format($p['jumlah'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-slate-50 font-bold text-slate-900">
                        <td class="py-2.5 px-3">TOTAL PENDAPATAN OPERASIONAL</td>
                        <td class="py-2.5 px-6 text-right font-mono text-[#15803D] text-sm">Rp {{ number_format($income['total_pendapatan'] ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="bg-[#FAF8F5] font-bold text-slate-800">
                        <td class="py-2.5 px-3" colspan="2">BEBAN OPERASIONAL & USAHA</td>
                    </tr>
                    @foreach(($income['beban'] ?? []) as $b)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 px-6 font-mono text-slate-600">{{ $b['kode_akun'] ?? '' }} - {{ $b['nama_akun'] ?? '' }}</td>
                            <td class="py-2 px-6 text-right font-mono font-semibold text-[#DC2626]">Rp {{ number_format($b['jumlah'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-slate-50 font-bold text-slate-900">
                        <td class="py-2.5 px-3">TOTAL BEBAN OPERASIONAL</td>
                        <td class="py-2.5 px-6 text-right font-mono text-[#DC2626] text-sm">Rp {{ number_format($income['total_beban'] ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="bg-[#1E293B] text-white font-bold text-sm">
                        <td class="py-3 px-3 uppercase">SURPLUS / (DEFISIT) BERSIH TAHUN BERJALAN (SHU)</td>
                        <td class="py-3 px-6 text-right font-mono text-amber-400">Rp {{ number_format($income['laba_bersih'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 2. NERACA TAB CONTENT -->
        <div x-show="reportTab === 'neraca'" x-cloak class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-xs">
                <h4 class="font-bold text-slate-900">II. NERACA KEUANGAN KONSOLIDASI</h4>
                <span class="font-mono text-[#15803D] font-bold">Seimbang & Akurat</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 2xl:gap-8">
                <!-- Aktiva -->
                <div class="space-y-3 border border-[#E5E0D8] p-4 rounded-xl bg-white">
                    <h5 class="font-bold text-slate-900 border-b border-slate-100 pb-1 flex justify-between items-center text-xs">
                        <span>ASET & KAS (AKTIVA)</span>
                        <span class="font-mono text-[#15803D] font-bold">Rp {{ number_format($balance['total_aset'] ?? 0, 0, ',', '.') }}</span>
                    </h5>
                    <div class="space-y-2 text-xs divide-y divide-slate-100">
                        @foreach(($balance['aset'] ?? []) as $a)
                            <div class="flex justify-between text-slate-800 pt-1">
                                <span>{{ $a['kode_akun'] }} - {{ $a['nama_akun'] }}:</span>
                                <span class="font-mono font-semibold">Rp {{ number_format($a['saldo'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pasiva -->
                <div class="space-y-3 border border-[#E5E0D8] p-4 rounded-xl bg-white">
                    <h5 class="font-bold text-slate-900 border-b border-slate-100 pb-1 flex justify-between items-center text-xs">
                        <span>KEWAJIBAN & MODAL (PASIVA)</span>
                        <span class="font-mono text-[#15803D] font-bold">Rp {{ number_format($balance['total_passiva'] ?? 0, 0, ',', '.') }}</span>
                    </h5>
                    <div class="space-y-2 text-xs divide-y divide-slate-100">
                        @foreach(($balance['kewajiban'] ?? []) as $k)
                            <div class="flex justify-between text-slate-800 pt-1">
                                <span>{{ $k['kode_akun'] }} - {{ $k['nama_akun'] }}:</span>
                                <span class="font-mono font-semibold">Rp {{ number_format($k['saldo'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        @foreach(($balance['ekuitas'] ?? []) as $e)
                            <div class="flex justify-between text-slate-800 pt-1">
                                <span>{{ $e['kode_akun'] }} - {{ $e['nama_akun'] }}:</span>
                                <span class="font-mono font-semibold">Rp {{ number_format($e['saldo'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between text-[#15803D] font-bold pt-1">
                            <span>Laba Periode Berjalan (SHU):</span>
                            <span class="font-mono">Rp {{ number_format($balance['laba_periode_berjalan'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. ARUS KAS TAB CONTENT -->
        <div x-show="reportTab === 'arus_kas'" x-cloak class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-xs">
                <h4 class="font-bold text-slate-900">III. LAPORAN ARUS KAS</h4>
                <span class="font-mono text-[#15803D] font-bold">Metode Langsung</span>
            </div>

            <table class="w-full text-left text-xs border-collapse">
                <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 px-3 font-semibold">Arus Kas Masuk dari Aktivitas Operasional</td>
                        <td class="py-2.5 px-6 text-right font-mono text-[#15803D] font-bold">Rp {{ number_format($cashFlow['arus_masuk'] ?? $income['total_pendapatan'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 px-3 font-semibold">Arus Kas Keluar untuk Operasional Usaha</td>
                        <td class="py-2.5 px-6 text-right font-mono text-[#DC2626] font-bold">(Rp {{ number_format($cashFlow['arus_keluar'] ?? $income['total_beban'] ?? 0, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-[#1E293B] text-white font-bold text-xs">
                        <td class="py-3 px-3">KENAIKAN BERSIH KAS & SETARA KAS</td>
                        <td class="py-3 px-6 text-right font-mono text-amber-400">Rp {{ number_format($cashFlow['bersih'] ?? $income['laba_bersih'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Digital Signature Approval Section -->
        <div class="pt-4 border-t border-[#E5E0D8] grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600">
            <div class="p-3.5 bg-[#FAF8F5] rounded-xl border border-[#E5E0D8] space-y-1">
                <span class="font-bold uppercase text-slate-800 text-[10px]">Kode Keabsahan Laporan Digital:</span>
                <p class="font-mono text-[11px] break-all text-[#15803D] font-semibold">
                    {{ $report->digital_signature ?? hash('sha256', 'BUMDES_KUALA_ALAM_' . $tahun . '_' . ($bulan ?? 'ALL')) }}
                </p>
            </div>
            
            <div class="flex items-center justify-between p-3.5 bg-[#FAF8F5] rounded-xl border border-[#E5E0D8]">
                <div>
                    <span class="font-bold uppercase text-slate-800 text-[10px] block">Status Pengesahan Laporan:</span>
                    <span class="font-bold text-[#15803D] text-xs">
                        {{ $report && $report->is_locked ? 'DISAHKAN & TERKUNCI' : 'DRAFT LAPORAN' }}
                    </span>
                </div>
                
                @if(!$report || !$report->is_locked)
                    @if($report)
                        <form method="POST" action="{{ route('reports.submit', ['id' => $report->id_laporan]) }}">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 bg-[#C62828] hover:bg-[#9A1B1B] text-white font-semibold rounded-xl text-xs transition flex items-center gap-1 shadow-xs">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                <span>Kunci & Sahkan Laporan</span>
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

    </div>

</div>

@endsection
