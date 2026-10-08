@extends('layouts.app')

@section('title', 'Pencatatan Unit Usaha - BJFin BUMDesa')

@section('header_title', 'Pencatatan Transaksi Unit Lapangan')

@section('content')

<!-- Header Section & Unit Usaha Selector -->
<div class="space-y-4">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 shrink-0">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Guided Input Bendahara Unit Usaha</h2>
                <p class="text-xs text-slate-500">
                    Pencatatan transaksi lapangan unit usaha BUMDesa dengan kalkulasi otomatis.
                </p>
            </div>
        </div>

        <!-- Unit Selector Button Tabs (with Golden Amber active highlight) -->
        <div class="flex items-center gap-2 overflow-x-auto">
            @foreach($units as $u)
                <a href="{{ route('transaksi.unit-input', ['id_unit' => $u->id_unit]) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 shrink-0 {{ $selectedUnit->id_unit == $u->id_unit ? 'bg-slate-900 text-amber-400 font-bold border border-slate-800 shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    <i data-lucide="{{ $u->kode_unit === 'WIFI' ? 'wifi' : ($u->kode_unit === 'USP' ? 'landmark' : 'sprout') }}" class="w-4 h-4"></i>
                    <span>{{ $u->nama_unit }}</span>
                </a>
            @endforeach
        </div>
    </div>

</div>

