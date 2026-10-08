<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\MasterEntriUnit;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class TransaksiController extends Controller
{
    /**
     * Tampilan Mobile-Friendly Guided-Input untuk Bendahara Unit Usaha.
     */
    public function unitInput(Request $request)
    {
        $units = UnitUsaha::where('kode_unit', '!=', 'PUSAT')->get();
        $selectedUnitId = (int) $request->query('id_unit', $units->first()?->id_unit ?? 2);
        $selectedUnit = UnitUsaha::find($selectedUnitId) ?? $units->first();

        // Ambil akun yang relevan untuk unit terpilih
        $accounts = ChartOfAccount::where('id_unit', $selectedUnit->id_unit)
            ->orWhereNull('id_unit')
            ->orderBy('kode_akun', 'asc')
            ->get();

        // Transaksi terakhir unit
        $recentTransactions = Transaksi::where('id_unit', $selectedUnit->id_unit)
            ->with(['coa'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_transaksi', 'desc')
            ->take(8)
            ->get();

        // Entri Master tersimpan resmi di tabel master_entri_units
        $savedMasters = MasterEntriUnit::where('id_unit', $selectedUnit->id_unit)
            ->where('is_active', true)
            ->orderBy('id_master', 'desc')
            ->get();

        // Data nasabah/pelanggan/komoditas untuk dropdown autofill
        $realCustomers = [];
        $seen = [];

        // 1. Prioritaskan entri master yang sudah didaftarkan khusus oleh bendahara
        foreach ($savedMasters as $sm) {
            $seen[$sm->nama] = true;
            $meta = $sm->metadata ?? [];
            if ($selectedUnit->kode_unit === 'WIFI') {
                $realCustomers[] = [
                    'id_master' => $sm->id_master,
                    'is_saved_master' => true,
                    'nama' => $sm->nama,
                    'no' => $sm->kode_referensi ?? '-',
                    'dusun' => $sm->kategori_sub ?? 'Kuala Alam',
                    'paket' => $meta['paket'] ?? '3 MBps',
                    'nominal' => (float) $sm->nominal_standar,
                    'biaya_jasa' => (float) ($meta['biaya_jasa'] ?? ($sm->nominal_standar - 7000)),
                    'safety_peralatan' => (float) ($sm->nominal_tambahan ?: 7000),
                    'no_hp' => $meta['no_hp'] ?? '-',
                ];
            } elseif ($selectedUnit->kode_unit === 'USP') {
                $realCustomers[] = [
                    'id_master' => $sm->id_master,
                    'is_saved_master' => true,
                    'nama' => $sm->nama,
                    'sppk' => $sm->kode_referensi ?? '-',
                    'usaha' => $sm->kategori_sub ?? 'Usaha Warga',
                    'angsuran_pokok' => (float) $sm->nominal_standar,
                    'nominal_bunga' => (float) $sm->nominal_tambahan,
                    'angsuran_ke' => $meta['angsuran_ke'] ?? '1',
                ];
            } elseif ($selectedUnit->kode_unit === 'KEBUN') {
                $realCustomers[] = [
                    'id_master' => $sm->id_master,
                    'is_saved_master' => true,
                    'uraian' => 'Panen ' . $sm->nama . ' (' . ($sm->kategori_sub ?? 'Tahap 1') . ')',
                    'nominal' => (float) ($sm->nominal_standar * ($meta['jumlah'] ?? 1)),
                    'komoditas' => $sm->nama,
                    'tahap_panen' => $sm->kategori_sub ?? 'Tahap 1',
                    'satuan' => $meta['satuan'] ?? 'Buah',
                    'jumlah' => (float) ($meta['jumlah'] ?? 100),
                    'harga' => (float) $sm->nominal_standar,
                    'pembeli' => $meta['pembeli'] ?? 'Pasar Bengkalis',
                ];
            }
        }

        // 2. Gabungkan dengan data riwayat transaksi riil agar selalu komprehensif
        if ($selectedUnit->kode_unit === 'WIFI') {
            $wifiTx = Transaksi::where('id_unit', $selectedUnit->id_unit)
                ->where('jenis_transaksi', 'masuk')
                ->whereNotNull('data_tambahan')
                ->orderBy('id_transaksi', 'desc')
                ->get();
            foreach ($wifiTx as $w) {
                $m = $w->data_tambahan;
                $nama = trim($m['nama_customer'] ?? '');
                if (!empty($nama) && !isset($seen[$nama])) {
                    $seen[$nama] = true;
                    $realCustomers[] = [
                        'id_master' => null,
                        'is_saved_master' => false,
                        'nama' => $nama,
                        'no' => $m['no_customer'] ?? ('WF-' . str_pad(count($seen), 2, '0', STR_PAD_LEFT)),
                        'dusun' => $m['dusun'] ?? 'Kuala Alam',
                        'paket' => $m['paket'] ?? '3 MBps',
                        'nominal' => (float) $w->nominal,
                        'biaya_jasa' => (float) ($m['biaya_jasa'] ?? ($w->nominal - 7000)),
                        'safety_peralatan' => (float) ($m['safety_peralatan'] ?? 7000),
                        'no_hp' => $m['no_hp'] ?? '-',
                    ];
                }
            }
        } elseif ($selectedUnit->kode_unit === 'USP') {
            $uspTx = Transaksi::where('id_unit', $selectedUnit->id_unit)
                ->where('jenis_transaksi', 'masuk')
                ->whereNotNull('data_tambahan')
                ->orderBy('id_transaksi', 'desc')
                ->get();
            foreach ($uspTx as $u) {
                $m = $u->data_tambahan;
                $nama = trim($m['nama_pemanfaat'] ?? '');
                if (!empty($nama) && !isset($seen[$nama])) {
                    $seen[$nama] = true;
                    $realCustomers[] = [
                        'id_master' => null,
                        'is_saved_master' => false,
                        'nama' => $nama,
                        'sppk' => $m['no_sppk'] ?? ('SPPK-' . str_pad(count($seen), 3, '0', STR_PAD_LEFT)),
                        'usaha' => $m['jenis_usaha'] ?? 'Usaha Warga',
                        'angsuran_pokok' => (float) ($m['angsuran_pokok'] ?? 300000),
                        'nominal_bunga' => (float) ($m['nominal_bunga'] ?? 50000),
                        'angsuran_ke' => $m['angsuran_ke'] ?? '1',
                    ];
                }
            }
        } elseif ($selectedUnit->kode_unit === 'KEBUN') {
            $kbnTx = Transaksi::where('id_unit', $selectedUnit->id_unit)
                ->orderBy('id_transaksi', 'desc')
                ->get();
            foreach ($kbnTx as $k) {
                $uraian = trim(str_replace('[Kebun] ', '', $k->keterangan));
                if (!empty($uraian) && !isset($seen[$uraian])) {
                    $seen[$uraian] = true;
                    $m = $k->data_tambahan ?? [];
                    $komoditas = $m['komoditas'] ?? 'Nanas Madu';
                    $realCustomers[] = [
                        'id_master' => null,
                        'is_saved_master' => false,
                        'uraian' => $uraian,
                        'nominal' => (float) $k->nominal,
                        'komoditas' => $komoditas,
                        'tahap_panen' => $m['tahap_panen'] ?? 'Tahap 1',
                        'satuan' => $m['satuan'] ?? 'Buah',
                        'jumlah' => (float) ($m['jumlah_buah'] ?? 100),
                        'harga' => (float) ($m['harga_satuan'] ?? 3500),
                        'pembeli' => $m['pembeli'] ?? 'Pasar Bengkalis',
                    ];
                }
            }
        }

        return view('transaksi.unit-input', compact('units', 'selectedUnit', 'accounts', 'recentTransactions', 'realCustomers', 'savedMasters'));
    }

    /**
     * Simpan Transaksi dari Form Bendahara Unit Usaha.
     */
    public function storeUnitTransaction(Request $request)
    {
        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'kode_akun' => 'nullable|exists:chart_of_accounts,kode_akun',
            'tanggal' => 'required|date',
            'jenis_transaksi' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|gt:0',
            'keterangan' => 'required|string|max:255',
            'bukti_foto' => 'nullable|image|max:5120',
        ]);

        $unit = UnitUsaha::findOrFail($request->id_unit);

        // Auto-assign akun standar jika tidak diisi manual
        $kodeAkun = $request->kode_akun;
        if (empty($kodeAkun)) {
            if ($unit->kode_unit === 'WIFI') {
                $kodeAkun = ($request->jenis_transaksi === 'masuk') ? '41-WF' : '51-WF';
            } elseif ($unit->kode_unit === 'USP') {
                $kodeAkun = ($request->jenis_transaksi === 'masuk') ? '41-USP' : '51-USP';
            } elseif ($unit->kode_unit === 'KEBUN') {
                $kodeAkun = ($request->jenis_transaksi === 'masuk') ? '41-KBN' : '51-KBN';
            } else {
                $kodeAkun = ($request->jenis_transaksi === 'masuk') ? '413' : '512';
            }
        }

        $user = User::where('id_unit', $unit->id_unit)->where('role', 'bendahara_unit')->first()
            ?? User::where('role', 'bendahara_umum')->first();

        $data = [
            'id_unit' => $unit->id_unit,
            'id_user' => $user->id,
            'tanggal' => $request->tanggal,
            'jenis_transaksi' => $request->jenis_transaksi,
            'kode_akun' => $kodeAkun,
            'nominal' => (float) $request->nominal,
            'keterangan' => $request->keterangan,
            'status' => 'menunggu',
        ];

        // Metadata tambahan sesuai unit usaha & input mandiri
        $metadata = [];
        $metadata['mode_input'] = $request->input('mode_input', 'mandiri');
        $metadata['is_mandiri'] = $request->boolean('is_mandiri', false);

        if ($unit->kode_unit === 'WIFI') {
            $metadata['no_customer'] = $request->input('no_customer', 'WF-' . rand(10, 99));
            $metadata['nama_customer'] = $request->input('nama_customer', 'Warga Pelanggan');
            $metadata['dusun'] = $request->input('dusun', 'Kuala Alam');
            $metadata['no_hp'] = $request->input('no_hp', '-');
            $metadata['paket'] = $request->input('paket', '3 MBps');
            $metadata['biaya_jasa'] = (float) $request->input('biaya_jasa', 0);
            $metadata['safety_peralatan'] = (float) $request->input('safety_peralatan', 7000);
            $metadata['no_bukti'] = $request->input('no_bukti', 'M-' . rand(10, 99));
        } elseif ($unit->kode_unit === 'USP') {
            $metadata['no_sppk'] = $request->input('no_sppk', 'SPPK-' . rand(100, 999));
            $metadata['nama_pemanfaat'] = $request->input('nama_pemanfaat', 'Warga Pemanfaat');
            $metadata['jenis_usaha'] = $request->input('jenis_usaha', 'Usaha Warga');
            $metadata['angsuran_ke'] = $request->input('angsuran_ke', '1');
            $metadata['angsuran_pokok'] = (float) $request->input('angsuran_pokok', 0);
            $metadata['nominal_bunga'] = (float) $request->input('nominal_bunga', 0);
            $metadata['bunga_persen'] = (float) $request->input('bunga_persen', 2.0);
            $metadata['metode_bunga'] = $request->input('metode_bunga', 'persen');
            $metadata['no_bukti'] = $request->input('no_bukti', 'M-' . rand(100, 999));
        } elseif ($unit->kode_unit === 'KEBUN') {
            $metadata['komoditas'] = $request->input('komoditas', 'Penjualan Nanas Panen');
            $metadata['lokasi_blok'] = $request->input('lokasi_blok', 'Kebun Kuala Alam');
            $metadata['tahap_panen'] = $request->input('tahap_panen', 'Tahap 1');
            $metadata['satuan'] = $request->input('satuan', 'Buah');
            $metadata['jumlah_buah'] = (float) $request->input('jumlah_buah', 0);
            $metadata['harga_satuan'] = (float) $request->input('harga_satuan', 0);
            $metadata['pembeli'] = $request->input('pembeli', 'Pasar Bengkalis');
            $metadata['no_bukti'] = $request->input('no_bukti', 'M-' . rand(10, 99));
        }

        $data['data_tambahan'] = $metadata;

        if ($request->hasFile('bukti_foto')) {
            $file = $request->file('bukti_foto');
            $filename = 'bukti_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts', $filename, 'public');
            $data['bukti_transaksi'] = '/storage/' . $path;
        }

        $tx = Transaksi::create($data);

        Alert::success('Transaksi Unit Dicatat', 'Transaksi sebesar Rp ' . number_format($tx->nominal, 0, ',', '.') . ' telah tersimpan dan masuk antrean validasi Bendahara Umum.');

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi unit berhasil dicatat dan masuk ke antrean validasi!',
                'data' => [
                    'id_transaksi' => $tx->id_transaksi,
                    'tanggal' => $tx->tanggal ? \Carbon\Carbon::parse($tx->tanggal)->format('d/m/Y') : '-',
                    'keterangan' => $tx->keterangan,
                    'nominal' => $tx->nominal,
                    'jenis_transaksi' => $tx->jenis_transaksi,
                    'status' => $tx->status,
                ],
            ]);
        }

        $redirectParams = ['id_unit' => $unit->id_unit];
        if ($request->filled('mode')) {
            $redirectParams['mode'] = $request->input('mode');
        }

        return redirect()->route('transaksi.unit-input', $redirectParams)
            ->with('success', 'Transaksi unit berhasil dicatat dan masuk ke antrean validasi Bendahara Umum!');
    }

    /**
     * Tambah Entri Master Unit Usaha (Pelanggan Wifi, Pemanfaat USP, Komoditas Kebun, dll)
     * Tersimpan permanen ke tabel master_entri_units dan otomatis muncul di dropdown pilihan.
     */
    public function storeMasterEntry(Request $request)
    {
        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'jenis_entri' => 'required|string|max:50',
            'nama' => 'required|string|max:150',
            'kode_referensi' => 'nullable|string|max:50',
            'kategori_sub' => 'nullable|string|max:100',
            'nominal_standar' => 'nullable|numeric|gte:0',
            'nominal_tambahan' => 'nullable|numeric|gte:0',
        ]);

        $unit = UnitUsaha::findOrFail($request->id_unit);

        $metadata = [];
        if ($unit->kode_unit === 'WIFI') {
            $metadata['paket'] = $request->input('paket', '3 MBps');
            $metadata['biaya_jasa'] = (float) $request->input('biaya_jasa', 168000);
            $metadata['no_hp'] = $request->input('no_hp', '-');
        } elseif ($unit->kode_unit === 'USP') {
            $metadata['jenis_usaha'] = $request->input('jenis_usaha', 'Usaha Warga');
            $metadata['angsuran_ke'] = $request->input('angsuran_ke', '1');
            $metadata['metode_bunga'] = $request->input('metode_bunga', 'persen');
        } elseif ($unit->kode_unit === 'KEBUN') {
            $metadata['satuan'] = $request->input('satuan', 'Buah');
            $metadata['jumlah'] = (float) $request->input('jumlah', 100);
            $metadata['pembeli'] = $request->input('pembeli', 'Pasar Bengkalis');
        }

        $master = MasterEntriUnit::create([
            'id_unit' => $unit->id_unit,
            'jenis_entri' => $request->jenis_entri,
            'kode_referensi' => $request->kode_referensi,
            'nama' => $request->nama,
            'kategori_sub' => $request->kategori_sub,
            'nominal_standar' => (float) ($request->nominal_standar ?? 0),
            'nominal_tambahan' => (float) ($request->nominal_tambahan ?? 0),
            'metadata' => $metadata,
            'is_active' => true,
        ]);

        Alert::success('Entri Berhasil Ditambahkan', 'Data "' . $master->nama . '" telah tersimpan permanen dan kini tersedia di daftar pilihan.');

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Entri baru berhasil ditambahkan ke daftar pilihan!',
                'data' => $master,
            ]);
        }

        return redirect()->route('transaksi.unit-input', ['id_unit' => $unit->id_unit]);
    }

    /**
     * Hapus Entri Master Unit Usaha.
     */
    public function destroyMasterEntry(Request $request, $id)
    {
        $master = MasterEntriUnit::findOrFail($id);
        $unitId = $master->id_unit;
        $nama = $master->nama;
        $master->delete();

        Alert::success('Entri Dihapus', 'Data "' . $nama . '" telah dihapus dari daftar pilihan.');

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Entri berhasil dihapus dari daftar!',
            ]);
        }

        return redirect()->route('transaksi.unit-input', ['id_unit' => $unitId]);
    }

    /**
     * Tampilan Input Bendahara Umum (Buku Kas BUMDesa Pusat & Memorial).
     */
    public function umumInput(Request $request)
    {
        $pusatUnit = UnitUsaha::where('kode_unit', 'PUSAT')->first();
        $accounts = ChartOfAccount::where('id_unit', $pusatUnit?->id_unit ?? 1)
            ->orWhereNull('id_unit')
            ->orderBy('kode_akun', 'asc')
            ->get();

        $recentTransactions = Transaksi::where('id_unit', $pusatUnit?->id_unit ?? 1)
            ->with(['coa'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_transaksi', 'desc')
            ->take(15)
            ->get();

        $saldoKasTunai = (float) JurnalDetail::where('kode_akun', '111')->sum(DB::raw('debit - kredit'));
        $saldoBank = (float) JurnalDetail::where('kode_akun', '112')->sum(DB::raw('debit - kredit'));

        return view('transaksi.umum-input', compact('pusatUnit', 'accounts', 'recentTransactions', 'saldoKasTunai', 'saldoBank'));
    }

    /**
     * Simpan Transaksi Langsung dari Bendahara Umum (Kas Pusat / Bank / Memorial).
     */
    public function storeUmumTransaction(Request $request)
    {
        $request->validate([
            'kode_akun' => 'required|exists:chart_of_accounts,kode_akun',
            'rekening_kas' => 'nullable|in:111,112',
            'tanggal' => 'required|date',
            'jenis_transaksi' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|gt:0',
            'keterangan' => 'required|string|max:255',
            'bukti_foto' => 'nullable|image|max:5120',
        ]);

        $pusatUnit = UnitUsaha::where('kode_unit', 'PUSAT')->firstOrFail();
        $bUmum = User::where('role', 'bendahara_umum')->firstOrFail();
        $rekeningKas = $request->input('rekening_kas', '111');

        $data = [
            'id_unit' => $pusatUnit->id_unit,
            'id_user' => $bUmum->id,
            'tanggal' => $request->tanggal,
            'jenis_transaksi' => $request->jenis_transaksi,
            'kode_akun' => $request->kode_akun,
            'nominal' => (float) $request->nominal,
            'keterangan' => $request->keterangan,
            'status' => 'disetujui', // Langsung tervalidasi karena otoritas Bendahara Umum
            'catatan_validasi' => 'Diinput langsung oleh Bendahara Umum (Pusat BUMDesa)',
            'data_tambahan' => [
                'no_bukti' => $request->input('no_bukti', ($request->jenis_transaksi === 'masuk' ? 'M-PST-' : 'K-PST-') . rand(10, 99)),
                'kategori' => $request->input('kategori_beban', 'Operasional BUMDesa'),
                'rekening_kas' => $rekeningKas,
            ],
        ];

        if ($request->hasFile('bukti_foto')) {
            $file = $request->file('bukti_foto');
            $filename = 'bukti_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts', $filename, 'public');
            $data['bukti_transaksi'] = '/storage/' . $path;
        }

        $tx = Transaksi::create($data);

        // Bukukan jurnal umum berpasangan
        $jurnal = Jurnal::create([
            'id_transaksi' => $tx->id_transaksi,
            'tanggal' => $tx->tanggal,
            'keterangan' => $tx->keterangan,
        ]);

        if ($tx->jenis_transaksi === 'masuk') {
            JurnalDetail::create([
                'id_jurnal' => $jurnal->id_jurnal,
                'kode_akun' => $rekeningKas,
                'debit' => $tx->nominal,
                'kredit' => 0,
            ]);
            JurnalDetail::create([
                'id_jurnal' => $jurnal->id_jurnal,
                'kode_akun' => $tx->kode_akun,
                'debit' => 0,
                'kredit' => $tx->nominal,
            ]);
        } else {
            JurnalDetail::create([
                'id_jurnal' => $jurnal->id_jurnal,
                'kode_akun' => $tx->kode_akun,
                'debit' => $tx->nominal,
                'kredit' => 0,
            ]);
            JurnalDetail::create([
                'id_jurnal' => $jurnal->id_jurnal,
                'kode_akun' => $rekeningKas,
                'debit' => 0,
                'kredit' => $tx->nominal,
            ]);
        }

        Alert::success('Transaksi Kas Pusat Dibukukan', 'Transaksi sebesar Rp ' . number_format($tx->nominal, 0, ',', '.') . ' berhasil dicatat dan dibukukan ke 18 Sheet Laporan!');

        return redirect()->route('transaksi.umum-input')
            ->with('success', 'Transaksi BUMDesa Pusat berhasil dicatat dan langsung dibukukan ke Jurnal Umum & Laporan 18 Sheet!');
    }
}
