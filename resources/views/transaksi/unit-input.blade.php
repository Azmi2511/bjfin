@extends('layouts.app')

@section('title', 'Pencatatan Unit Usaha - BJFin BUMDesa')

@section('header_title', 'Pencatatan Transaksi Unit Lapangan')

@section('content')

<!-- Header Section & Unit Usaha Selector -->
<div class="space-y-4">
    
    <!-- BUMDesa Crimson Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#C62828] to-[#8B1515] p-5 sm:p-6 text-white shadow-md">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 p-1 bg-white rounded-full border-2 border-amber-400 shadow-sm flex items-center justify-center shrink-0 text-[#C62828]">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-extrabold tracking-tight">Pencatatan Transaksi Unit Usaha</h2>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-bold shadow-xs">
                            {{ $selectedUnit->nama_unit }} ({{ $selectedUnit->kode_unit }})
                        </span>
                    </div>
                    <p class="text-xs text-red-100 font-medium">
                        Pencatatan transaksi penerimaan dan pengeluaran unit usaha BUMDesa secara mudah dan otomatis.
                    </p>
                </div>
            </div>

            <!-- Unit Selector Button Tabs (Mobile Choice Chips) -->
            <div class="flex items-center gap-2 overflow-x-auto">
                @foreach($units as $u)
                    <a href="{{ route('transaksi.unit-input', ['id_unit' => $u->id_unit]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1.5 shrink-0 {{ $selectedUnit->id_unit == $u->id_unit ? 'bg-white text-[#C62828] shadow-xs' : 'bg-black/20 hover:bg-black/30 text-white border border-white/20' }}">
                        <i data-lucide="{{ $u->kode_unit === 'WIFI' ? 'wifi' : ($u->kode_unit === 'USP' ? 'landmark' : 'sprout') }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $u->nama_unit }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Mobile Operational Notice -->
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 border border-amber-300 flex items-center justify-center shrink-0 text-amber-700">
                <i data-lucide="smartphone" class="w-4 h-4"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-amber-900">Pencatatan Operasional Unit Ditangani via Aplikasi Mobile</p>
                <p class="text-[11px] text-amber-700">Pengelola unit usaha (WiFi, USP, Pasar) mencatat transaksi langsung dari aplikasi smartphone. Halaman ini hanya untuk keperluan penyesuaian khusus.</p>
            </div>
        </div>
        <a href="{{ route('transaksi.umum-input') }}" class="shrink-0 px-3 py-1.5 rounded-lg bg-[#C62828] hover:bg-[#9A1B1B] text-white text-xs font-semibold inline-flex items-center gap-1.5 transition shadow-xs">
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            <span>Buka Kas Pusat</span>
        </a>
    </div>

</div>

