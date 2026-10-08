@extends('layouts.app')

@section('title', 'Validasi Transaksi - BJFin BUMDesa Kuala Alam')

@section('header_title', 'Validasi Transaksi Unit Usaha')

@section('content')

<!-- Header Section & Summary Metrics -->
<div class="space-y-4" x-data="{ activeTab: 'all' }">
    
    <!-- BUMDesa Crimson Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#C62828] to-[#8B1515] p-5 sm:p-6 text-white shadow-md">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 p-1 bg-white rounded-full border-2 border-amber-400 shadow-sm flex items-center justify-center shrink-0 text-[#C62828]">
                    <i data-lucide="check-square" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-extrabold tracking-tight">Antrean Validasi Transaksi</h2>
                    </div>
                    <p class="text-xs text-red-100 font-medium">
                        Persetujuan transaksi unit usaha sebelum dicatat resmi ke dalam buku kas BUMDesa.
                    </p>
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/20 text-xs text-white">
                <i data-lucide="shield-check" class="w-4 h-4 text-amber-300"></i>
                <span class="font-medium text-[11px]">Pemeriksaan Bukti Transaksi & Nota Pembayaran</span>
            </div>
        </div>
    </div>

    <!-- Filter Tab Unit Usaha (Mobile Pill Choice Chips) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <button @click="activeTab = 'all'" 
                :class="activeTab === 'all' ? 'bg-[#FFEBEE] text-[#C62828] font-bold border-2 border-[#C62828] shadow-xs' : 'bg-white text-slate-600 hover:bg-[#FAF8F5] border border-[#E5E0D8]'"
                class="px-4 py-2 rounded-full transition shrink-0 font-medium">
            Semua Transaksi ({{ $pending->count() }})
        </button>
        <button @click="activeTab = 'WIFI'" 
                :class="activeTab === 'WIFI' ? 'bg-[#FFEBEE] text-[#C62828] font-bold border-2 border-[#C62828] shadow-xs' : 'bg-white text-slate-600 hover:bg-[#FAF8F5] border border-[#E5E0D8]'"
                class="px-4 py-2 rounded-full transition shrink-0 font-medium">
            Unit Wifi ({{ $pending->where('unit.kode_unit', 'WIFI')->count() }})
        </button>
        <button @click="activeTab = 'USP'" 
                :class="activeTab === 'USP' ? 'bg-[#FFEBEE] text-[#C62828] font-bold border-2 border-[#C62828] shadow-xs' : 'bg-white text-slate-600 hover:bg-[#FAF8F5] border border-[#E5E0D8]'"
                class="px-4 py-2 rounded-full transition shrink-0 font-medium">
            Unit USP ({{ $pending->where('unit.kode_unit', 'USP')->count() }})
        </button>
        <button @click="activeTab = 'KEBUN'" 
                :class="activeTab === 'KEBUN' ? 'bg-[#FFEBEE] text-[#C62828] font-bold border-2 border-[#C62828] shadow-xs' : 'bg-white text-slate-600 hover:bg-[#FAF8F5] border border-[#E5E0D8]'"
                class="px-4 py-2 rounded-full transition shrink-0 font-medium">
            Unit Kebun Nanas ({{ $pending->where('unit.kode_unit', 'KEBUN')->count() }})
        </button>
    </div>

    <!-- Table Antrean Validasi -->
    <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden">
        @if($pending->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#FAF8F5] text-slate-600 font-semibold border-b border-[#E5E0D8] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 lg:px-5 2xl:px-6">Tanggal & Ref</th>
                            <th class="py-3.5 px-4 lg:px-5 2xl:px-6">Unit Usaha</th>
                            <th class="py-3.5 px-4 lg:px-5 2xl:px-6">Rincian Transaksi</th>
                            <th class="py-3.5 px-4 lg:px-5 2xl:px-6">Kategori Akun</th>
                            <th class="py-3.5 px-4 lg:px-5 2xl:px-6 text-right">Nominal</th>
                            <th class="py-3.5 px-4 lg:px-5 2xl:px-6 text-center">Bukti Nota</th>
                            <th class="py-3.5 px-4 lg:px-5 2xl:px-6 text-center">Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pending as $p)
                            <tr class="hover:bg-slate-50/80 transition" 
                                x-show="activeTab === 'all' || activeTab === '{{ $p->unit->kode_unit ?? '' }}'">
                                
                                <td class="py-3.5 px-4 lg:px-5 2xl:px-6 whitespace-nowrap">
                                    <p class="font-mono font-bold text-slate-900">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</p>
                                    <span class="text-[10px] text-slate-400 font-mono">ID #{{ $p->id_transaksi }}</span>
                                </td>

                                <td class="py-3.5 px-4 lg:px-5 2xl:px-6 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $p->unit->nama_unit ?? 'Unit Usaha' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 lg:px-5 2xl:px-6">
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

                                <td class="py-3.5 px-4 lg:px-5 2xl:px-6 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-lg bg-[#FAF8F5] text-slate-700 font-mono border border-[#E5E0D8] font-semibold text-[11px]">
                                        {{ $p->kode_akun }}
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $p->coa->nama_akun ?? '-' }}</p>
                                </td>

                                <td class="py-3.5 px-4 lg:px-5 2xl:px-6 text-right whitespace-nowrap">
                                    <p class="font-mono font-extrabold text-sm {{ $p->jenis_transaksi === 'masuk' ? 'text-[#15803D]' : 'text-[#DC2626]' }}">
                                        {{ $p->jenis_transaksi === 'masuk' ? '+' : '-' }} Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                    </p>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">
                                        {{ $p->jenis_transaksi === 'masuk' ? 'Penerimaan' : 'Pengeluaran' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 lg:px-5 2xl:px-6 text-center whitespace-nowrap">
                                    @if($p->bukti_transaksi)
                                        <a href="{{ $p->bukti_transaksi }}" target="_blank" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold border border-slate-200 transition">
                                            <i data-lucide="image" class="w-3.5 h-3.5 text-slate-500"></i> Lihat Nota
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Tanpa Foto</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 lg:px-5 2xl:px-6 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="handleApprove({{ $p->id_transaksi }})" 
                                                class="px-3 py-1.5 bg-[#15803D] hover:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-xs transition inline-flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Setujui
                                        </button>
                                        <button onclick="handleReject({{ $p->id_transaksi }})" 
                                                class="px-3 py-1.5 bg-white hover:bg-red-50 text-slate-700 hover:text-[#C62828] font-semibold rounded-xl text-xs border border-[#E5E0D8] transition inline-flex items-center gap-1">
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
                <div class="w-16 h-16 rounded-full bg-[#F0FDF4] text-[#15803D] mx-auto flex items-center justify-center border border-[#BBF7D0]">
                    <i data-lucide="check-check" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Semua Transaksi Selesai Diperiksa</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Tidak ada transaksi unit usaha yang menunggu persetujuan dari Bendahara Umum. Seluruh transaksi telah selesai diperiksa.
                </p>
                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#C62828] hover:bg-[#9A1B1B] text-white font-bold rounded-xl text-xs shadow-xs transition">
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
        const form = document.getElementById('reject-reason');
        form.action = `/validation/${id}/reject`;
        document.getElementById('reject-reason').value = reason;
        form.submit();
    });
}
</script>
@endpush
