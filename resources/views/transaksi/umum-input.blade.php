@extends('layouts.app')

@section('title', 'Kas & Memorial Pusat - BJFin BUMDesa')

@section('header_title', 'Pencatatan Kas Pusat & Jurnal SAK ETAP')

@section('content')

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 shrink-0">
            <i data-lucide="book-open" class="w-5 h-5"></i>
        </div>
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Kas & Memorial Pusat BUMDesa</h2>
            <p class="text-xs text-slate-500">
                Pencatatan kas induk, mutasi bank, dan jurnal memorial berpasangan (Double-Entry) SAK ETAP.
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <span class="px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center gap-1.5">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            SAK ETAP Compliant
        </span>
    </div>
</div>

<!-- Main Form & Live Balance Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="umumJournalApp()">

    <!-- Left Column (2/3 Width): Form Kas & Jurnal -->
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <h3 class="text-sm font-bold text-slate-900">Form Jurnal Transaksi Kas Pusat</h3>
                </div>
                <span class="text-xs font-mono text-slate-500">Double-Entry</span>
            </div>

            <form method="POST" action="{{ route('transaksi.umum-store') }}" enctype="multipart/form-data" class="p-5 space-y-5 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Transaksi *</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pilih Unit Usaha Terkait *</label>
                        <select name="id_unit" required class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600 font-medium">
                            @foreach($units as $u)
                                <option value="{{ $u->id_unit }}">{{ $u->nama_unit }} ({{ $u->kode_unit }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Transaksi Kas *</label>
                        <select name="jenis_transaksi" required class="w-full border border-slate-300 rounded-md p-2 text-xs font-bold focus:ring-emerald-600 focus:border-emerald-600">
                            <option value="masuk" selected>Penerimaan Kas (Kas Masuk)</option>
                            <option value="keluar">Pengeluaran Operasional (Kas Keluar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nominal Transaksi (Rp) *</label>
                        <input type="number" name="nominal" x-model.number="nominal" required placeholder="0" class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono font-bold text-sm focus:ring-emerald-600 focus:border-emerald-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Akun Debet SAK ETAP *</label>
                        <select name="kode_akun_debet" required class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->kode_akun }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }} ({{ strtoupper($acc->kelompok) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Akun Kredit SAK ETAP *</label>
                        <select name="kode_akun_kredit" required class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->kode_akun }}" {{ $loop->index == 1 ? 'selected' : '' }}>{{ $acc->kode_akun }} - {{ $acc->nama_akun }} ({{ strtoupper($acc->kelompok) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Uraian / Keterangan Transaksi Kas *</label>
                    <textarea name="keterangan" required rows="2" placeholder="Tuliskan keterangan lengkap pencatatan kas pusat..." class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Upload Bukti Fisik / Nota (Opsional)</label>
                    <input type="file" name="bukti_foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-md text-xs shadow-2xs transition flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Posting Jurnal & Simpan ke Kas Pusat SAK ETAP</span>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- Right Column (1/3 Width): Live Balance & Recent Journals -->
    <div class="space-y-6">

        <!-- Live Balance Calculator Card -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="scale" class="w-4 h-4 text-emerald-600"></i>
                    <h3 class="text-sm font-bold text-slate-900">Kalkulator Balance Jurnal</h3>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Balanced
                </span>
            </div>

            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80 space-y-2 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Total Debet (D):</span>
                    <span class="font-mono font-bold text-slate-900">Rp <span x-text="formatRupiah(nominal)"></span></span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Total Kredit (K):</span>
                    <span class="font-mono font-bold text-slate-900">Rp <span x-text="formatRupiah(nominal)"></span></span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center font-bold text-emerald-700">
                    <span>Status Keseimbangan:</span>
                    <span class="flex items-center gap-1"><i data-lucide="check-circle" class="w-3.5 h-3.5"></i> SEIMBANG</span>
                </div>
            </div>

            <p class="text-[11px] text-slate-500 leading-relaxed">
                Pencatatan kas otomatis menerapkan jurnal berpasangan (Double-Entry) SAK ETAP untuk memastikan laporan keuangan selalu seimbang.
            </p>
        </div>

        <!-- Recent General Journals -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900">Jurnal Terakhir Dibukukan</h3>
                <span class="text-[10px] font-mono text-slate-400">Real-time</span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentTransactions as $tx)
                    <div class="p-3 hover:bg-slate-50 transition space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($tx->tanggal)->format('d/m/Y') }}</span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Disetujui
                            </span>
                        </div>
                        <p class="font-semibold text-slate-800 line-clamp-1">{{ $tx->keterangan }}</p>
                        <p class="font-mono font-bold text-right text-emerald-700">
                            Rp {{ number_format($tx->nominal, 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-400 text-xs italic">Belum ada jurnal umum dibukukan.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
function umumJournalApp() {
    return {
        nominal: 0,
        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number || 0);
        }
    }
}
</script>
@endpush