<!-- Main Form Grid -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 2xl:gap-8" x-data="unitFormApp()">

    <!-- Left Column: Dynamic Guided Input Form -->
    <div class="xl:col-span-8 space-y-6">

        <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[#E5E0D8] bg-[#FAF8F5] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#C62828]"></span>
                    <h3 class="text-sm font-bold text-slate-900">Form Pencatatan Transaksi {{ $selectedUnit->nama_unit }}</h3>
                </div>
                <span class="text-xs font-mono text-slate-500 font-semibold">Kode Unit: {{ $selectedUnit->kode_unit }}</span>
            </div>

            <form method="POST" action="{{ route('transaksi.unit-store') }}" enctype="multipart/form-data" class="p-5 space-y-5 text-xs">
                @csrf
                <input type="hidden" name="id_unit" value="{{ $selectedUnit->id_unit }}">

                <!-- Select Customer/Autofill Presets (If available) -->
                @if(count($realCustomers) > 0)
                    <div class="p-4 bg-[#FAF8F5] rounded-xl border border-[#E5E0D8] space-y-2">
                        <label class="block font-bold text-slate-800">
                            Pilih Data {{ $selectedUnit->kode_unit === 'WIFI' ? 'Pelanggan Wifi' : ($selectedUnit->kode_unit === 'USP' ? 'Pemanfaat USP' : 'Komoditas Kebun') }} Tersimpan:
                        </label>
                        <select @change="selectPreset($event.target.value)" class="w-full bg-white border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-medium focus:ring-[#C62828] focus:border-[#C62828]">
                            <option value="">-- Pilih dari Daftar / Input Baru --</option>
                            @foreach($realCustomers as $index => $c)
                                <option value="{{ $index }}">
                                    {{ !empty($c['is_saved_master']) ? '⭐ [Master] ' : '' }}
                                    @if($selectedUnit->kode_unit === 'WIFI')
                                        {{ $c['nama'] }} ({{ $c['no'] }}) - {{ $c['paket'] }} - Rp {{ number_format($c['nominal'], 0, ',', '.') }}
                                    @elseif($selectedUnit->kode_unit === 'USP')
                                        {{ $c['nama'] }} ({{ $c['sppk'] }}) - {{ $c['usaha'] }}
                                    @else
                                        {{ $c['uraian'] }} - Rp {{ number_format($c['nominal'], 0, ',', '.') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Dynamic Unit Fields based on Kode Unit -->
                @if($selectedUnit->kode_unit === 'WIFI')
                    
                    <!-- UNIT WIFI FIELDS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Pelanggan Wifi *</label>
                            <input type="text" name="nama_customer" x-model="wifi.nama" required placeholder="Contoh: Ahmad Subagyo" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor ID Pelanggan / Dusun</label>
                            <input type="text" name="no_customer" x-model="wifi.no" placeholder="Contoh: WF-08 / Dusun II" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Paket Internet</label>
                            <select name="paket" x-model="wifi.paket" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                                <option value="3 MBps">Paket 3 MBps (Rp 175.000)</option>
                                <option value="5 MBps">Paket 5 MBps (Rp 225.000)</option>
                                <option value="10 MBps">Paket 10 MBps (Rp 350.000)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Biaya Berlangganan (Rp)</label>
                            <input type="number" name="biaya_jasa" x-model.number="wifi.biaya_jasa" @input="calcWifiTotal()" required class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono font-bold focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Safety Peralatan (Rp)</label>
                            <input type="number" name="safety_peralatan" x-model.number="wifi.safety" @input="calcWifiTotal()" required class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                @elseif($selectedUnit->kode_unit === 'USP')

                    <!-- UNIT USP SIMPAN PINJAM FIELDS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Pemanfaat USP *</label>
                            <input type="text" name="nama_pemanfaat" x-model="usp.nama" required placeholder="Contoh: Ibu Siti Aminah" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor SPPK Pinjaman</label>
                            <input type="text" name="no_sppk" x-model="usp.sppk" placeholder="Contoh: SPPK-042" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Angsuran Pokok (Rp)</label>
                            <input type="number" name="angsuran_pokok" x-model.number="usp.pokok" @input="calcUspTotal()" required class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono font-bold focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nominal Bunga/Jasa (Rp)</label>
                            <input type="number" name="nominal_bunga" x-model.number="usp.bunga" @input="calcUspTotal()" required class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono font-bold focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Angsuran Ke-</label>
                            <input type="text" name="angsuran_ke" x-model="usp.angsuran_ke" placeholder="1" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                @else

                    <!-- UNIT PERKEBUNAN NANAS FIELDS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Komoditas / Hasil Panen *</label>
                            <input type="text" name="komoditas" x-model="kebun.komoditas" required placeholder="Contoh: Nanas Madu Grade A" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Pembeli / Agen Pasar</label>
                            <input type="text" name="pembeli" x-model="kebun.pembeli" placeholder="Contoh: Pasar Bengkalis" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah Panen (Buah/Kg)</label>
                            <input type="number" name="jumlah_buah" x-model.number="kebun.jumlah" @input="calcKebunTotal()" required class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono font-bold focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                            <input type="number" name="harga_satuan" x-model.number="kebun.harga" @input="calcKebunTotal()" required class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono font-bold focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tahap Panen</label>
                            <input type="text" name="tahap_panen" x-model="kebun.tahap" placeholder="Tahap 1" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                @endif

                <!-- Common Transaction Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-[#E5E0D8]">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Transaksi *</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Kas *</label>
                        <select name="jenis_transaksi" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-bold focus:ring-[#C62828] focus:border-[#C62828]">
                            <option value="masuk" selected>Penerimaan (Kas Masuk)</option>
                            <option value="keluar">Pengeluaran (Kas Keluar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pilih Akun Rekening (COA)</label>
                        <select name="kode_akun" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828] focus:border-[#C62828]">
                            <option value="">-- Otomatis Sesuai Unit --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->kode_akun }}">
                                    {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Total Nominal Ringkasan & Keterangan (Dark Charcoal matching mobile theme) -->
                <div class="p-4 bg-[#1E293B] text-white rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-300 uppercase tracking-wider font-semibold">Total Nominal Dicatat:</span>
                        <span class="text-xl font-extrabold font-mono text-amber-400">
                            Rp <span x-text="formatRupiah(totalNominal)"></span>
                        </span>
                    </div>
                    <input type="hidden" name="nominal" :value="totalNominal">

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan / Uraian Transaksi *</label>
                        <input type="text" name="keterangan" x-model="keteranganAuto" required placeholder="Uraian otomatis akan terbentuk..." class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:ring-[#C62828] focus:border-[#C62828]">
                    </div>
                </div>

                <!-- Upload Bukti Fisik / Receipt Foto -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Upload Bukti Foto / Nota Fisik (Opsional)</label>
                    <input type="file" name="bukti_foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFEBEE] file:text-[#C62828] hover:file:bg-red-100 cursor-pointer">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-[#C62828] hover:bg-[#9A1B1B] text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Simpan & Kirim ke Antrean Validasi Bendahara Umum</span>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- Right Column: Master Data Presets & Recent Unit History -->
    <div class="xl:col-span-4 space-y-6">

        <!-- Form Tambah Data Pelanggan / Mitra Baru -->
        <div class="bg-white p-5 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-[#D97706]"></i>
                    <h3 class="text-sm font-bold text-slate-900">Tambah Data Pelanggan / Mitra</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('transaksi.master-store') }}" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="id_unit" value="{{ $selectedUnit->id_unit }}">
                <input type="hidden" name="jenis_entri" value="{{ strtolower($selectedUnit->kode_unit) }}">

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Nama {{ $selectedUnit->kode_unit === 'WIFI' ? 'Pelanggan' : ($selectedUnit->kode_unit === 'USP' ? 'Pemanfaat' : 'Komoditas') }} *
                    </label>
                    <input type="text" name="nama" required placeholder="Nama lengkap..." class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828]">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Ref / SPPK / Dusun</label>
                    <input type="text" name="kode_referensi" placeholder="WF-12 / SPPK-101..." class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:ring-[#C62828]">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Biaya Standar (Rp)</label>
                        <input type="number" name="nominal_standar" placeholder="168000" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Biaya Tambahan (Rp)</label>
                        <input type="number" name="nominal_tambahan" placeholder="7000" class="w-full border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-[#D97706] hover:bg-[#B45309] text-white font-bold rounded-xl text-xs shadow-xs transition">
                    + Simpan Data Baru
                </button>
            </form>
        </div>

        <!-- Riwayat Transaksi Unit Terbaru -->
        <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden">
            <div class="p-4 border-b border-[#E5E0D8] bg-[#FAF8F5] flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900">Riwayat Terakhir {{ $selectedUnit->kode_unit }}</h3>
                <span class="text-[10px] font-mono text-slate-500 font-bold">8 Entri</span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentTransactions as $tx)
                    <div class="p-3.5 hover:bg-slate-50/80 transition space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($tx->tanggal)->format('d/m/Y') }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase 
                                {{ $tx->status === 'disetujui' ? 'bg-[#F0FDF4] text-[#15803D] border border-[#BBF7D0]' : ($tx->status === 'ditolak' ? 'bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]' : 'bg-[#FFFBEB] text-[#D97706] border border-[#FDE68A]') }}">
                                {{ $tx->status }}
                            </span>
                        </div>
                        <p class="font-semibold text-slate-800 line-clamp-1">{{ $tx->keterangan }}</p>
                        <p class="font-mono font-bold text-right {{ $tx->jenis_transaksi === 'masuk' ? 'text-[#15803D]' : 'text-[#DC2626]' }}">
                            {{ $tx->jenis_transaksi === 'masuk' ? '+' : '-' }} Rp {{ number_format($tx->nominal, 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs italic">Belum ada transaksi unit.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
function unitFormApp() {
    const rawPresets = @json($realCustomers);
    const unitCode = '{{ $selectedUnit->kode_unit }}';

    return {
        unitCode: unitCode,
        presets: rawPresets,
        wifi: { nama: '', no: '', paket: '3 MBps', biaya_jasa: 168000, safety: 7000 },
        usp: { nama: '', sppk: '', usaha: '', pokok: 300000, bunga: 50000, angsuran_ke: '1' },
        kebun: { komoditas: 'Nanas Madu Grade A', pembeli: 'Pasar Bengkalis', jumlah: 100, harga: 3500, tahap: 'Tahap 1' },
        totalNominal: 175000,
        keteranganAuto: '',

        init() {
            this.updateTotalAndKeterangan();
        },

        calcWifiTotal() {
            this.totalNominal = (Number(this.wifi.biaya_jasa) || 0) + (Number(this.wifi.safety) || 0);
            this.keteranganAuto = `[Wifi] Iuran ${this.wifi.nama || 'Pelanggan'} (${this.wifi.no || 'WF'}) - ${this.wifi.paket}`;
        },

        calcUspTotal() {
            this.totalNominal = (Number(this.usp.pokok) || 0) + (Number(this.usp.bunga) || 0);
            this.keteranganAuto = `[USP] Angsuran ke-${this.usp.angsuran_ke} ${this.usp.nama || 'Pemanfaat'} (${this.usp.sppk || 'SPPK'})`;
        },

        calcKebunTotal() {
            this.totalNominal = (Number(this.kebun.jumlah) || 0) * (Number(this.kebun.harga) || 0);
            this.keteranganAuto = `[Kebun] Penjualan ${this.kebun.komoditas || 'Nanas'} (${this.kebun.jumlah} buah x Rp ${this.formatRupiah(this.kebun.harga)}) - ${this.kebun.pembeli}`;
        },

        updateTotalAndKeterangan() {
            if (this.unitCode === 'WIFI') this.calcWifiTotal();
            else if (this.unitCode === 'USP') this.calcUspTotal();
            else if (this.unitCode === 'KEBUN') this.calcKebunTotal();
        },

        selectPreset(index) {
            if (index === '' || !this.presets[index]) return;
            const p = this.presets[index];

            if (this.unitCode === 'WIFI') {
                this.wifi.nama = p.nama || '';
                this.wifi.no = p.no || '';
                this.wifi.paket = p.paket || '3 MBps';
                this.wifi.biaya_jasa = p.biaya_jasa || 168000;
                this.wifi.safety = p.safety_peralatan || 7000;
                this.calcWifiTotal();
            } else if (this.unitCode === 'USP') {
                this.usp.nama = p.nama || '';
                this.usp.sppk = p.sppk || '';
                this.usp.usaha = p.usaha || '';
                this.usp.pokok = p.angsuran_pokok || 300000;
                this.usp.bunga = p.nominal_bunga || 50000;
                this.calcUspTotal();
            } else if (this.unitCode === 'KEBUN') {
                this.kebun.komoditas = p.komoditas || 'Nanas Madu';
                this.kebun.pembeli = p.pembeli || 'Pasar Bengkalis';
                this.kebun.jumlah = p.jumlah || 100;
                this.kebun.harga = p.harga || 3500;
                this.calcKebunTotal();
            }
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number || 0);
        }
    }
}
</script>
@endpush