<!-- Main Form Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="unitFormApp()">

    <!-- Left Column (2/3 Width): Dynamic Guided Input Form -->
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <h3 class="text-sm font-bold text-slate-900">Form Pencatatan Transaksi {{ $selectedUnit->nama_unit }}</h3>
                </div>
                <span class="text-xs font-mono text-slate-500">Kode Unit: {{ $selectedUnit->kode_unit }}</span>
            </div>

            <form method="POST" action="{{ route('transaksi.unit-store') }}" enctype="multipart/form-data" class="p-5 space-y-5 text-xs">
                @csrf
                <input type="hidden" name="id_unit" value="{{ $selectedUnit->id_unit }}">

                <!-- Select Customer/Autofill Presets (If available) -->
                @if(count($realCustomers) > 0)
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200 space-y-2">
                        <label class="block font-bold text-slate-800">
                            Pilih Data {{ $selectedUnit->kode_unit === 'WIFI' ? 'Pelanggan Wifi' : ($selectedUnit->kode_unit === 'USP' ? 'Pemanfaat USP' : 'Komoditas Kebun') }} Tersimpan:
                        </label>
                        <select @change="selectPreset($event.target.value)" class="w-full bg-white border border-slate-300 rounded-md p-2 text-xs font-medium focus:ring-emerald-600 focus:border-emerald-600">
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
                            <input type="text" name="nama_customer" x-model="wifi.nama" required placeholder="Contoh: Ahmad Subagyo" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor ID Pelanggan / Dusun</label>
                            <input type="text" name="no_customer" x-model="wifi.no" placeholder="Contoh: WF-08 / Dusun II" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Paket Internet</label>
                            <select name="paket" x-model="wifi.paket" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                                <option value="3 MBps">Paket 3 MBps (Rp 175.000)</option>
                                <option value="5 MBps">Paket 5 MBps (Rp 225.000)</option>
                                <option value="10 MBps">Paket 10 MBps (Rp 350.000)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Biaya Berlangganan (Rp)</label>
                            <input type="number" name="biaya_jasa" x-model.number="wifi.biaya_jasa" @input="calcWifiTotal()" required class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono font-bold focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Safety Peralatan (Rp)</label>
                            <input type="number" name="safety_peralatan" x-model.number="wifi.safety" @input="calcWifiTotal()" required class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                    </div>

                @elseif($selectedUnit->kode_unit === 'USP')

                    <!-- UNIT USP SIMPAN PINJAM FIELDS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Pemanfaat USP *</label>
                            <input type="text" name="nama_pemanfaat" x-model="usp.nama" required placeholder="Contoh: Ibu Siti Aminah" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor SPPK Pinjaman</label>
                            <input type="text" name="no_sppk" x-model="usp.sppk" placeholder="Contoh: SPPK-042" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Angsuran Pokok (Rp)</label>
                            <input type="number" name="angsuran_pokok" x-model.number="usp.pokok" @input="calcUspTotal()" required class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono font-bold focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nominal Bunga/Jasa (Rp)</label>
                            <input type="number" name="nominal_bunga" x-model.number="usp.bunga" @input="calcUspTotal()" required class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono font-bold focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Angsuran Ke-</label>
                            <input type="text" name="angsuran_ke" x-model="usp.angsuran_ke" placeholder="1" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                    </div>

                @else

                    <!-- UNIT PERKEBUNAN NANAS FIELDS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Komoditas / Hasil Panen *</label>
                            <input type="text" name="komoditas" x-model="kebun.komoditas" required placeholder="Contoh: Nanas Madu Grade A" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Pembeli / Agen Pasar</label>
                            <input type="text" name="pembeli" x-model="kebun.pembeli" placeholder="Contoh: Pasar Bengkalis" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah Panen (Buah/Kg)</label>
                            <input type="number" name="jumlah_buah" x-model.number="kebun.jumlah" @input="calcKebunTotal()" required class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono font-bold focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                            <input type="number" name="harga_satuan" x-model.number="kebun.harga" @input="calcKebunTotal()" required class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono font-bold focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tahap Panen</label>
                            <input type="text" name="tahap_panen" x-model="kebun.tahap" placeholder="Tahap 1" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                    </div>

                @endif

                <!-- Common Transaction Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Transaksi *</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Kas *</label>
                        <select name="jenis_transaksi" class="w-full border border-slate-300 rounded-md p-2 text-xs font-bold focus:ring-emerald-600 focus:border-emerald-600">
                            <option value="masuk" selected>Penerimaan (Kas Masuk)</option>
                            <option value="keluar">Pengeluaran (Kas Keluar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pilih Akun Rekening (COA)</label>
                        <select name="kode_akun" class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600 focus:border-emerald-600">
                            <option value="">-- Otomatis Sesuai Unit --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->kode_akun }}">
                                    {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Total Nominal Ringkasan & Keterangan -->
                <div class="p-4 bg-slate-900 text-white rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Nominal Dicatat:</span>
                        <span class="text-xl font-extrabold font-mono text-emerald-400">
                            Rp <span x-text="formatRupiah(totalNominal)"></span>
                        </span>
                    </div>
                    <input type="hidden" name="nominal" :value="totalNominal">

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan / Uraian Transaksi *</label>
                        <input type="text" name="keterangan" x-model="keteranganAuto" required placeholder="Uraian otomatis akan terbentuk..." class="w-full bg-slate-800 border border-slate-700 rounded-md p-2 text-xs text-white focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Upload Bukti Fisik / Receipt Foto -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Upload Bukti Foto / Nota Fisik (Opsional)</label>
                    <input type="file" name="bukti_foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-md text-xs shadow-2xs transition flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Simpan & Kirim ke Antrean Validasi Bendahara Umum</span>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- Right Column (1/3 Width): Master Data Presets & Recent Unit History -->
    <div class="space-y-6">

        <!-- Form Tambah Master Entri Baru -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-emerald-700"></i>
                    <h3 class="text-sm font-bold text-slate-900">Daftarkan Master Unit</h3>
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
                    <input type="text" name="nama" required placeholder="Nama lengkap..." class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Ref / SPPK / Dusun</label>
                    <input type="text" name="kode_referensi" placeholder="WF-12 / SPPK-101..." class="w-full border border-slate-300 rounded-md p-2 text-xs focus:ring-emerald-600">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nominal Std (Rp)</label>
                        <input type="number" name="nominal_standar" placeholder="168000" class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nominal Plus (Rp)</label>
                        <input type="number" name="nominal_tambahan" placeholder="7000" class="w-full border border-slate-300 rounded-md p-2 text-xs font-mono">
                    </div>
                </div>

                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-amber-400 font-bold rounded-md text-xs border border-slate-800 shadow-2xs transition">
                    + Simpan Data Master Permanen
                </button>
            </form>
        </div>

        <!-- Riwayat Transaksi Unit Terbaru -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900">Riwayat Terakhir {{ $selectedUnit->kode_unit }}</h3>
                <span class="text-[10px] font-mono text-slate-400">8 Entri</span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentTransactions as $tx)
                    <div class="p-3 hover:bg-slate-50 transition space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($tx->tanggal)->format('d/m/Y') }}</span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase 
                                {{ $tx->status === 'disetujui' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($tx->status === 'ditolak' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                {{ $tx->status }}
                            </span>
                        </div>
                        <p class="font-semibold text-slate-800 line-clamp-1">{{ $tx->keterangan }}</p>
                        <p class="font-mono font-bold text-right {{ $tx->jenis_transaksi === 'masuk' ? 'text-emerald-700' : 'text-red-600' }}">
                            {{ $tx->jenis_transaksi === 'masuk' ? '+' : '-' }} Rp {{ number_format($tx->nominal, 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-400 text-xs italic">Belum ada transaksi unit.</div>
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
