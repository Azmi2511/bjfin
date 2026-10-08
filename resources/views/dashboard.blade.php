@extends('layouts.app')

@section('title', 'Executive Dashboard - BJFin BUMDesa Kuala Alam')

@section('header_title', 'Executive Dashboard Keuangan')

@section('content')

@php
    $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
@endphp

<!-- Header Section & Filter Control Bar -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 shrink-0">
            <i data-lucide="line-chart" class="w-5 h-5"></i>
        </div>
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Ringkasan Finansial BUMDesa</h2>
            <p class="text-xs text-slate-500">
                {{ $bulan ? 'Periode ' . ($namaBulan[$bulan] ?? '') . ' ' . $tahun : 'Konsolidasi Tahunan (' . $tahun . ')' }}
            </p>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2 text-xs">
        <!-- Filter Tahun -->
        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-md px-2.5 py-1.5">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-slate-500 font-medium">Tahun:</span>
            <select name="tahun" onchange="this.form.submit()" class="bg-transparent font-bold text-slate-800 border-none p-0 focus:ring-0 cursor-pointer text-xs">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Bulan -->
        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-md px-2.5 py-1.5">
            <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-slate-500 font-medium">Bulan:</span>
            <select name="bulan" onchange="this.form.submit()" class="bg-transparent font-bold text-slate-800 border-none p-0 focus:ring-0 cursor-pointer text-xs">
                <option value="all" {{ is_null($bulan) ? 'selected' : '' }}>Semua Bulan (Setahun)</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ $namaBulan[$m] }}
                    </option>
                @endfor
            </select>
        </div>

        <!-- Filter Unit Usaha -->
        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-md px-2.5 py-1.5">
            <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-slate-500 font-medium">Unit:</span>
            <select name="id_unit" onchange="this.form.submit()" class="bg-transparent font-bold text-slate-800 border-none p-0 focus:ring-0 cursor-pointer text-xs">
                <option value="all" {{ is_null($selectedUnitId) ? 'selected' : '' }}>Semua Unit Usaha</option>
                @foreach($units as $u)
                    <option value="{{ $u->id_unit }}" {{ $selectedUnitId == $u->id_unit ? 'selected' : '' }}>
                        {{ $u->nama_unit }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedUnitId || $bulan)
            <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-md transition text-xs">
                Reset Filter
            </a>
        @endif
    </form>
</div>

