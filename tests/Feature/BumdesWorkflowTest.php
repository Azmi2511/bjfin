<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use App\Models\User;
use App\Services\AccountingService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BumdesWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (UnitUsaha::count() === 0) {
            $this->seed(\Database\Seeders\DatabaseSeeder::class);
        }

        $user = User::where('role', 'bendahara_umum')->first() ?? User::factory()->create(['role' => 'bendahara_umum']);
        $this->actingAs($user);
    }

    /**
     * Dashboard eksekutif dapat dimuat dengan KPI dan status periode.
     */
    public function test_dashboard_displays_financial_kpis_and_actions(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('BJFin');
        $response->assertSee('Ringkasan Finansial BUMDesa');
        $response->assertSee('Validasi Transaksi');
        $response->assertSee('Kas & Memorial Pusat', false);
        $response->assertSee('Laporan Keuangan');
    }

    /**
     * Dashboard dapat difilter berdasarkan tahun, bulan tertentu, dan unit usaha.
     */
    public function test_dashboard_can_be_filtered_by_year_month_and_unit(): void
    {
        // 1. Filter tahun spesifik & bulan spesifik
        $responseMonth = $this->get(route('dashboard', ['tahun' => 2025, 'bulan' => 3]));
        $responseMonth->assertStatus(200);
        $responseMonth->assertSee('Maret 2025');

        // 2. Filter tahun & seluruh bulan (konsolidasi tahunan)
        $responseAnnual = $this->get(route('dashboard', ['tahun' => 2025, 'bulan' => 'all']));
        $responseAnnual->assertStatus(200);
        $responseAnnual->assertSee('Konsolidasi Tahunan');

        // 3. Filter berdasarkan unit usaha tertentu
        $wifiUnit = UnitUsaha::where('kode_unit', 'WIFI')->firstOrFail();
        $responseUnit = $this->get(route('dashboard', ['tahun' => 2025, 'bulan' => 'all', 'id_unit' => $wifiUnit->id_unit]));
        $responseUnit->assertStatus(200);
        $responseUnit->assertSee($wifiUnit->nama_unit);
    }

    /**
     * Bendahara Unit menginput transaksi Wifi melalui form antarmuka unit.
     */
    public function test_bendahara_unit_can_submit_wifi_guided_transaction(): void
    {
        $wifiUnit = UnitUsaha::where('kode_unit', 'WIFI')->firstOrFail();
        $pendapatanWifi = ChartOfAccount::where('kode_akun', '41-WF')->firstOrFail();

        $payload = [
            'id_unit' => $wifiUnit->id_unit,
            'kode_akun' => $pendapatanWifi->kode_akun,
            'tanggal' => '2026-01-20',
            'jenis_transaksi' => 'masuk',
            'nominal' => 175000,
            'keterangan' => 'Pembayaran Wifi 3 MBps Pelanggan #WF-101 (Ahmad Subarjo, Dusun Kuala)',
            'no_customer' => 'WF-101',
            'nama_customer' => 'Ahmad Subarjo',
            'dusun' => 'Dusun Kuala',
            'paket' => '3 MBps',
            'biaya_jasa' => 168000,
            'safety_peralatan' => 7000,
            'no_bukti' => 'M-WF-99',
        ];

        $response = $this->post(route('transaksi.unit-store'), $payload);

        $response->assertRedirect(route('transaksi.unit-input', ['id_unit' => $wifiUnit->id_unit]));

        $this->assertDatabaseHas('transactions', [
            'id_unit' => $wifiUnit->id_unit,
            'kode_akun' => '41-WF',
            'nominal' => 175000,
            'status' => 'menunggu',
        ]);
    }

    /**
     * Bendahara Unit menginput transaksi Unit Simpan Pinjam (USP).
     */
    public function test_bendahara_unit_can_submit_usp_guided_transaction(): void
    {
        $uspUnit = UnitUsaha::where('kode_unit', 'USP')->firstOrFail();
        $pendapatanUsp = ChartOfAccount::where('kode_akun', '41-USP')->firstOrFail();

        $payload = [
            'id_unit' => $uspUnit->id_unit,
            'kode_akun' => $pendapatanUsp->kode_akun,
            'tanggal' => '2026-01-21',
            'jenis_transaksi' => 'masuk',
            'nominal' => 20000, // Pendapatan jasa bunga
            'keterangan' => 'Pendapatan Jasa Simpan Pinjam Pemanfaat SPPK-042',
            'no_sppk' => 'SPPK-042',
            'nama_pemanfaat' => 'Siti Aminah',
            'jenis_usaha' => 'Warung Kelontong',
            'angsuran_pokok' => 1000000,
            'nominal_bunga' => 20000,
            'bunga_persen' => 2.0,
            'no_bukti' => 'M-USP-42',
        ];

        $response = $this->post(route('transaksi.unit-store'), $payload);

        $response->assertRedirect(route('transaksi.unit-input', ['id_unit' => $uspUnit->id_unit]));

        $this->assertDatabaseHas('transactions', [
            'id_unit' => $uspUnit->id_unit,
            'kode_akun' => '41-USP',
            'nominal' => 20000,
            'status' => 'menunggu',
        ]);
    }

    /**
     * Bendahara Unit menginput transaksi Penjualan Perkebunan Nanas.
     */
    public function test_bendahara_unit_can_submit_kebun_guided_transaction(): void
    {
        $kebunUnit = UnitUsaha::where('kode_unit', 'KEBUN')->firstOrFail();
        $pendapatanKebun = ChartOfAccount::where('kode_akun', '41-KBN')->firstOrFail();

        $payload = [
            'id_unit' => $kebunUnit->id_unit,
            'kode_akun' => $pendapatanKebun->kode_akun,
            'tanggal' => '2026-01-22',
            'jenis_transaksi' => 'masuk',
            'nominal' => 250000,
            'keterangan' => 'Penjualan Hasil Panen Nanas Kuala Alam (50 buah)',
            'komoditas' => 'Penjualan Nanas Panen',
            'jumlah_buah' => 50,
            'harga_satuan' => 5000,
            'pembeli' => 'Pedagang Pasar Bengkalis',
            'no_bukti' => 'M-KBN-15',
        ];

        $response = $this->post(route('transaksi.unit-store'), $payload);

        $response->assertRedirect(route('transaksi.unit-input', ['id_unit' => $kebunUnit->id_unit]));

        $this->assertDatabaseHas('transactions', [
            'id_unit' => $kebunUnit->id_unit,
            'kode_akun' => '41-KBN',
            'nominal' => 250000,
            'status' => 'menunggu',
        ]);
    }

    /**
     * Bendahara Unit menginput pelanggan, pemanfaat, dan panen secara mandiri.
     */
    public function test_bendahara_unit_can_submit_self_service_custom_entries(): void
    {
        // 1. Wifi Mandiri (Pelanggan baru + Paket kustom mandiri)
        $wifiUnit = UnitUsaha::where('kode_unit', 'WIFI')->firstOrFail();
        $responseWifi = $this->post(route('transaksi.unit-store'), [
            'id_unit' => $wifiUnit->id_unit,
            'is_mandiri' => '1',
            'mode_input' => 'mandiri',
            'tanggal' => '2026-02-01',
            'jenis_transaksi' => 'masuk',
            'nominal' => 220000,
            'keterangan' => 'Pembayaran Wifi Paket Khusus Mandiri Pelanggan #WF-99 (Budi Santoso, Dusun 3)',
            'no_customer' => 'WF-99',
            'nama_customer' => 'Budi Santoso',
            'dusun' => 'Dusun 3 Kuala Alam',
            'no_hp' => '081299887766',
            'paket' => 'Paket Khusus Mandiri',
            'biaya_jasa' => 210000,
            'safety_peralatan' => 10000,
            'no_bukti' => 'M-WF-99',
        ]);
        $responseWifi->assertRedirect();

        $txWifi = Transaksi::where('id_unit', $wifiUnit->id_unit)
            ->where('keterangan', 'like', '%Budi Santoso%')
            ->firstOrFail();
        $this->assertEquals('Budi Santoso', $txWifi->data_tambahan['nama_customer']);
        $this->assertEquals('WF-99', $txWifi->data_tambahan['no_customer']);
        $this->assertEquals('Paket Khusus Mandiri', $txWifi->data_tambahan['paket']);
        $this->assertEquals(210000, $txWifi->data_tambahan['biaya_jasa']);
        $this->assertEquals(10000, $txWifi->data_tambahan['safety_peralatan']);

        // 2. USP Mandiri (Pemanfaat baru + Angsuran ke-X + Bunga mandiri)
        $uspUnit = UnitUsaha::where('kode_unit', 'USP')->firstOrFail();
        $responseUsp = $this->post(route('transaksi.unit-store'), [
            'id_unit' => $uspUnit->id_unit,
            'is_mandiri' => '1',
            'mode_input' => 'mandiri',
            'tanggal' => '2026-02-02',
            'jenis_transaksi' => 'masuk',
            'nominal' => 525000,
            'keterangan' => 'Setoran Angsuran USP Bathin Alam (Ahmad Dahlan, SPPK-888, Angs-3): Pokok Rp 500.000 + Jasa Rp 25.000',
            'no_sppk' => 'SPPK-888',
            'nama_pemanfaat' => 'Ahmad Dahlan',
            'jenis_usaha' => 'Bengkel Motor',
            'angsuran_ke' => '3',
            'angsuran_pokok' => 500000,
            'nominal_bunga' => 25000,
            'metode_bunga' => 'manual',
            'no_bukti' => 'M-USP-888',
        ]);
        $responseUsp->assertRedirect();

        $txUsp = Transaksi::where('id_unit', $uspUnit->id_unit)
            ->where('keterangan', 'like', '%Ahmad Dahlan%')
            ->firstOrFail();
        $this->assertEquals('Ahmad Dahlan', $txUsp->data_tambahan['nama_pemanfaat']);
        $this->assertEquals('SPPK-888', $txUsp->data_tambahan['no_sppk']);
        $this->assertEquals('Bengkel Motor', $txUsp->data_tambahan['jenis_usaha']);
        $this->assertEquals('3', $txUsp->data_tambahan['angsuran_ke']);
        $this->assertEquals(500000, $txUsp->data_tambahan['angsuran_pokok']);
        $this->assertEquals(25000, $txUsp->data_tambahan['nominal_bunga']);

        // 3. Kebun Mandiri (Komoditas mandiri + Satuan kg + Tahap panen fleksibel)
        $kebunUnit = UnitUsaha::where('kode_unit', 'KEBUN')->firstOrFail();
        $responseKebun = $this->post(route('transaksi.unit-store'), [
            'id_unit' => $kebunUnit->id_unit,
            'is_mandiri' => '1',
            'mode_input' => 'mandiri',
            'tanggal' => '2026-02-03',
            'jenis_transaksi' => 'masuk',
            'nominal' => 450000,
            'keterangan' => 'Penjualan Panen Kelapa Sawit (Tahap 2: 300 Kg @ Rp 1.500) kepada Pengepul Sawit Jaya',
            'komoditas' => 'Kelapa Sawit (TBS)',
            'tahap_panen' => 'Tahap 2 (Blok Timur)',
            'satuan' => 'Kg',
            'jumlah_buah' => 300,
            'harga_satuan' => 1500,
            'pembeli' => 'Pengepul Sawit Jaya',
            'no_bukti' => 'M-KBN-77',
        ]);
        $responseKebun->assertRedirect();

        $txKebun = Transaksi::where('id_unit', $kebunUnit->id_unit)
            ->where('keterangan', 'like', '%Kelapa Sawit%')
            ->firstOrFail();
        $this->assertEquals('Kelapa Sawit (TBS)', $txKebun->data_tambahan['komoditas']);
        $this->assertEquals('Kg', $txKebun->data_tambahan['satuan']);
        $this->assertEquals(300, $txKebun->data_tambahan['jumlah_buah']);
        $this->assertEquals(1500, $txKebun->data_tambahan['harga_satuan']);
        $this->assertEquals('Pengepul Sawit Jaya', $txKebun->data_tambahan['pembeli']);
    }

    /**
     * Bendahara Unit dapat mendaftarkan entri master baru (pelanggan/pemanfaat/komoditas)
     * sehingga entri tersebut tersimpan permanen dan muncul di daftar dropdown pilihan.
     */
    public function test_bendahara_unit_can_create_and_reuse_master_entries(): void
    {
        $wifiUnit = UnitUsaha::where('kode_unit', 'WIFI')->firstOrFail();

        // 1. Tambah Master Pelanggan Wifi baru
        $resStore = $this->postJson(route('transaksi.master-store'), [
            'id_unit' => $wifiUnit->id_unit,
            'jenis_entri' => 'pelanggan_wifi',
            'kode_referensi' => 'WF-777',
            'nama' => 'Haji Mansyur',
            'kategori_sub' => 'Dusun Sungai Batang',
            'paket' => '4 MBps',
            'nominal_standar' => 200000,
            'nominal_tambahan' => 7000,
            'biaya_jasa' => 193000,
            'no_hp' => '085211223344',
        ]);

        $resStore->assertStatus(200);
        $resStore->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('master_entri_units', [
            'id_unit' => $wifiUnit->id_unit,
            'kode_referensi' => 'WF-777',
            'nama' => 'Haji Mansyur',
            'nominal_standar' => 200000,
        ]);

        // 2. Akses halaman form unit-input dan pastikan entri baru muncul di daftar pilihan
        $responseView = $this->get(route('transaksi.unit-input', ['id_unit' => $wifiUnit->id_unit]));
        $responseView->assertStatus(200);
        $responseView->assertSee('Haji Mansyur');
        $responseView->assertSee('WF-777');
        $responseView->assertSee('⭐ [Master]');
    }

    /**
     * Bendahara Umum menyetujui transaksi unit melalui antarmuka validasi.
     * Sistem otomatis menerbitkan Jurnal Umum Double-Entry berpasangan.
     */
    public function test_bendahara_umum_validates_and_approves_unit_transaction(): void
    {
        $pendingTx = Transaksi::where('status', 'menunggu')->first();
        if (! $pendingTx) {
            $wifiUnit = UnitUsaha::where('kode_unit', 'WIFI')->firstOrFail();
            $user = User::where('id_unit', $wifiUnit->id_unit)->firstOrFail();
            $pendingTx = Transaksi::create([
                'id_unit' => $wifiUnit->id_unit,
                'id_user' => $user->id,
                'tanggal' => '2025-04-20',
                'jenis_transaksi' => 'masuk',
                'kode_akun' => '41-WF',
                'nominal' => 175000,
                'keterangan' => 'Pembayaran Wifi Menunggu Validasi',
                'status' => 'menunggu',
            ]);
        }

        $response = $this->postJson(route('validation.approve', ['id' => $pendingTx->id_transaksi]), [
            'catatan' => 'Disetujui setelah mencocokkan nota bukti fisik.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $pendingTx->refresh();
        $this->assertEquals('disetujui', $pendingTx->status);
        $this->assertNotNull($pendingTx->journal);

        // Verifikasi keseimbangan debit & kredit pada jurnal yang terbit otomatis
        $details = $pendingTx->journal->details;
        $totalDebit = $details->sum('debit');
        $totalKredit = $details->sum('kredit');

        $this->assertEquals($totalDebit, $totalKredit);
        $this->assertEquals($pendingTx->nominal, $totalDebit);
    }

    /**
     * Bendahara Umum dapat menginput transaksi langsung Kas BUMDesa Pusat (Template Jan 2023).
     */
    public function test_bendahara_umum_records_central_cash_transaction_directly(): void
    {
        $bebanCoa = ChartOfAccount::where('kode_akun', '512')->firstOrFail();

        $payload = [
            'kode_akun' => $bebanCoa->kode_akun,
            'tanggal' => '2026-01-25',
            'jenis_transaksi' => 'keluar',
            'nominal' => 150000,
            'keterangan' => 'Pembelian ATK dan Kertas Kantor BUMDesa',
            'no_bukti' => 'K-PST-05',
            'kategori_beban' => 'Biaya Kantor',
        ];

        $response = $this->post(route('transaksi.umum-store'), $payload);

        $response->assertRedirect(route('transaksi.umum-input'));

        $this->assertDatabaseHas('transactions', [
            'kode_akun' => '512',
            'nominal' => 150000,
            'status' => 'disetujui',
        ]);

        $latestTx = Transaksi::where('kode_akun', '512')->latest('id_transaksi')->first();
        $this->assertNotNull($latestTx->journal);

        $details = $latestTx->journal->details;
        $totalDebit = $details->sum('debit');
        $totalKredit = $details->sum('kredit');

        $this->assertEquals($totalDebit, $totalKredit);
        $this->assertEquals(150000, $totalDebit);
    }

    /**
     * Laporan Keuangan SAK ETAP terkonsolidasi & Neraca Seimbang (Aktiva = Pasiva).
     */
    public function test_consolidated_financial_reports_balance(): void
    {
        $response = $this->get(route('reports', ['tahun' => 2026, 'bulan' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Laporan Keuangan BUMDesa');

        $accountingService = app(AccountingService::class);
        $balanceSheet = $accountingService->getBalanceSheet(2026, 1);

        $this->assertTrue($balanceSheet['is_balanced'], 'Neraca SAK ETAP harus seimbang 100% (Total Aktiva == Total Kewajiban + Ekuitas)');
        $this->assertEquals(
            $balanceSheet['total_aset'],
            $balanceSheet['total_passiva']
        );
    }

    /**
     * Ekspor Multi-sheet Format Excel Kecamatan Bengkalis menghasilkan tepat 18 sheet resmi.
     */
    public function test_download_excel_format_kecamatan(): void
    {
        $response = $this->get(route('reports.download-excel', ['tahun' => 2026, 'bulan' => 1]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Verifikasi langsung struktur workbook
        $konverter = app(\App\Services\KonverterKecamatan::class);
        $filePath = $konverter->exportToFile(2026, 1);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $sheetNames = $reader->listWorksheetNames($filePath);

        $this->assertCount(18, $sheetNames, 'Laporan Keuangan Resmi BUMDesa harus memiliki tepat 18 Sheet');

        $expectedSheets = [
            'KAS BUMDESA',
            'PERSENTASE PEMB PENDAPATAN 1',
            'PERSENTASE PEMB. PENDAPATAN 2',
            'DUMDUKBUMDESA',
            'LKN I',
            'JM',
            'LABARUGI',
            'INV',
            'Neraca',
            'MODAL KE UNIT USAHA',
            'AKUM. SHU',
            'PERUBAHAN MODAL',
            'MODAL',
            'COVER',
            'PERUBAHAN MODAL PERMENDES',
            'AMPRAH',
            'AMPRAH USP',
            'Sheet1',
        ];

        foreach ($expectedSheets as $expected) {
            $this->assertContains($expected, $sheetNames, "Sheet '{$expected}' wajib ada dalam 18 sheet standar pemerintah.");
        }

        // Pastikan tidak ada warna artifisial buatan (maroon 991B1B atau gold FEF3C7)
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $kasSheet = $spreadsheet->getSheetByName('KAS BUMDESA');
        $headerFill = $kasSheet->getStyle('A9')->getFill()->getStartColor()->getRGB();
        $this->assertNotEquals('991B1B', $headerFill, 'Header tidak boleh menggunakan warna merah maroon artifisial');
        
        $totalFill = $kasSheet->getStyle('A24')->getFill()->getStartColor()->getRGB();
        $this->assertNotEquals('FEF3C7', $totalFill, 'Baris jumlah tidak boleh menggunakan warna emas artifisial');

        // Pastikan periode dan cover terisi
        $this->assertStringContainsString('2026', (string) $kasSheet->getCell('C7')->getValue());
        $coverSheet = $spreadsheet->getSheetByName('COVER');
        $this->assertStringContainsString('2026', (string) $coverSheet->getCell('B16')->getValue());
    }

    /**
     * Bendahara Umum dapat mencatat transaksi lewat Rekening Bank (112) secara berpasangan.
     */
    public function test_bendahara_umum_can_record_bank_transaction(): void
    {
        $payload = [
            'kode_akun' => '413', // Pendapatan Bunga Bank
            'rekening_kas' => '112', // Rekening Bank
            'tanggal' => '2026-01-28',
            'jenis_transaksi' => 'masuk',
            'nominal' => 250000,
            'keterangan' => 'Penerimaan Bunga Bank Rekening BUMDesa Januari 2026',
            'no_bukti' => 'M-BANK-01',
            'kategori_beban' => 'Pendapatan Bunga Bank',
        ];

        $response = $this->post(route('transaksi.umum-store'), $payload);
        $response->assertRedirect(route('transaksi.umum-input'));

        $this->assertDatabaseHas('transactions', [
            'kode_akun' => '413',
            'nominal' => 250000,
            'status' => 'disetujui',
        ]);

        $latestTx = Transaksi::where('kode_akun', '413')->latest('id_transaksi')->first();
        $this->assertNotNull($latestTx->journal);

        $details = $latestTx->journal->details;
        $this->assertCount(2, $details);

        // Debit harus masuk ke rekening 112 (Bank), kredit ke 413 (Bunga Bank)
        $debitDetail = $details->where('debit', '>', 0)->first();
        $kreditDetail = $details->where('kredit', '>', 0)->first();

        $this->assertEquals('112', $debitDetail->kode_akun);
        $this->assertEquals(250000, (float) $debitDetail->debit);
        $this->assertEquals('413', $kreditDetail->kode_akun);
        $this->assertEquals(250000, (float) $kreditDetail->kredit);
    }
}
