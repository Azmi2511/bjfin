@extends('layouts.app')

@section('title', 'Kas & Memorial Pusat - BJFin BUMDesa')

@section('header_title', 'Pencatatan Kas & Memorial Pusat')

@section('content')

<div class="space-y-5" x-data="umumSatSetApp()">

    <!-- BUMDesa Crimson Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#C62828] to-[#8B1515] p-5 sm:p-6 text-white shadow-md">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 p-1 bg-white rounded-full border-2 border-amber-400 shadow-sm flex items-center justify-center shrink-0 text-[#C62828]">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-extrabold tracking-tight">Kas & Memorial Pusat BUMDesa</h2>
                    </div>
                    <p class="text-xs text-red-100 font-medium">
                        Pencatatan kas operasional induk dan mutasi bank cepat untuk percepatan laporan keuangan BUMDesa.
                    </p>
                </div>
            </div>

            <!-- Quick Saldo Badges in Hero Header -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/20 text-xs text-white flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-amber-300"></i>
                    <div>
                        <span class="text-[10px] text-red-100 block leading-tight">Kas Tunai (111)</span>
                        <span class="font-mono font-bold text-xs">Rp {{ number_format($saldoKasTunai, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/20 text-xs text-white flex items-center gap-2">
                    <i data-lucide="landmark" class="w-4 h-4 text-emerald-300"></i>
                    <div>
                        <span class="text-[10px] text-red-100 block leading-tight">Bank BUMDesa (112)</span>
                        <span class="font-mono font-bold text-xs">Rp {{ number_format($saldoBank, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ⚡ TEMPLATE CEPAT SAT-SET (1-Klik Auto-Fill) -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#C62828] animate-pulse"></span>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900">Pilih Template Transaksi Cepat (1-Klik Isi Otomatis)</h3>
            </div>
            <span class="text-[11px] text-slate-500 font-medium hidden sm:inline">Klik salah satu tombol untuk mengisi form secara instan</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5 2xl:gap-3 text-xs">
            <template x-for="(p, idx) in presets" :key="idx">
                <button type="button" @click="applyPreset(p)" 
                        class="p-2.5 rounded-xl border border-[#E5E0D8] hover:border-[#C62828] bg-[#FAF8F5] hover:bg-[#FFEBEE] transition-all text-left flex flex-col justify-between group cursor-pointer shadow-2xs">
                    <div class="flex items-center justify-between gap-1 w-full mb-1">
                        <span class="font-bold text-slate-900 group-hover:text-[#C62828] text-[11px] truncate" x-text="p.label"></span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase"
                              :class="p.jenis === 'keluar' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'"
                              x-text="p.jenis === 'keluar' ? 'Keluar' : 'Masuk'"></span>
                    </div>
                    <span class="text-[10px] text-slate-500 line-clamp-1" x-text="p.rekening === '112' ? 'Via Bank (112)' : 'Via Kas Tunai (111)'"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 2xl:gap-8">

        <!-- Left Column: Form Pencatatan Kas Cepat -->
        <div class="xl:col-span-8 space-y-5">

            <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-[#E5E0D8] bg-[#FAF8F5] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4 h-4 text-[#C62828]"></i>
                        <h3 class="text-sm font-bold text-slate-900">Form Input Cepat Bendahara Umum</h3>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                        Pusat BUMDesa (PUSAT)
                    </span>
                </div>

                <form method="POST" action="{{ route('transaksi.umum-store') }}" enctype="multipart/form-data" class="p-5 space-y-5 text-xs">
                    @csrf
                    <input type="hidden" name="id_unit" value="{{ $pusatUnit->id_unit }}">

                    <!-- Step 1: Pilihan Arah Dana & Sumber Rekening -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Arah Dana: Keluar vs Masuk -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-1.5">
                                1. Jenis Aliran Kas *
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="setJenis('keluar')"
                                        :class="jenis_transaksi === 'keluar' ? 'bg-[#DC2626] text-white border-[#DC2626] shadow-xs font-bold' : 'bg-white text-slate-700 border-[#E5E0D8] hover:bg-slate-50 font-medium'"
                                        class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                                    <span>Kas Keluar (Biaya)</span>
                                </button>
                                <button type="button" @click="setJenis('masuk')"
                                        :class="jenis_transaksi === 'masuk' ? 'bg-[#15803D] text-white border-[#15803D] shadow-xs font-bold' : 'bg-white text-slate-700 border-[#E5E0D8] hover:bg-slate-50 font-medium'"
                                        class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                                    <span>Kas Masuk (Terima)</span>
                                </button>
                            </div>
                            <input type="hidden" name="jenis_transaksi" :value="jenis_transaksi">
                        </div>

                        <!-- Sumber Rekening: Kas Tunai vs Bank -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-1.5">
                                2. Rekening Kas yang Digunakan *
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="rekening_kas = '111'; updateNoBukti()"
                                        :class="rekening_kas === '111' ? 'bg-[#1E293B] text-white border-slate-900 shadow-xs font-bold' : 'bg-white text-slate-700 border-[#E5E0D8] hover:bg-slate-50 font-medium'"
                                        class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                    <span>Kas Tunai (111)</span>
                                </button>
                                <button type="button" @click="rekening_kas = '112'; updateNoBukti()"
                                        :class="rekening_kas === '112' ? 'bg-[#1E293B] text-white border-slate-900 shadow-xs font-bold' : 'bg-white text-slate-700 border-[#E5E0D8] hover:bg-slate-50 font-medium'"
                                        class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i data-lucide="landmark" class="w-4 h-4"></i>
                                    <span>Bank BUMDes (112)</span>
                                </button>
                            </div>
                            <input type="hidden" name="rekening_kas" :value="rekening_kas">
                        </div>

                    </div>

                    <!-- Step 2: Kategori Pos Pembukuan (Filtered by Jenis Transaksi) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-bold text-slate-800">
                                3. Pos Anggaran / Kategori Pembukuan *
                            </label>
                            <span class="text-[11px] text-slate-500">
                                Menampilkan akun <strong x-text="jenis_transaksi === 'keluar' ? 'Pengeluaran/Beban' : 'Penerimaan/Pendapatan'"></strong>
                            </span>
                        </div>
                        <select name="kode_akun" x-model="kode_akun" required
                                class="w-full bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl p-3 text-xs font-semibold text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]">
                            
                            <!-- Opsi Khusus Kas Keluar -->
                            <template x-if="jenis_transaksi === 'keluar'">
                                <optgroup label="Pos Pengeluaran & Biaya Operasional">
                                    @foreach($accounts->where('kategori', 'Beban') as $acc)
                                        <option value="{{ $acc->kode_akun }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }}</option>
                                    @endforeach
                                    @foreach($accounts->where('kategori', 'Kewajiban') as $acc)
                                        <option value="{{ $acc->kode_akun }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }} (Kewajiban)</option>
                                    @endforeach
                                </optgroup>
                            </template>

                            <!-- Opsi Khusus Kas Masuk -->
                            <template x-if="jenis_transaksi === 'masuk'">
                                <optgroup label="Pos Penerimaan & Pendapatan">
                                    @foreach($accounts->where('kategori', 'Pendapatan') as $acc)
                                        <option value="{{ $acc->kode_akun }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }}</option>
                                    @endforeach
                                    @foreach($accounts->where('kategori', 'Ekuitas') as $acc)
                                        <option value="{{ $acc->kode_akun }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }} (Modal/Ekuitas)</option>
                                    @endforeach
                                    @foreach($accounts->where('kategori', 'Aset Lancar')->whereNotIn('kode_akun', ['111', '112']) as $acc)
                                        <option value="{{ $acc->kode_akun }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }}</option>
                                    @endforeach
                                </optgroup>
                            </template>

                            <!-- Semua Akun Cadangan -->
                            <optgroup label="Semua Akun Lainnya">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->kode_akun }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }} ({{ $acc->kategori }})</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <!-- Step 3: Nominal Transaksi & Chip Sat-Set Tambah Cepat -->
                    <div class="space-y-2">
                        <label class="block font-bold text-slate-800">
                            4. Nominal Transaksi (Rp) *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                            <input type="number" id="nominal-input" name="nominal" x-model.number="nominal" required placeholder="0" min="1"
                                   class="w-full pl-12 pr-4 py-3 bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl text-lg font-mono font-extrabold text-[#C62828] focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>

                        <!-- Chip Nominal Cepat (Sat-Set Quick Amount) -->
                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <span class="text-[11px] text-slate-500 font-medium mr-1">Tambah Cepat:</span>
                            <button type="button" @click="addNominal(50000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono font-bold text-[11px] transition cursor-pointer">+50 rb</button>
                            <button type="button" @click="addNominal(100000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono font-bold text-[11px] transition cursor-pointer">+100 rb</button>
                            <button type="button" @click="addNominal(250000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono font-bold text-[11px] transition cursor-pointer">+250 rb</button>
                            <button type="button" @click="addNominal(500000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono font-bold text-[11px] transition cursor-pointer">+500 rb</button>
                            <button type="button" @click="addNominal(1000000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono font-bold text-[11px] transition cursor-pointer">+1 jt</button>
                            <button type="button" @click="clearNominal()" class="px-2 py-1 rounded-lg bg-red-50 hover:bg-red-100 text-[#C62828] font-bold text-[11px] transition cursor-pointer">Reset</button>
                        </div>
                    </div>

                    <!-- Step 4: Tanggal & Nomor Bukti Nota -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-semibold text-slate-700">Tanggal Transaksi *</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="setTanggal('{{ date('Y-m-d') }}')" class="text-[10px] text-[#C62828] hover:underline font-bold">Hari Ini</button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" @click="setTanggal('{{ date('Y-m-d', strtotime('-1 day')) }}')" class="text-[10px] text-slate-500 hover:underline">Kemarin</button>
                                </div>
                            </div>
                            <input type="date" name="tanggal" x-model="tanggal" required
                                   class="w-full bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:bg-white focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor Bukti / Nota (Otomatis)</label>
                            <input type="text" name="no_bukti" x-model="no_bukti" required
                                   class="w-full bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl p-2.5 text-xs font-mono font-bold text-slate-800 focus:bg-white focus:ring-[#C62828] focus:border-[#C62828]">
                        </div>
                    </div>

                    <!-- Step 5: Uraian Transaksi Kas -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Uraian / Keterangan Transaksi *</label>
                        <textarea name="keterangan" x-model="keterangan" required rows="2"
                                  placeholder="Contoh: Pembelian ATK kantor BUMDesa..."
                                  class="w-full bg-[#FAF8F5] border border-[#E5E0D8] rounded-xl p-2.5 text-xs focus:bg-white focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828]"></textarea>
                    </div>

                    <!-- Step 6: Bukti Foto / Nota Fisik (Opsional) -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Upload Foto Nota / Bukti Fisik (Opsional)</label>
                        <input type="file" name="bukti_foto" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFEBEE] file:text-[#C62828] hover:file:bg-red-100 cursor-pointer">
                    </div>

                    <!-- Step 7: Tombol Eksekusi Sat-Set -->
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3.5 bg-[#C62828] hover:bg-[#9A1B1B] text-white font-extrabold rounded-xl text-sm shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer focus:ring-2 focus:ring-[#C62828]">
                            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            <span>Simpan Transaksi Kas Pusat (Sat-Set)</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>

        <!-- Right Column: Live Preview & Riwayat Cepat -->
        <div class="xl:col-span-4 space-y-5">

            <!-- Live Ringkasan Transaksi yang Akan Dicatat -->
            <div class="bg-white p-5 rounded-2xl border border-[#E5E0D8] shadow-xs space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="eye" class="w-4 h-4 text-[#C62828]"></i>
                        <h3 class="text-sm font-bold text-slate-900">Ringkasan yang Akan Dicatat</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
                          :class="jenis_transaksi === 'keluar' ? 'bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]' : 'bg-[#F0FDF4] text-[#15803D] border border-[#BBF7D0]'"
                          x-text="jenis_transaksi === 'keluar' ? 'Kas Keluar' : 'Kas Masuk'">
                    </span>
                </div>

                <div class="p-4 bg-[#FAF8F5] rounded-xl border border-[#E5E0D8] space-y-2.5 text-xs">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Nominal:</span>
                        <span class="font-mono font-extrabold text-base text-slate-900" x-text="'Rp ' + formatRupiah(nominal)"></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Rekening Kas:</span>
                        <span class="font-semibold text-slate-900" x-text="rekening_kas === '112' ? 'Bank BUMDesa (112)' : 'Kas Tunai (111)'"></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Kode Pos:</span>
                        <span class="font-mono font-bold text-slate-900" x-text="kode_akun"></span>
                    </div>
                    <div class="pt-2 border-t border-[#E5E0D8]">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Uraian:</span>
                        <p class="font-medium text-slate-800 line-clamp-2" x-text="keterangan || '-'"></p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-[#F0FDF4] border border-[#BBF7D0] flex items-center gap-2 text-[#15803D] text-[11px] font-semibold">
                    <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
                    <span>Langsung disetujui & otomatis masuk ke laporan 18 sheet resmi BUMDesa.</span>
                </div>
            </div>

            <!-- Riwayat Transaksi Kas Pusat Terakhir (Dengan Fitur Salin Cepat) -->
            <div class="bg-white rounded-2xl border border-[#E5E0D8] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[#E5E0D8] bg-[#FAF8F5] flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">Riwayat Kas Pusat Terakhir</h3>
                        <p class="text-[10px] text-slate-500">Klik "Salin" untuk mengulang transaksi sejenis</p>
                    </div>
                    <span class="text-[10px] font-mono text-slate-500 font-bold">{{ $recentTransactions->count() }} Entri</span>
                </div>

                <div class="divide-y divide-slate-100 text-xs max-h-[460px] overflow-y-auto">
                    @forelse($recentTransactions as $tx)
                        <div class="p-3.5 hover:bg-slate-50/80 transition space-y-1.5 group">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($tx->tanggal)->format('d/m/Y') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $tx->jenis_transaksi === 'masuk' ? 'bg-[#F0FDF4] text-[#15803D] border border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]' }}">
                                    {{ $tx->jenis_transaksi === 'masuk' ? 'Masuk' : 'Keluar' }}
                                </span>
                            </div>
                            
                            <p class="font-semibold text-slate-800 line-clamp-1">{{ $tx->keterangan }}</p>
                            
                            <div class="flex items-center justify-between pt-1">
                                <span class="font-mono font-bold {{ $tx->jenis_transaksi === 'masuk' ? 'text-[#15803D]' : 'text-[#DC2626]' }}">
                                    {{ $tx->jenis_transaksi === 'masuk' ? '+' : '-' }} Rp {{ number_format($tx->nominal, 0, ',', '.') }}
                                </span>

                                <!-- Tombol Salin Cepat ke Form -->
                                <button type="button" 
                                        @click="copyFromHistory(@json($tx))"
                                        class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 hover:text-[#C62828] border border-[#E5E0D8] rounded-lg text-[10px] font-bold flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                    <span>Salin</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs italic">Belum ada transaksi kas yang dicatat.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
function umumSatSetApp() {
    const today = '{{ date('Y-m-d') }}';
    const rand = Math.floor(10 + Math.random() * 90);

    return {
        jenis_transaksi: 'keluar',
        rekening_kas: '111',
        kode_akun: '512',
        nominal: 0,
        tanggal: today,
        no_bukti: `K-PST-{{ date('dmy') }}-${rand}`,
        keterangan: 'Pembelian ATK dan Perlengkapan Kantor BUMDesa',

        presets: [
            { label: 'ATK & Kantor', jenis: 'keluar', rekening: '111', akun: '512', ket: 'Pembelian ATK dan Perlengkapan Kantor BUMDesa' },
            { label: 'Listrik & Operasional', jenis: 'keluar', rekening: '111', akun: '512', ket: 'Pembayaran Tagihan Listrik dan Komunikasi Kantor' },
            { label: 'Konsumsi Rapat', jenis: 'keluar', rekening: '111', akun: '512', ket: 'Biaya Konsumsi Rapat Koordinasi BUMDesa' },
            { label: 'Transport & BBM', jenis: 'keluar', rekening: '111', akun: '512', ket: 'Transport dan BBM Operasional Dinas BUMDesa' },
            { label: 'Admin & Pajak Bank', jenis: 'keluar', rekening: '112', akun: '513', ket: 'Biaya Administrasi dan Pajak Bank BUMDesa' },
            { label: 'Bunga Bank', jenis: 'masuk', rekening: '112', akun: '413', ket: 'Penerimaan Pendapatan Bunga Bank BUMDesa' },
            { label: 'Setoran Unit Wifi', jenis: 'masuk', rekening: '111', akun: '417', ket: 'Penerimaan Setoran Hasil Usaha Unit Wifi' },
            { label: 'Setoran Unit USP', jenis: 'masuk', rekening: '111', akun: '414', ket: 'Penerimaan Setoran Hasil Usaha Unit Simpan Pinjam USP' },
            { label: 'Setoran Unit Kebun', jenis: 'masuk', rekening: '111', akun: '415', ket: 'Penerimaan Setoran Hasil Usaha Unit Kebun Nanas' },
            { label: 'Honor Pengurus', jenis: 'keluar', rekening: '111', akun: '519', ket: 'Penyaluran Honor dan Insentif Pengurus BUMDesa' },
        ],

        applyPreset(p) {
            this.jenis_transaksi = p.jenis;
            this.rekening_kas = p.rekening;
            this.kode_akun = p.akun;
            this.keterangan = p.ket;
            this.updateNoBukti();
            this.$nextTick(() => {
                const el = document.getElementById('nominal-input');
                if (el) {
                    el.focus();
                    el.select();
                }
            });
        },

        setJenis(val) {
            this.jenis_transaksi = val;
            if (val === 'keluar') {
                if (!['511', '512', '513', '516', '517', '519', '520'].includes(this.kode_akun)) {
                    this.kode_akun = '512';
                }
            } else {
                if (!['411', '412', '413', '414', '415', '416', '417', '311'].includes(this.kode_akun)) {
                    this.kode_akun = '413';
                }
            }
            this.updateNoBukti();
        },

        addNominal(amount) {
            this.nominal = (Number(this.nominal) || 0) + amount;
        },

        clearNominal() {
            this.nominal = 0;
        },

        setTanggal(val) {
            this.tanggal = val;
        },

        updateNoBukti() {
            const prefix = this.jenis_transaksi === 'masuk' 
                ? (this.rekening_kas === '112' ? 'M-BANK' : 'M-PST')
                : (this.rekening_kas === '112' ? 'K-BANK' : 'K-PST');
            const rand = Math.floor(10 + Math.random() * 90);
            this.no_bukti = `${prefix}-{{ date('dmy') }}-${rand}`;
        },

        copyFromHistory(item) {
            this.jenis_transaksi = item.jenis_transaksi;
            this.kode_akun = item.kode_akun;
            this.nominal = Number(item.nominal) || 0;
            this.keterangan = item.keterangan;
            if (item.data_tambahan && item.data_tambahan.rekening_kas) {
                this.rekening_kas = item.data_tambahan.rekening_kas;
            }
            this.updateNoBukti();
            if (window.BumdesAlert) {
                window.BumdesAlert.toastSuccess('Data transaksi disalin ke form!');
            }
            this.$nextTick(() => {
                const el = document.getElementById('nominal-input');
                if (el) {
                    el.focus();
                    el.select();
                }
            });
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        }
    };
}
</script>
@endpush