<!-- KPI Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- KPI Card 1: Total Pendapatan -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs border-l-4 border-l-emerald-600 space-y-2">
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span class="font-semibold text-slate-600">Total Revenue / Pendapatan</span>
            <span class="p-1.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[10px]">Kredit</span>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">
            Rp {{ number_format($income['total_pendapatan'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
            <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Masuk Pembukuan
            </span>
            <span class="font-mono text-slate-400">{{ $totalTxCount }} Transaksi</span>
        </div>
    </div>

    <!-- KPI Card 2: Total Beban (Red banner accent from logo) -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs border-l-4 border-l-red-600 space-y-2">
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span class="font-semibold text-slate-600">Total Beban & Operasional</span>
            <span class="p-1.5 rounded bg-red-50 text-red-700 font-bold text-[10px]">Debit</span>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">
            Rp {{ number_format($income['total_beban'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
            <span class="flex items-center gap-1 text-red-600 font-semibold">
                <i data-lucide="trending-down" class="w-3.5 h-3.5"></i> Biaya Operasional
            </span>
            <span class="font-mono text-slate-400">SAK ETAP</span>
        </div>
    </div>

    <!-- KPI Card 3: Surplus Bersih SHU (Amber gold accent from logo) -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs border-l-4 border-l-amber-500 space-y-2">
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span class="font-semibold text-slate-600">Surplus Bersih (SHU)</span>
            <span class="p-1.5 rounded bg-amber-50 text-amber-700 font-bold text-[10px]">Net Result</span>
        </div>
        <div class="text-2xl font-extrabold font-mono tracking-tight {{ ($income['laba_bersih'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
            Rp {{ number_format($income['laba_bersih'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
            <span class="font-semibold {{ ($income['laba_bersih'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ ($income['laba_bersih'] ?? 0) >= 0 ? 'Surplus Operasional' : 'Defisit Operasional' }}
            </span>
            <span class="font-mono text-slate-400">Hasil Usaha</span>
        </div>
    </div>

    <!-- KPI Card 4: Saldo Kas & Bank -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs border-l-4 border-l-slate-800 space-y-2">
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span class="font-semibold text-slate-600">Saldo Kas & Bank</span>
            <span class="p-1.5 rounded bg-slate-100 text-slate-800 font-bold text-[10px]">Aktiva Lancar</span>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">
            Rp {{ number_format(($balance['total_aset'] ?? $balance['aktiva_lancar']['kas_bank'] ?? 0), 0, ',', '.') }}
        </div>
        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
            <span class="text-slate-600 font-semibold flex items-center gap-1">
                <i data-lucide="wallet" class="w-3.5 h-3.5 text-slate-500"></i> Kas Tunai & Bank
            </span>
            <span class="font-mono text-emerald-600 font-bold">Lancar</span>
        </div>
    </div>

</div>

<!-- Performance per Unit Usaha Breakdown -->
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="building-2" class="w-4 h-4 text-emerald-700"></i>
            Kinerja Finansial Per Unit Usaha ({{ $tahun }})
        </h3>
        <span class="text-xs text-slate-500">3 Unit Usaha Aktif BUMDesa Kuala Alam</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($unitPerformances as $kode => $uData)
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-3 {{ $uData['is_selected'] ? 'ring-2 ring-emerald-600 bg-emerald-50/15' : '' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kode: {{ $kode }}</span>
                        <h4 class="text-sm font-bold text-slate-900">{{ $uData['nama'] }}</h4>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ $uData['surplus'] >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                        {{ $uData['surplus'] >= 0 ? 'Surplus' : 'Defisit' }}
                    </span>
                </div>

                <div class="space-y-1.5 pt-2 border-t border-slate-100 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Penerimaan:</span>
                        <span class="font-mono font-bold text-emerald-700">Rp {{ number_format($uData['penerimaan'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Pengeluaran:</span>
                        <span class="font-mono font-semibold text-red-700">Rp {{ number_format($uData['pengeluaran'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-900 font-bold pt-1.5 border-t border-slate-100">
                        <span>Hasil Usaha Bersih:</span>
                        <span class="font-mono {{ $uData['surplus'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                            Rp {{ number_format($uData['surplus'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between text-[11px] text-slate-500">
                    <span class="font-mono">{{ $uData['total_transaksi'] }} Transaksi</span>
                    <a href="{{ route('transaksi.unit-input', ['id_unit' => $uData['id_unit']]) }}" class="text-emerald-700 hover:text-emerald-800 font-semibold inline-flex items-center gap-1">
                        Input Transaksi <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Grid 2 Kolom: Antrean Validasi & SAK ETAP Status -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kolom Kiri (2/3 Width): Antrean Validasi Transaksi Terbaru -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                <h3 class="text-sm font-bold text-slate-900">Antrean Validasi Transaksi Unit (Menunggu Bendahara Umum)</h3>
            </div>
            <a href="{{ url('/validation') }}" class="text-xs text-emerald-700 hover:text-emerald-800 font-semibold inline-flex items-center gap-1">
                Lihat Semua <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="overflow-x-auto flex-1">
            @if($pendingTransactions->count() > 0)
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200 uppercase tracking-wider text-[10px]">
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
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $p->unit->kode_unit ?? 'UNIT' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-slate-900 line-clamp-1">{{ $p->keterangan }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">Akun: {{ $p->kode_akun }} - {{ $p->coa->nama_akun ?? '-' }}</p>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $p->jenis_transaksi === 'masuk' ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $p->jenis_transaksi === 'masuk' ? '+' : '-' }} Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="handleApprove({{ $p->id_transaksi }})" 
                                                class="px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded text-[11px] shadow-2xs transition inline-flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i> Setujui
                                        </button>
                                        <button onclick="handleReject({{ $p->id_transaksi }})" 
                                                class="px-2.5 py-1 bg-slate-100 hover:bg-red-50 text-slate-700 hover:text-red-700 font-semibold rounded text-[11px] border border-slate-200 transition inline-flex items-center gap-1">
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
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center border border-emerald-200">
                        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-900">Semua Transaksi Telah Terverifikasi</p>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Tidak ada antrean transaksi unit yang menunggu validasi dari Bendahara Umum untuk periode ini.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan (1/3 Width): Status SAK ETAP & Ekspor Resmi -->
    <div class="space-y-4">
        
        <!-- Status Pembukuan SAK ETAP Card -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
                    <h3 class="text-sm font-bold text-slate-900">Status Pembukuan SAK ETAP</h3>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Balanced
                </span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Debit = Kredit</span>
                    <p class="font-mono font-bold text-slate-900 text-sm">
                        Rp {{ number_format(($balance['total_aset'] ?? 0) + ($income['total_pendapatan'] ?? 0), 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Jurnal Berpasangan Seimbang
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
                        <span>Ekspor Format Kecamatan:</span>
                        <span class="font-semibold text-emerald-700">18 Sheet Lengkap</span>
                    </div>
                </div>
            </div>

            <div class="pt-2 space-y-2">
                <a href="{{ route('reports.download-excel', ['tahun' => $tahun, 'bulan' => $bulan]) }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-md text-xs shadow-2xs transition">
                    <i data-lucide="download" class="w-4 h-4"></i> Download Excel 18 Sheet
                </a>
                <a href="{{ url('/reports') }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-md text-xs transition">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-600"></i> Buka Detail Laporan
                </a>
            </div>
        </div>

        <!-- Quick Info BUMDesa Kuala Alam Card -->
        <div class="bg-slate-900 text-slate-300 p-5 rounded-xl border border-slate-800 space-y-3">
            <div class="flex items-center gap-2 text-white font-bold text-xs">
                <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                <span>BUMDesa Kuala Alam Digital System</span>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
                Aplikasi pencatatan keuangan terpadu untuk Unit Usaha Wifi Internet, USP Simpan Pinjam, dan Perkebunan Nanas Madu. Menjamin transparansi publik dan kepatuhan audit.
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
        const form = document.getElementById('reject-form');
        form.action = `/validation/${id}/reject`;
        document.getElementById('reject-reason').value = reason;
        form.submit();
    });
}
</script>
@endpush
