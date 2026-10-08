@extends('layouts.app')

@section('title', 'Validasi Transaksi - BJFin BUMDesa Kuala Alam')

@section('header_title', 'Validasi Transaksi Unit Usaha')

@section('content')

<!-- Header Section & Summary Metrics -->
<div class="space-y-4" x-data="{ activeTab: 'all' }">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 shrink-0">
                <i data-lucide="check-square" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Antrean Validasi Transaksi Bendahara Umum</h2>
                <p class="text-xs text-slate-500">
                    Otorisasi transaksi unit usaha sebelum otomatis dibukukan ke Jurnal Berpasangan (Double-Entry).
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-md bg-slate-100 text-slate-800 font-bold text-xs font-mono border border-slate-200">
                {{ $pending->count() }} Menunggu Otorisasi
            </span>
        </div>
    </div>

    <!-- Filter Tab Unit Usaha -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <button @click="activeTab = 'all'" 
                :class="activeTab === 'all' ? 'bg-slate-900 text-amber-400 font-bold border border-slate-800 shadow-2xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-lg transition shrink-0">
            Semua Antrean ({{ $pending->count() }})
        </button>
        <button @click="activeTab = 'WIFI'" 
                :class="activeTab === 'WIFI' ? 'bg-slate-900 text-amber-400 font-bold border border-slate-800 shadow-2xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-lg transition shrink-0">
            Unit Wifi ({{ $pending->where('unit.kode_unit', 'WIFI')->count() }})
        </button>
        <button @click="activeTab = 'USP'" 
                :class="activeTab === 'USP' ? 'bg-slate-900 text-amber-400 font-bold border border-slate-800 shadow-2xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-lg transition shrink-0">
            Unit USP ({{ $pending->where('unit.kode_unit', 'USP')->count() }})
        </button>
        <button @click="activeTab = 'KEBUN'" 
                :class="activeTab === 'KEBUN' ? 'bg-slate-900 text-amber-400 font-bold border border-slate-800 shadow-2xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-lg transition shrink-0">
            Unit Kebun Nanas ({{ $pending->where('unit.kode_unit', 'KEBUN')->count() }})
        </button>
    </div>

    <!-- Table Antrean Validasi -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        @if($pending->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Tanggal & Ref</th>
                            <th class="py-3 px-4">Unit Usaha</th>
                            <th class="py-3 px-4">Rincian Transaksi</th>
                            <th class="py-3 px-4">Kode Akun (COA)</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-center">Bukti Nota</th>
                            <th class="py-3 px-4 text-center">Otorisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pending as $p)
                            <tr class="hover:bg-slate-50 transition" 
                                x-show="activeTab === 'all' || activeTab === '{{ $p->unit->kode_unit ?? '' }}'">
                                
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <p class="font-mono font-bold text-slate-900">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</p>
                                    <span class="text-[10px] text-slate-400 font-mono">ID #{{ $p->id_transaksi }}</span>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded text-[10px] font-bold font-mono bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $p->unit->nama_unit ?? 'Unit Usaha' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900 text-sm">{{ $p->keterangan }}</p>
                                    
                                    @if(!empty($p->data_tambahan))
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[11px] text-slate-500">
                                            @if(isset($p->data_tambahan['nama_customer']))
                                                 <span>Pelanggan: <strong class="text-slate-800">{{ $p->data_tambahan['nama_customer'] }}</strong></span>
                                            @endif
                                            @if(isset($p->data_tambahan['nama_pemanfaat']))
                                                <span>Pemanfaat: <strong class="text-slate-800">{{ $p->data_tambahan['nama_pemanfaat'] }}</strong></span>
                                            @endif
                                            @if(isset($p->data_tambahan['komoditas']))
                                                <span>Komoditas: <strong class="text-slate-800">{{ $p->data_tambahan['komoditas'] }}</strong></span>
                                            @endif
                                            @if(isset($p->data_tambahan['no_bukti']))
                                                <span class="font-mono text-[10px] text-slate-400">Nota: {{ $p->data_tambahan['no_bukti'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <p class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $p->user->nama ?? $p->user->name ?? 'Bendahara Unit' }}</p>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded bg-slate-50 text-slate-700 font-mono border border-slate-200 font-semibold text-[11px]">
                                        {{ $p->kode_akun }}
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $p->coa->nama_akun ?? '-' }}</p>
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <p class="font-mono font-extrabold text-sm {{ $p->jenis_transaksi === 'masuk' ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ $p->jenis_transaksi === 'masuk' ? '+' : '-' }} Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                    </p>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">
                                        {{ $p->jenis_transaksi === 'masuk' ? 'Penerimaan' : 'Pengeluaran' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($p->bukti_transaksi)
                                        <a href="{{ $p->bukti_transaksi }}" target="_blank" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold border border-slate-200 transition">
                                            <i data-lucide="image" class="w-3.5 h-3.5 text-slate-500"></i> Lihat Nota
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Tanpa Foto</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="handleApprove({{ $p->id_transaksi }})" 
                                                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded text-xs shadow-2xs transition inline-flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Setujui
                                        </button>
                                        <button onclick="handleReject({{ $p->id_transaksi }})" 
                                                class="px-3 py-1.5 bg-slate-100 hover:bg-red-50 text-slate-700 hover:text-red-700 font-semibold rounded text-xs border border-slate-200 transition inline-flex items-center gap-1">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Tolak
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center border border-emerald-200">
                    <i data-lucide="check-check" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Semua Transaksi Selesai Divalidasi</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Tidak ada transaksi unit usaha yang menunggu persetujuan dari Bendahara Umum. Seluruh laporan keuangan saat ini telah seimbang dan sinkron.
                </p>
                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white font-semibold rounded-md text-xs hover:bg-slate-800 transition">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>

<!-- Forms for Action Submission -->
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
