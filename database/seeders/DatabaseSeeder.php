<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitUsaha;
use App\Models\User;
use App\Models\ChartOfAccount;
use App\Models\Transaksi;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\LaporanKeuangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Bersihkan Data Lama Secara Idempoten
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        JurnalDetail::truncate();
        Jurnal::truncate();
        Transaksi::truncate();
        LaporanKeuangan::truncate();
        ChartOfAccount::truncate();
        User::truncate();
        UnitUsaha::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed Unit Usaha Resmi BUMDesa Kuala Alam
        $pusat = UnitUsaha::create([
            'kode_unit' => 'PUSAT',
            'nama_unit' => 'Kantor Pengelola BUMDesa (Pusat)',
            'jenis_unit' => 'pusat',
            'keterangan' => 'Manajemen pusat dan konsolidasi BUMDesa Kuala Alam',
        ]);

        $usp = UnitUsaha::create([
            'kode_unit' => 'USP',
            'nama_unit' => 'Unit Usaha Simpan Pinjam "Bathin Alam"',
            'jenis_unit' => 'simpan_pinjam',
            'keterangan' => 'Pelayanan pinjaman permodalan usaha mikro dengan jasa 2%',
        ]);

        $wifi = UnitUsaha::create([
            'kode_unit' => 'WIFI',
            'nama_unit' => 'Unit Jasa Internet & Wifi Kuala Alam',
            'jenis_unit' => 'jasa_wifi',
            'keterangan' => 'Penyediaan jaringan internet desa berkecepatan tinggi',
        ]);

        $kebun = UnitUsaha::create([
            'kode_unit' => 'KEBUN',
            'nama_unit' => 'Unit Perkebunan & Pertanian Desa',
            'jenis_unit' => 'perkebunan',
            'keterangan' => 'Pengelolaan komoditas perkebunan nanas dan pertanian produktif desa',
        ]);

        // 2. Seed Pengurus & Personel Resmi
        $direktur = User::create([
            'nama' => 'Drs. H. M. Syukri (Direktur)',
            'email' => 'direktur@kualaalam.desa.id',
            'role' => 'direktur',
            'id_unit' => $pusat->id_unit,
            'password' => Hash::make('password123'),
        ]);

        $bUmum = User::create([
            'nama' => 'Raudhah, S.E. (Bendahara Umum)',
            'email' => 'bendahara.umum@kualaalam.desa.id',
            'role' => 'bendahara_umum',
            'id_unit' => $pusat->id_unit,
            'password' => Hash::make('password123'),
        ]);

        $bUsp = User::create([
            'nama' => 'Zubaidah (Bendahara USP)',
            'email' => 'bendahara.usp@kualaalam.desa.id',
            'role' => 'bendahara_unit',
            'id_unit' => $usp->id_unit,
            'password' => Hash::make('password123'),
        ]);

        $bWifi = User::create([
            'nama' => 'Muhammad Rifqi (Bendahara Wifi)',
            'email' => 'bendahara.wifi@kualaalam.desa.id',
            'role' => 'bendahara_unit',
            'id_unit' => $wifi->id_unit,
            'password' => Hash::make('password123'),
        ]);

        $bKebun = User::create([
            'nama' => 'Suwardi (Bendahara Kebun)',
            'email' => 'bendahara.kebun@kualaalam.desa.id',
            'role' => 'bendahara_unit',
            'id_unit' => $kebun->id_unit,
            'password' => Hash::make('password123'),
        ]);

        // 3. Seed Bagan Akun Standar Resmi (Chart of Accounts)
        $accounts = [
            // PUSAT / BUMDESA KONSOLIDASI (Sesuai Standar Pemerintah & SAK ETAP Bengkalis)
            ['111', 'Kas BUMDesa', 'Aset Lancar', 'Debit', $pusat->id_unit],
            ['112', 'Bank BUMDesa', 'Aset Lancar', 'Debit', $pusat->id_unit],
            ['113', 'Modal Usaha Ke Unit Usaha', 'Aset Lancar', 'Debit', $pusat->id_unit],
            ['117', 'Inventaris BUMDesa', 'Aset Tetap', 'Debit', $pusat->id_unit],
            ['118', 'Akumulasi Penyusutan Inventaris', 'Aset Tetap', 'Kredit', $pusat->id_unit],
            ['212', 'Hutang Pihak Lain BUMDesa', 'Kewajiban', 'Kredit', $pusat->id_unit],
            ['213', 'Titipan', 'Kewajiban', 'Kredit', $pusat->id_unit],
            ['214', 'PADes (Pendapatan Asli Desa)', 'Kewajiban', 'Kredit', $pusat->id_unit],
            ['215', 'Utang Usaha', 'Kewajiban', 'Kredit', $pusat->id_unit],
            ['216', 'Utang Pajak', 'Kewajiban', 'Kredit', $pusat->id_unit],
            ['217', 'Titipan Gebyar / Cadangan Biaya Unit', 'Kewajiban', 'Kredit', $pusat->id_unit],
            ['218', 'Tunjangan Kinerja BUMDesa', 'Kewajiban', 'Kredit', $pusat->id_unit],
            ['311', 'Penyertaan Modal Desa', 'Ekuitas', 'Kredit', $pusat->id_unit],
            ['312', 'Modal Dari Pihak Lain', 'Ekuitas', 'Kredit', $pusat->id_unit],
            ['313', 'Modal Cadangan Pengembangan Usaha', 'Ekuitas', 'Kredit', $pusat->id_unit],
            ['314', 'Modal Dari Laba', 'Ekuitas', 'Kredit', $pusat->id_unit],
            ['315', 'Akumulasi Laba BUMDesa', 'Ekuitas', 'Kredit', $pusat->id_unit],
            ['411', 'Pendapatan Penyertaan Modal Operasional', 'Pendapatan', 'Kredit', $pusat->id_unit],
            ['412', 'Pendapatan Tak Terduga', 'Pendapatan', 'Kredit', $pusat->id_unit],
            ['413', 'Bunga Bank BUMDesa', 'Pendapatan', 'Kredit', $pusat->id_unit],
            ['414', 'Pendapatan dari Unit USP', 'Pendapatan', 'Kredit', $pusat->id_unit],
            ['415', 'Pendapatan dari Unit Pertanian / Kebun', 'Pendapatan', 'Kredit', $pusat->id_unit],
            ['416', 'Pendapatan dari Unit Penyewaan', 'Pendapatan', 'Kredit', $pusat->id_unit],
            ['417', 'Pendapatan dari Unit Jasa / Wifi', 'Pendapatan', 'Kredit', $pusat->id_unit],
            ['511', 'Pengeluaran Tak Terduga', 'Beban', 'Debit', $pusat->id_unit],
            ['512', 'Operasional BUMDesa (ATK & Kantor)', 'Beban', 'Debit', $pusat->id_unit],
            ['513', 'Pajak & Administrasi Bank BUMDesa', 'Beban', 'Debit', $pusat->id_unit],
            ['516', 'Penyusutan Aset BUMDesa', 'Beban', 'Debit', $pusat->id_unit],
            ['517', 'Pengeluaran untuk Operasional Unit', 'Beban', 'Debit', $pusat->id_unit],
            ['519', 'Insentif Pengurus BUMDesa', 'Beban', 'Debit', $pusat->id_unit],
            ['520', 'Pajak PPh 21 / Pendapatan', 'Beban', 'Debit', $pusat->id_unit],

            // UNIT WIFI
            ['11-WF', 'Kas Unit Jasa Wifi', 'Aset Lancar', 'Debit', $wifi->id_unit],
            ['12-WF', 'Bank Unit Wifi', 'Aset Lancar', 'Debit', $wifi->id_unit],
            ['13-WF', 'Piutang / Belanja Barang Wifi', 'Aset Lancar', 'Debit', $wifi->id_unit],
            ['17-WF', 'Inventaris Jaringan & Server Wifi', 'Aset Tetap', 'Debit', $wifi->id_unit],
            ['18-WF', 'Akumulasi Penyusutan Aset Wifi', 'Aset Tetap', 'Kredit', $wifi->id_unit],
            ['21-WF', 'Hutang Pihak Lain Unit Wifi', 'Kewajiban', 'Kredit', $wifi->id_unit],
            ['22-WF', 'Titipan / Safety Peralatan Wifi', 'Kewajiban', 'Kredit', $wifi->id_unit],
            ['27-WF', 'SHU / Alokasi PADes Unit Wifi', 'Kewajiban', 'Kredit', $wifi->id_unit],
            ['31-WF', 'Modal Awal Unit Jasa Wifi', 'Ekuitas', 'Kredit', $wifi->id_unit],
            ['35-WF', 'Akumulasi Laba Berjalan Wifi', 'Ekuitas', 'Kredit', $wifi->id_unit],
            ['41-WF', 'Pendapatan Jasa Paket Internet Wifi', 'Pendapatan', 'Kredit', $wifi->id_unit],
            ['42-WF', 'Bunga Bank Unit Wifi', 'Pendapatan', 'Kredit', $wifi->id_unit],
            ['51-WF', 'Insentif Pengelola Unit Wifi', 'Beban', 'Debit', $wifi->id_unit],
            ['52-WF', 'Administrasi & Operasional Wifi', 'Beban', 'Debit', $wifi->id_unit],
            ['57-WF', 'Beban Bandwidth ISP Dedicated', 'Beban', 'Debit', $wifi->id_unit],
            ['510-WF', 'Administrasi & Pajak Bank Wifi', 'Beban', 'Debit', $wifi->id_unit],

            // UNIT SIMPAN PINJAM (USP)
            ['11-USP', 'Kas Unit USP Bathin Alam', 'Aset Lancar', 'Debit', $usp->id_unit],
            ['12-USP', 'Bank Unit USP', 'Aset Lancar', 'Debit', $usp->id_unit],
            ['13-USP', 'Piutang Pinjaman Pokok Anggota', 'Aset Lancar', 'Debit', $usp->id_unit],
            ['17-USP', 'Inventaris Kantor USP', 'Aset Tetap', 'Debit', $usp->id_unit],
            ['18-USP', 'Akumulasi Penyusutan USP', 'Aset Tetap', 'Kredit', $usp->id_unit],
            ['21-USP', 'Hutang / Titipan Simpanan Anggota', 'Kewajiban', 'Kredit', $usp->id_unit],
            ['31-USP', 'Modal Awal Unit Simpan Pinjam', 'Ekuitas', 'Kredit', $usp->id_unit],
            ['41-USP', 'Pendapatan Jasa Pinjaman (Bunga 2%)', 'Pendapatan', 'Kredit', $usp->id_unit],
            ['43-USP', 'Pendapatan Denda / Provisi USP', 'Pendapatan', 'Kredit', $usp->id_unit],
            ['51-USP', 'Insentif Pengelola Unit USP', 'Beban', 'Debit', $usp->id_unit],
            ['52-USP', 'Beban Administrasi & ATK USP', 'Beban', 'Debit', $usp->id_unit],

            // UNIT PERKEBUNAN & PERTANIAN
            ['11-KBN', 'Kas Unit Perkebunan Nanas', 'Aset Lancar', 'Debit', $kebun->id_unit],
            ['17-KBN', 'Inventaris Alsintan & Lahan Kebun', 'Aset Tetap', 'Debit', $kebun->id_unit],
            ['31-KBN', 'Modal Awal Unit Perkebunan', 'Ekuitas', 'Kredit', $kebun->id_unit],
            ['41-KBN', 'Pendapatan Penjualan Hasil Nanas', 'Pendapatan', 'Kredit', $kebun->id_unit],
            ['51-KBN', 'Beban Upah Tenaga Kerja & Panen Kebun', 'Beban', 'Debit', $kebun->id_unit],
            ['52-KBN', 'Beban Pembelian Pupuk & Obat Racun Hama', 'Beban', 'Debit', $kebun->id_unit],
            ['53-KBN', 'Beban Operasional Angkutan / Distribusi Nanas', 'Beban', 'Debit', $kebun->id_unit],
        ];

        foreach ($accounts as $acc) {
            ChartOfAccount::create([
                'kode_akun' => $acc[0],
                'nama_akun' => $acc[1],
                'kategori' => $acc[2],
                'tipe_saldo_normal' => $acc[3],
                'id_unit' => $acc[4],
            ]);
        }

        // 4. Saldo Awal Konsolidasi Resmi BUMDesa (Sesuai Sheet LKN I Template Resmi)
        $openingJournal = Jurnal::create([
            'id_transaksi' => null,
            'tanggal' => Carbon::create(2023, 1, 1),
            'keterangan' => 'Saldo Awal Modal & Kas BUMDesa Kuala Alam (Standar Pemerintah)',
        ]);

        $openingEntries = [
            ['111', 412000, 0],              // Kas BUMDesa
            ['112', 13479874, 0],            // Bank BUMDesa
            ['113', 1096609819, 0],          // Modal Usaha Ke Unit
            ['117', 5380000, 0],             // Inventaris
            ['214', 0, 3002000],             // PADes (Kewajiban)
            ['311', 0, 1121206819],          // Penyertaan Modal Desa (Ekuitas)
            ['313', 0, 11169000],            // Cad. Pengemb. Usaha (Ekuitas)
            ['315', 0, -3002000],            // Akum. Laba
        ];

        foreach ($openingEntries as $entry) {
            JurnalDetail::create([
                'id_jurnal' => $openingJournal->id_jurnal,
                'kode_akun' => $entry[0],
                'debit' => $entry[1],
                'kredit' => $entry[2],
            ]);
        }

        // 5. Seed Seluruh Data Transaksi Riil Excel (1.648 Transaksi)
        $allDataPath = database_path('seeders/all_excel_transactions.json');
        if (file_exists($allDataPath)) {
            $transactionsData = json_decode(file_get_contents($allDataPath), true) ?? [];

            $unitMap = [
                'WIFI' => ['unit' => $wifi, 'user' => $bWifi, 'kas' => '11-WF'],
                'USP' => ['unit' => $usp, 'user' => $bUsp, 'kas' => '11-USP'],
                'KEBUN' => ['unit' => $kebun, 'user' => $bKebun, 'kas' => '11-KBN'],
                'PUSAT' => ['unit' => $pusat, 'user' => $bUmum, 'kas' => '111'],
            ];

            foreach ($transactionsData as $tData) {
                $uKey = $tData['unit'] ?? 'PUSAT';
                $map = $unitMap[$uKey] ?? $unitMap['PUSAT'];

                $tx = Transaksi::create([
                    'id_unit' => $map['unit']->id_unit,
                    'id_user' => $map['user']->id,
                    'tanggal' => $tData['tanggal'],
                    'jenis_transaksi' => $tData['jenis'],
                    'kode_akun' => $tData['kode_akun'],
                    'nominal' => (float) $tData['nominal'],
                    'keterangan' => $tData['keterangan'],
                    'status' => 'disetujui',
                    'catatan_validasi' => 'Divalidasi otomatis dari arsip resmi berkas Excel BUMDesa: ' . ($tData['sumber'] ?? 'Excel'),
                    'data_tambahan' => [
                        'unit' => $uKey,
                        'no_bukti' => $tData['bukti'] ?? '-',
                        'sumber_excel' => $tData['sumber'] ?? 'Excel',
                    ],
                ]);

                // Buat Jurnal Umum Berpasangan (Double-Entry SAK ETAP)
                $jurnal = Jurnal::create([
                    'id_transaksi' => $tx->id_transaksi,
                    'tanggal' => $tx->tanggal,
                    'keterangan' => $tx->keterangan,
                ]);

                $kasAkun = $map['kas'];

                if ($tx->jenis_transaksi === 'masuk') {
                    // Kas (Debit), Akun Lawan (Kredit)
                    JurnalDetail::create([
                        'id_jurnal' => $jurnal->id_jurnal,
                        'kode_akun' => $kasAkun,
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
                    // Akun Lawan (Debit), Kas (Kredit)
                    JurnalDetail::create([
                        'id_jurnal' => $jurnal->id_jurnal,
                        'kode_akun' => $tx->kode_akun,
                        'debit' => $tx->nominal,
                        'kredit' => 0,
                    ]);
                    JurnalDetail::create([
                        'id_jurnal' => $jurnal->id_jurnal,
                        'kode_akun' => $kasAkun,
                        'debit' => 0,
                        'kredit' => $tx->nominal,
                    ]);
                }
            }
        }

        // 6. Seed Status Pengesahan Laporan Resmi Direktur
        LaporanKeuangan::create([
            'periode_bulan' => 12,
            'periode_tahun' => 2023,
            'status_laporan' => 'disetujui',
            'is_locked' => true,
            'catatan_direktur' => 'Laporan Pertanggungjawaban Tahunan BUMDesa Kuala Alam Periode 2023 telah disahkan pada Musyawarah Desa.',
            'approved_at' => Carbon::create(2023, 12, 31, 23, 59, 59),
            'approved_by' => $direktur->id,
            'signature_hash' => hash('sha256', 'BJFIN-SAK-ETAP-2023-12-DIREKTUR'),
        ]);

        LaporanKeuangan::create([
            'periode_bulan' => 12,
            'periode_tahun' => 2024,
            'status_laporan' => 'disetujui',
            'is_locked' => true,
            'catatan_direktur' => 'Laporan Keuangan Tahunan BUMDesa Kuala Alam Periode 2024 telah diverifikasi dan disetujui.',
            'approved_at' => Carbon::create(2024, 12, 31, 23, 59, 59),
            'approved_by' => $direktur->id,
            'signature_hash' => hash('sha256', 'BJFIN-SAK-ETAP-2024-12-DIREKTUR'),
        ]);
    }
}
