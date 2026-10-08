<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\UnitUsaha;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Exception;

class KonverterKecamatan
{
    protected AccountingService $accountingService;

    protected array $namaBulan = [
        0 => 'Tahunan',
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Menghasilkan Workbook Excel Resmi BUMDesa Standar Pemerintah Kecamatan Bengkalis (18 Sheet Lengkap).
     */
    public function generateOfficialWorkbook(int $tahun, int $bulan): Spreadsheet
    {
        $templatePath = storage_path('app/templates/template_bumdesa_18_sheet.xls');

        if (file_exists($templatePath)) {
            return $this->fillOfficialTemplate($templatePath, $tahun, $bulan);
        }

        return $this->buildWorkbookFromScratch($tahun, $bulan);
    }

    /**
     * Mengisi Master Template Resmi Pemerintah 18 Sheet dengan data dinamis tanpa mengubah format/warna asli.
     */
    protected function fillOfficialTemplate(string $templatePath, int $tahun, int $bulan): Spreadsheet
    {
        $spreadsheet = IOFactory::load($templatePath);

        $bulanText = $this->namaBulan[$bulan] ?? "Bulan-{$bulan}";
        $bulanUpper = strtoupper($bulanText);
        $lastDay = ($bulan > 0) ? date('t', strtotime("{$tahun}-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01")) : 31;

        $spreadsheet->getProperties()
            ->setCreator("BJFin (Bandar Jaya Financial) - BUMDesa Kuala Alam")
            ->setLastModifiedBy("Direktur / Bendahara Umum BUMDesa")
            ->setTitle("Laporan Keuangan BJFin Periode {$bulanText} {$tahun}")
            ->setSubject("Laporan Pertanggungjawaban Keuangan Standar Pemerintah")
            ->setDescription("Format Resmi Laporan BUMDesa Kecamatan Bengkalis Standar Real Template (18 Sheet Lengkap) via BJFin");

        // 1. KAS BUMDESA
        if ($sheetKas = $spreadsheet->getSheetByName('KAS BUMDESA')) {
            $sheetKas->setCellValue('C7', ($bulan > 0) ? ": {$bulanUpper} {$tahun}" : ": TAHUNAN {$tahun}");
            $sheetKas->setCellValue('E33', "Kuala Alam, {$lastDay} {$bulanText} {$tahun}");

            // Ambil transaksi yang disetujui untuk periode terkait
            $query = Transaksi::whereYear('tanggal', $tahun)->where('status', 'disetujui');
            if ($bulan > 0) {
                $query->whereMonth('tanggal', $bulan);
            }
            $pusatQuery = clone $query;
            $pusatQuery->where(function ($q) {
                $q->where('id_unit', 1)->orWhereNull('id_unit');
            });
            $txs = $pusatQuery->orderBy('tanggal', 'asc')->orderBy('id_transaksi', 'asc')->get();
            if ($txs->isEmpty()) {
                $txs = $query->orderBy('tanggal', 'asc')->orderBy('id_transaksi', 'asc')->take(13)->get();
            }

            $count = $txs->count();
            $startRow = 11;
            $maxTemplateRows = 13; // rows 11 to 23

            if ($count <= $maxTemplateRows) {
                for ($i = 0; $i < $maxTemplateRows; $i++) {
                    $r = $startRow + $i;
                    if ($i < $count) {
                        $tx = $txs[$i];
                        $masuk = $tx->jenis_transaksi === 'masuk' ? (float) $tx->nominal : null;
                        $keluar = $tx->jenis_transaksi === 'keluar' ? (float) $tx->nominal : null;
                        $meta = $tx->data_tambahan ?? [];
                        $noBukti = $meta['no_bukti'] ?? ($tx->jenis_transaksi === 'masuk' ? 'M-' . str_pad($tx->id_transaksi, 3, '0', STR_PAD_LEFT) : 'K-' . str_pad($tx->id_transaksi, 3, '0', STR_PAD_LEFT));
                        $kodeAkun = $tx->kode_akun ?? ($tx->jenis_transaksi === 'masuk' ? '411' : '512');

                        $sheetKas->setCellValue('A' . $r, $tx->tanggal->format('Y-m-d'));
                        $sheetKas->setCellValue('B' . $r, $tx->keterangan);
                        $sheetKas->setCellValue('C' . $r, $noBukti);
                        $sheetKas->setCellValue('D' . $r, $kodeAkun);
                        $sheetKas->setCellValue('E' . $r, $masuk);
                        $sheetKas->setCellValue('F' . $r, $keluar);
                        $sheetKas->setCellValue('G' . $r, "=G" . ($r - 1) . "+E{$r}-F{$r}");
                    } else {
                        // Bersihkan baris template yang tidak terpakai
                        $sheetKas->setCellValue('A' . $r, null);
                        $sheetKas->setCellValue('B' . $r, null);
                        $sheetKas->setCellValue('C' . $r, null);
                        $sheetKas->setCellValue('D' . $r, null);
                        $sheetKas->setCellValue('E' . $r, null);
                        $sheetKas->setCellValue('F' . $r, null);
                        $sheetKas->setCellValue('G' . $r, "=G" . ($r - 1) . "+E{$r}-F{$r}");
                    }
                }
            } else {
                $extraRows = $count - $maxTemplateRows;
                $sheetKas->insertNewRowBefore(24, $extraRows);
                for ($i = 0; $i < $count; $i++) {
                    $r = $startRow + $i;
                    $tx = $txs[$i];
                    $masuk = $tx->jenis_transaksi === 'masuk' ? (float) $tx->nominal : null;
                    $keluar = $tx->jenis_transaksi === 'keluar' ? (float) $tx->nominal : null;
                    $meta = $tx->data_tambahan ?? [];
                    $noBukti = $meta['no_bukti'] ?? ($tx->jenis_transaksi === 'masuk' ? 'M-' . str_pad($tx->id_transaksi, 3, '0', STR_PAD_LEFT) : 'K-' . str_pad($tx->id_transaksi, 3, '0', STR_PAD_LEFT));
                    $kodeAkun = $tx->kode_akun ?? ($tx->jenis_transaksi === 'masuk' ? '411' : '512');

                    $sheetKas->setCellValue('A' . $r, $tx->tanggal->format('Y-m-d'));
                    $sheetKas->setCellValue('B' . $r, $tx->keterangan);
                    $sheetKas->setCellValue('C' . $r, $noBukti);
                    $sheetKas->setCellValue('D' . $r, $kodeAkun);
                    $sheetKas->setCellValue('E' . $r, $masuk);
                    $sheetKas->setCellValue('F' . $r, $keluar);
                    $sheetKas->setCellValue('G' . $r, "=G" . ($r - 1) . "+E{$r}-F{$r}");
                }
                $totalRow = 24 + $extraRows;
                $sheetKas->setCellValue('E' . $totalRow, "=SUM(E10:E" . ($totalRow - 1) . ")");
                $sheetKas->setCellValue('F' . $totalRow, "=SUM(F10:F" . ($totalRow - 1) . ")");
                $sheetKas->setCellValue('G' . $totalRow, "=G10+E{$totalRow}-F{$totalRow}");
            }
        }

        // 2. LKN I
        if ($sheetLkn = $spreadsheet->getSheetByName('LKN I')) {
            $sheetLkn->setCellValue('B3', "Tahun {$tahun}");
        }

        // 3. DUMDUKBUMDESA
        if ($sheetDumduk = $spreadsheet->getSheetByName('DUMDUKBUMDESA')) {
            $sheetDumduk->setCellValue('B4', ($bulan > 0) ? ": {$bulanUpper} {$tahun}" : ": TAHUNAN {$tahun}");
        }

        // 4. LABARUGI
        if ($sheetLr = $spreadsheet->getSheetByName('LABARUGI')) {
            if ($bulan > 0) {
                $sheetLr->setCellValue('F13', "Bulanan (Periode : 1 s/d {$lastDay} {$bulanText} {$tahun})");
                $sheetLr->setCellValue('K13', "Kumulatif (Periode : 1 Januari s/d {$lastDay} {$bulanText} {$tahun})");
            } else {
                $sheetLr->setCellValue('F13', "Tahunan (Periode : 1 Januari s/d 31 Desember {$tahun})");
                $sheetLr->setCellValue('K13', "Kumulatif (Periode : 1 Januari s/d 31 Desember {$tahun})");
            }
        }

        // 5. COVER
        if ($sheetCover = $spreadsheet->getSheetByName('COVER')) {
            $sheetCover->setCellValue('B14', 'LAPORAN KEUANGAN BUMDESA KUALA ALAM BANDAR JAYA');
            $sheetCover->setCellValue('B15', 'DESA KUALA ALAM');
            $sheetCover->setCellValue('B16', ($bulan > 0) ? "BULAN {$bulanUpper} {$tahun}" : "TAHUN {$tahun}");
            $sheetCover->setCellValue('B17', 'KECAMATAN BENGKALIS');
            $sheetCover->setCellValue('B18', 'KABUPATEN BENGKALIS');
        }

        // 6. AMPRAH
        if ($sheetAmprah = $spreadsheet->getSheetByName('AMPRAH')) {
            $sheetAmprah->setCellValue('A3', 'BUMDesa KUALA ALAM (BJFin)');
            $sheetAmprah->setCellValue('A4', ($bulan > 0) ? "{$bulanUpper} {$tahun}" : "TAHUN {$tahun}");
            $sheetAmprah->setCellValue('D19', "Kuala Alam, {$lastDay} {$bulanText} {$tahun}");
            foreach (['D11', 'D12', 'D13', 'D14'] as $cell) {
                $sheetAmprah->setCellValue($cell, 0);
            }
        }

        // 7. AMPRAH USP
        if ($sheetAmprahUsp = $spreadsheet->getSheetByName('AMPRAH USP')) {
            $sheetAmprahUsp->setCellValue('A4', ($bulan > 0) ? "{$bulanUpper} {$tahun}" : "TAHUN {$tahun}");
            $sheetAmprahUsp->setCellValue('D18', "Kuala Alam, {$lastDay} {$bulanText} {$tahun}");
            foreach (['D8', 'D9', 'D10', 'D11', 'D12', 'D13'] as $cell) {
                $sheetAmprahUsp->setCellValue($cell, 0);
            }
        }

        // 8. Neraca (Perbaiki referensi tanda tangan)
        if ($sheetNeraca = $spreadsheet->getSheetByName('Neraca')) {
            $sheetNeraca->setCellValue('K30', 'Dibuat Oleh');
            $sheetNeraca->setCellValue('B34', "='KAS BUMDESA'!B39");
            $sheetNeraca->setCellValue('K34', "='KAS BUMDESA'!F39");
        }

        // 9. MODAL KE UNIT USAHA (Perbaiki referensi tanda tangan)
        if ($sheetModalUnit = $spreadsheet->getSheetByName('MODAL KE UNIT USAHA')) {
            $sheetModalUnit->setCellValue('B41', "='KAS BUMDESA'!B34");
            $sheetModalUnit->setCellValue('G41', "='KAS BUMDESA'!F34");
            $sheetModalUnit->setCellValue('B46', "='KAS BUMDESA'!B39");
            $sheetModalUnit->setCellValue('G46', "='KAS BUMDESA'!F39");
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    /**
     * Fallback membangun workbook jika template master fisik belum ada di storage.
     */
    protected function buildWorkbookFromScratch(int $tahun, int $bulan): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $bulanText = $this->namaBulan[$bulan] ?? "Bulan-{$bulan}";
        $spreadsheet->getProperties()
            ->setCreator("BJFin (Bandar Jaya Financial) - BUMDesa Kuala Alam")
            ->setLastModifiedBy("Direktur / Bendahara Umum BUMDesa")
            ->setTitle("Laporan Keuangan BJFin Periode {$bulanText} {$tahun}")
            ->setSubject("Laporan Pertanggungjawaban Keuangan Standar Pemerintah")
            ->setDescription("Format Resmi Laporan BUMDesa Kecamatan Bengkalis Standar Real Template (18 Sheet Lengkap) via BJFin");

        // Hapus sheet default kosong
        $spreadsheet->removeSheetByIndex(0);

        // 1. KAS BUMDESA (Buku Kas Umum Konsolidasi Pusat)
        $this->buildKasBumdesaSheet($spreadsheet, $tahun, $bulan);

        // 2. PERSENTASE PEMB PENDAPATAN 1 (Pembagian Jasa Unit 1)
        $this->buildPersentasePembPendapatan1Sheet($spreadsheet, $tahun, $bulan);

        // 3. PERSENTASE PEMB. PENDAPATAN 2 (Pembagian Jasa Unit 2)
        $this->buildPersentasePembPendapatan2Sheet($spreadsheet, $tahun, $bulan);

        // 4. DUMDUKBUMDESA (Daftar Uang Masuk dan Keluar per Akun)
        $this->buildDumdukBumdesaSheet($spreadsheet, $tahun, $bulan);

        // 5. LKN I (Neraca Saldo / Trial Balance Konsolidasi)
        $this->buildNeracaSaldoSheet($spreadsheet, $tahun, $bulan);

        // 6. JM (Jurnal Memorial Debet-Kredit)
        $this->buildJurnalMemorialSheet($spreadsheet, $tahun, $bulan);

        // 7. LABARUGI (Laporan Laba Rugi SAK ETAP 3 Kolom)
        $this->buildLabaRugiSheet($spreadsheet, $tahun, $bulan);

        // 8. INV (Daftar Inventaris & Aset Tetap)
        $this->buildInventarisSheet($spreadsheet, $tahun, $bulan);

        // 9. Neraca (Laporan Posisi Keuangan Aktiva vs Passiva)
        $this->buildNeracaSheet($spreadsheet, $tahun, $bulan);

        // 10. MODAL KE UNIT USAHA (Laporan Perubahan Modal Unit Usaha)
        $this->buildModalKeUnitSheet($spreadsheet, $tahun, $bulan);

        // 11. AKUM. SHU (Laporan Perkembangan SHU & PADes)
        $this->buildAkumulasiShuSheet($spreadsheet, $tahun, $bulan);

        // 12. PERUBAHAN MODAL (Laporan Perubahan Modal Usaha)
        $this->buildPerubahanModalSheet($spreadsheet, $tahun, $bulan);

        // 13. MODAL (Laporan Penyertaan Modal Usaha: Desa, Provinsi, BKK)
        $this->buildPenyertaanModalSheet($spreadsheet, $tahun, $bulan);

        // 14. COVER (Sampul Resmi Laporan Pertanggungjawaban)
        $this->buildCoverSheet($spreadsheet, $tahun, $bulan);

        // 15. PERUBAHAN MODAL PERMENDES (Format Standar Permendesa PDTT)
        $this->buildPerubahanModalPermendesSheet($spreadsheet, $tahun, $bulan);

        // 16. AMPRAH (Tanda Terima Insentif Pengurus BUMDesa Pusat)
        $this->buildAmprahSheet($spreadsheet, $tahun, $bulan);

        // 17. AMPRAH USP (Tanda Terima Insentif Pengelola Unit USP)
        $this->buildAmprahUspSheet($spreadsheet, $tahun, $bulan);

        // 18. Sheet1 (Catatan Alokasi Cadangan Pengembangan & Tunjangan Kinerja)
        $this->buildPetunjukSheet($spreadsheet, $tahun, $bulan);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    /**
     * Simpan file workbook ke direktori storage dan kembalikan absolute path.
     */
    public function exportToFile(int $tahun, int $bulan, ?string $destinationPath = null): string
    {
        $spreadsheet = $this->generateOfficialWorkbook($tahun, $bulan);

        if (!$destinationPath) {
            $dir = storage_path('app/public/reports');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $destinationPath = $dir . "/Laporan_BUMDes_Kuala_Alam_{$tahun}_{$bulan}.xlsx";
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($destinationPath);

        return $destinationPath;
    }

    // =============================================================
    // SHEET 1: KAS BUMDESA
    // =============================================================
    protected function buildKasBumdesaSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('KAS BUMDESA');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        // Kop Laporan
        $sheet->setCellValue('A1', 'BUKU KAS BUMDESA');
        $sheet->setCellValue('A3', 'BUMDESA');
        $sheet->setCellValue('C3', ': KUALA ALAM BANDAR JAYA');
        $sheet->setCellValue('A4', 'DESA');
        $sheet->setCellValue('C4', ': KUALA ALAM');
        $sheet->setCellValue('A5', 'KECAMATAN');
        $sheet->setCellValue('C5', ': BENGKALIS');
        $sheet->setCellValue('A6', 'KABUPATEN');
        $sheet->setCellValue('C6', ': BENGKALIS');
        $sheet->setCellValue('A7', 'BULAN');
        $sheet->setCellValue('C7', ": {$bulanText} {$tahun}");

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:A7')->getFont()->setBold(true);

        // Header Kolom
        $headers = ['Tanggal', 'Uraian', 'Bukti', 'KODE', 'Masuk', 'Keluar', 'Saldo'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '9', $h);
        }
        $this->applyHeaderStyle($sheet, 'A9:G9');

        // Saldo Bulan Lalu (Kas Tunai + Bank Awal)
        $saldoAwalKas = 412000;
        $sheet->setCellValue('B10', 'SALDO BULAN LALU');
        $sheet->setCellValue('G10', $saldoAwalKas);
        $sheet->getStyle('B10:G10')->getFont()->setBold(true);

        // Query Transaksi Pusat (atau konsolidasi jika bulan dispesifikasikan)
        $query = Transaksi::whereYear('tanggal', $tahun)
            ->where('status', 'disetujui');

        if ($bulan > 0) {
            $query->whereMonth('tanggal', $bulan);
        }

        // Ambil transaksi pusat dan transfer
        $transactions = $query->orderBy('tanggal', 'asc')
            ->orderBy('id_transaksi', 'asc')
            ->get();

        $row = 11;
        $saldoBerjalan = $saldoAwalKas;
        $totalMasuk = 0;
        $totalKeluar = 0;

        foreach ($transactions as $tx) {
            $masuk = $tx->jenis_transaksi === 'masuk' ? (float) $tx->nominal : 0;
            $keluar = $tx->jenis_transaksi === 'keluar' ? (float) $tx->nominal : 0;
            $saldoBerjalan += ($masuk - $keluar);
            $totalMasuk += $masuk;
            $totalKeluar += $keluar;

            $meta = $tx->data_tambahan ?? [];
            $noBukti = $meta['no_bukti'] ?? ($tx->jenis_transaksi === 'masuk' ? 'M-' . str_pad($tx->id_transaksi, 3, '0', STR_PAD_LEFT) : 'K-' . str_pad($tx->id_transaksi, 3, '0', STR_PAD_LEFT));
            $kodeAkun = $tx->kode_akun ?? ($tx->jenis_transaksi === 'masuk' ? '411' : '512');

            $sheet->setCellValue('A' . $row, $tx->tanggal->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, $tx->keterangan);
            $sheet->setCellValue('C' . $row, $noBukti);
            $sheet->setCellValue('D' . $row, $kodeAkun);
            $sheet->setCellValue('E' . $row, $masuk);
            $sheet->setCellValue('F' . $row, $keluar);
            $sheet->setCellValue('G' . $row, $saldoBerjalan);

            $row++;
        }

        // Baris Total
        $sheet->setCellValue('A' . $row, 'Jumlah');
        $sheet->setCellValue('E' . $row, $totalMasuk);
        $sheet->setCellValue('F' . $row, $totalKeluar);
        $sheet->setCellValue('G' . $row, $saldoBerjalan);
        $this->applyTotalStyle($sheet, "A{$row}:G{$row}");

        // Ringkasan Rekonsiliasi Kas
        $summaryRow = $row + 3;
        $sheet->setCellValue('B' . $summaryRow, 'Saldo Awal Kas');
        $sheet->setCellValue('C' . $summaryRow, $saldoAwalKas);
        $sheet->setCellValue('B' . ($summaryRow + 1), 'Pemasukan');
        $sheet->setCellValue('C' . ($summaryRow + 1), $totalMasuk);
        $sheet->setCellValue('B' . ($summaryRow + 2), 'Pengeluaran');
        $sheet->setCellValue('C' . ($summaryRow + 2), $totalKeluar);
        $sheet->setCellValue('B' . ($summaryRow + 3), 'Saldo Akhir');
        $sheet->setCellValue('C' . ($summaryRow + 3), $saldoBerjalan);
        $sheet->getStyle("B{$summaryRow}:B" . ($summaryRow + 3))->getFont()->setBold(true);

        // Penandatanganan
        $ttdRow = $summaryRow + 6;
        $sheet->setCellValue('B' . $ttdRow, 'Mengetahui,');
        $sheet->setCellValue('F' . $ttdRow, 'Kuala Alam, ' . date('t') . " {$bulanText} {$tahun}");
        $sheet->setCellValue('B' . ($ttdRow + 1), 'DIREKTUR');
        $sheet->setCellValue('F' . ($ttdRow + 1), 'BENDAHARA');

        $sheet->setCellValue('B' . ($ttdRow + 5), 'ZULKIFLI');
        $sheet->setCellValue('F' . ($ttdRow + 5), 'ZULFIKAR');
        $sheet->getStyle("B" . ($ttdRow + 5) . ":F" . ($ttdRow + 5))->getFont()->setBold(true)->setUnderline(true);

        $this->formatCurrencyRange($sheet, "E10:G{$row}");
        $this->formatCurrencyRange($sheet, "C{$summaryRow}:C" . ($summaryRow + 3));
        $this->autoFitColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G']);
    }

    // =============================================================
    // SHEET 2: PERSENTASE PEMB PENDAPATAN 1
    // =============================================================
    protected function buildPersentasePembPendapatan1Sheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('PERSENTASE PEMB PENDAPATAN 1');

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'PEMBAGIAN JASA');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->getStyle('B1:B3')->getFont()->setBold(true);

        // Unit columns: USP (B-C), Jasa Wifi (E-F), Tenda (H-I), Pertanian (K-L)
        $sheet->setCellValue('B6', 'USP');
        $sheet->setCellValue('E6', 'UNIT PELAYANAN JASA (WIFI)');
        $sheet->setCellValue('H6', 'PENYEWAAN TENDA');
        $sheet->setCellValue('K6', 'PERTANIAN (NANAS)');
        $sheet->getStyle('B6:L6')->getFont()->setBold(true);

        // Pendapatan real dari unit
        $uspIncome = $this->accountingService->getIncomeStatement($tahun, $bulan, 2)['total_pendapatan'] ?? 0;
        $wifiIncome = $this->accountingService->getIncomeStatement($tahun, $bulan, 3)['total_pendapatan'] ?? 0;
        $kebunIncome = $this->accountingService->getIncomeStatement($tahun, $bulan, 4)['total_pendapatan'] ?? 0;

        $sheet->setCellValue('B7', $uspIncome);
        $sheet->setCellValue('E7', $wifiIncome);
        $sheet->setCellValue('H7', 0);
        $sheet->setCellValue('K7', $kebunIncome);

        // Komponen Pembagian: Utang Pajak 0.5%
        $sheet->setCellValue('B8', 'UTANG PAJAK/BLN');
        $sheet->setCellValue('B9', '0.5%');
        $sheet->setCellValue('C9', $uspIncome * 0.005);
        $sheet->setCellValue('E8', 'UTANG PAJAK/BLN');
        $sheet->setCellValue('E9', '0.5%');
        $sheet->setCellValue('F9', $wifiIncome * 0.005);
        $sheet->setCellValue('K8', 'UTANG PAJAK/BLN');
        $sheet->setCellValue('K9', '0.5%');
        $sheet->setCellValue('L9', $kebunIncome * 0.005);

        // Pendapatan utk dibagi (99.5%)
        $sheet->setCellValue('B10', 'PENDAPATAN UTK DI BAGI');
        $sheet->setCellValue('C11', $uspIncome * 0.995);
        $sheet->setCellValue('E10', 'PENDAPATAN UTK DI BAGI');
        $sheet->setCellValue('F11', $wifiIncome * 0.995);
        $sheet->setCellValue('K10', 'PENDAPATAN UTK DI BAGI');
        $sheet->setCellValue('L11', $kebunIncome * 0.995);

        // Biaya Operasional (10%)
        $sheet->setCellValue('B12', 'Biaya Operasional');
        $sheet->setCellValue('B13', '10%');
        $sheet->setCellValue('C13', $uspIncome * 0.0995);
        $sheet->setCellValue('E12', 'BIAYA OPERASIONAL');
        $sheet->setCellValue('E13', '10%');
        $sheet->setCellValue('F13', $wifiIncome * 0.0995);
        $sheet->setCellValue('K12', 'BIAYA OPERASIONAL');
        $sheet->setCellValue('K13', '10%');
        $sheet->setCellValue('L13', $kebunIncome * 0.0995);

        // Hadiah Pemanfaat / Perawatan (10%)
        $sheet->setCellValue('B14', 'Hadiah Pemanfaat');
        $sheet->setCellValue('B15', '10%');
        $sheet->setCellValue('C15', $uspIncome * 0.0995);
        $sheet->setCellValue('E14', 'BIAYA PERAWATAN');
        $sheet->setCellValue('E15', '10%');
        $sheet->setCellValue('F15', $wifiIncome * 0.0995);
        $sheet->setCellValue('K14', 'PENGEMBALIAN MODAL');
        $sheet->setCellValue('K15', '10%');
        $sheet->setCellValue('L15', $kebunIncome * 0.0995);

        // Pendapatan untuk BUMDesa (80%)
        $sheet->setCellValue('B20', 'PENDAPATAN UTK BUMDESA');
        $sheet->setCellValue('C21', $uspIncome * 0.796);
        $sheet->setCellValue('E20', 'PENDAPATAN UTK BUMDESA');
        $sheet->setCellValue('F21', $wifiIncome * 0.796);
        $sheet->setCellValue('K20', 'PENDAPATAN UTK BUMDESA');
        $sheet->setCellValue('L21', $kebunIncome * 0.796);

        // Total Pendapatan Usaha dari Semua Unit
        $totalSemuaUnit = $uspIncome + $wifiIncome + $kebunIncome;
        $sheet->setCellValue('E25', 'PENDAPATAN USAHA DARI SEMUA UNIT');
        $sheet->setCellValue('F26', $totalSemuaUnit);
        $sheet->getStyle('E25:F26')->getFont()->setBold(true);

        $this->formatCurrencyRange($sheet, 'B7:L26');
        $this->autoFitColumns($sheet, ['B', 'C', 'E', 'F', 'H', 'I', 'K', 'L']);
    }

    // =============================================================
    // SHEET 3: PERSENTASE PEMB. PENDAPATAN 2
    // =============================================================
    protected function buildPersentasePembPendapatan2Sheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('PERSENTASE PEMB. PENDAPATAN 2');

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'PEMBAGIAN JASA');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->getStyle('B1:B3')->getFont()->setBold(true);

        $headers = ['USP', 'DAGANG / WIFI', 'BRI LINK', 'TENDA / KEBUN'];
        $cols = ['B', 'E', 'H', 'K'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '5', $h);
        }
        $sheet->getStyle('B5:K5')->getFont()->setBold(true);

        $sheet->setCellValue('B7', 'UTANG PAJAK/BLN');
        $sheet->setCellValue('B8', '0.5%');
        $sheet->setCellValue('B9', 'PENDAPATAN UTK DI BAGI');

        $sheet->setCellValue('B18', 'OPERASIONAL UNIT');
        $sheet->setCellValue('E18', 'HADIAH PEMANFAAT/CAD. BIAYA');
        $sheet->setCellValue('H18', 'GAJI KARYAWAN DAN LABA USAHA');

        $sheet->setCellValue('C21', 'UNIT USP');
        $sheet->setCellValue('H21', 'GAJI UNIT');
        $sheet->setCellValue('C23', 'UNIT DAGANG / WIFI');
        $sheet->setCellValue('C25', 'UNIT KEBUN');
        $sheet->setCellValue('H24', 'LABA USAHA');

        $sheet->getStyle('B18:H24')->getFont()->setBold(true);
        $this->autoFitColumns($sheet, ['B', 'C', 'E', 'F', 'H', 'I', 'K', 'L']);
    }

    // =============================================================
    // SHEET 4: DUMDUKBUMDESA
    // =============================================================
    protected function buildDumdukBumdesaSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('DUMDUKBUMDESA');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        // 1. DAFTAR UANG MASUK (DUM)
        $sheet->setCellValue('A2', 'DAFTAR UANG MASUK BUMDESA');
        $sheet->setCellValue('A4', 'Bulan');
        $sheet->setCellValue('B4', ": {$bulanText} {$tahun}");
        $sheet->setCellValue('C5', 'Pemasukan (Rp)');
        $sheet->getStyle('A2:C5')->getFont()->setBold(true);

        $sheet->setCellValue('A6', 'Tgl');
        $sheet->setCellValue('B6', 'Uraian');
        $sheet->setCellValue('C6', 'Aktiva');
        $sheet->setCellValue('F6', 'Hutang');
        $sheet->setCellValue('L6', 'Modal');
        $sheet->setCellValue('P6', 'Pendapatan');

        // Header akun-akun
        $accounts = [
            'C' => ['112', 'Bank'],
            'D' => ['113', 'Modal Usaha Ke Unit Usaha'],
            'E' => ['117', 'Inventaris'],
            'F' => ['212', 'Hutang pihak lain'],
            'G' => ['213', 'Titipan'],
            'H' => ['214', 'PADes'],
            'I' => ['215', 'Utang Usaha'],
            'J' => ['216', 'Utang Pajak'],
            'K' => ['217', 'Titipan gebyar/cad. Biaya unit'],
            'L' => ['218', 'Tunjangan Kinerja'],
            'M' => ['311', 'Penyertaan Modal'],
            'N' => ['312', 'Modal Dari Pihak Lain'],
            'O' => ['313', 'Modal dari cad. Pengemb. Usaha unit'],
            'P' => ['411', 'Pendapatan peny. Modal utk operasional'],
            'Q' => ['412', 'Pendapatan Tak Terduga'],
            'R' => ['413', 'Bunga Bank'],
            'S' => ['414', 'Pendapatan dari Unit USP'],
            'T' => ['415', 'Pendapatan dari Unit Pertanian'],
            'U' => ['416', 'Pendapatan dari Unit Penyewaan'],
            'V' => ['417', 'Pendapatan dari Unit Jasa / Wifi'],
            'W' => ['418', 'Pendapatan dari Unit Usaha'],
        ];

        foreach ($accounts as $col => $acc) {
            $sheet->setCellValue($col . '7', $acc[1]);
            $sheet->setCellValue($col . '8', $acc[0]);
        }
        $this->applyHeaderStyle($sheet, 'A6:W8');

        // Transaksi Masuk
        $qMasuk = Transaksi::whereYear('tanggal', $tahun)
            ->where('jenis_transaksi', 'masuk')
            ->where('status', 'disetujui');
        if ($bulan > 0) $qMasuk->whereMonth('tanggal', $bulan);
        $txMasuk = $qMasuk->orderBy('tanggal', 'asc')->get();

        $row = 9;
        foreach ($txMasuk as $tx) {
            $sheet->setCellValue('A' . $row, $tx->tanggal->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $tx->keterangan);

            // Petakan ke kolom akun lawan yang sesuai
            $placed = false;
            foreach ($accounts as $col => $acc) {
                if ($tx->kode_akun === $acc[0]) {
                    $sheet->setCellValue($col . $row, (float) $tx->nominal);
                    $placed = true;
                    break;
                }
            }
            if (!$placed) {
                // Default ke Pendapatan Operasional (P)
                $sheet->setCellValue('P' . $row, (float) $tx->nominal);
            }
            $row++;
        }

        // Subtotal DUM
        $sheet->setCellValue('B' . $row, 'Jumlah s/d halaman ini');
        foreach ($accounts as $col => $acc) {
            $sheet->setCellValue($col . $row, "=SUM({$col}9:{$col}" . ($row - 1) . ")");
        }
        $this->applyTotalStyle($sheet, "A{$row}:W{$row}");

        // 2. DAFTAR UANG KELUAR (DUK)
        $dukStartRow = $row + 5;
        $sheet->setCellValue('A' . $dukStartRow, 'DAFTAR UANG KELUAR BUMDESA');
        $sheet->setCellValue('A' . ($dukStartRow + 2), 'Tgl');
        $sheet->setCellValue('B' . ($dukStartRow + 2), 'Uraian');
        $sheet->setCellValue('C' . ($dukStartRow + 2), 'Aktiva / Beban');

        $dukAccounts = [
            'C' => ['112', 'Bank'],
            'D' => ['113', 'Modal Usaha Ke Unit Usaha'],
            'E' => ['117', 'Inventaris'],
            'F' => ['212', 'Hutang pihak lain'],
            'G' => ['214', 'PADes'],
            'H' => ['313', 'Prive Cad. Pengemb. Usaha'],
            'I' => ['511', 'Biaya Bunga'],
            'J' => ['512', 'Beban Operasional & ATK'],
            'K' => ['513', 'Pajak & Adm Bank'],
        ];

        foreach ($dukAccounts as $col => $acc) {
            $sheet->setCellValue($col . ($dukStartRow + 1), $acc[1]);
            $sheet->setCellValue($col . ($dukStartRow + 2), $acc[0]);
        }
        $this->applyHeaderStyle($sheet, "A" . ($dukStartRow + 1) . ":K" . ($dukStartRow + 2));

        $qKeluar = Transaksi::whereYear('tanggal', $tahun)
            ->where('jenis_transaksi', 'keluar')
            ->where('status', 'disetujui');
        if ($bulan > 0) $qKeluar->whereMonth('tanggal', $bulan);
        $txKeluar = $qKeluar->orderBy('tanggal', 'asc')->get();

        $dRow = $dukStartRow + 3;
        foreach ($txKeluar as $tx) {
            $sheet->setCellValue('A' . $dRow, $tx->tanggal->format('d/m/Y'));
            $sheet->setCellValue('B' . $dRow, $tx->keterangan);

            $placed = false;
            foreach ($dukAccounts as $col => $acc) {
                if ($tx->kode_akun === $acc[0]) {
                    $sheet->setCellValue($col . $dRow, (float) $tx->nominal);
                    $placed = true;
                    break;
                }
            }
            if (!$placed) {
                $sheet->setCellValue('J' . $dRow, (float) $tx->nominal); // Default Beban Operasional
            }
            $dRow++;
        }

        $sheet->setCellValue('B' . $dRow, 'Jumlah Pengeluaran s/d halaman ini');
        foreach ($dukAccounts as $col => $acc) {
            $sheet->setCellValue($col . $dRow, "=SUM({$col}" . ($dukStartRow + 3) . ":{$col}" . ($dRow - 1) . ")");
        }
        $this->applyTotalStyle($sheet, "A{$dRow}:K{$dRow}");

        $this->formatCurrencyRange($sheet, "C9:W{$row}");
        $this->formatCurrencyRange($sheet, "C" . ($dukStartRow + 3) . ":K{$dRow}");
        $this->autoFitColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K']);
    }

    // =============================================================
    // SHEET 5: LKN I (Neraca Saldo)
    // =============================================================
    protected function buildNeracaSaldoSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('LKN I');

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'NERACA SALDO');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->getStyle('B1:B3')->getFont()->setBold(true);

        $sheet->setCellValue('D6', 'SALDO AWAL (Rp)');
        $sheet->setCellValue('F6', 'MUTASI (Rp)');
        $sheet->setCellValue('H6', 'SALDO AKHIR (Rp)');
        $sheet->setCellValue('B7', 'BUMDESA');
        $sheet->setCellValue('D7', 'D');
        $sheet->setCellValue('E7', 'K');
        $sheet->setCellValue('F7', 'D');
        $sheet->setCellValue('G7', 'K');
        $sheet->setCellValue('H7', 'D');
        $sheet->setCellValue('I7', 'K');
        $this->applyHeaderStyle($sheet, 'B6:I7');

        $trialBalance = $this->accountingService->getTrialBalance($tahun, $bulan > 0 ? $bulan : null);

        $row = 8;
        $sheet->setCellValue('B' . $row, '1');
        $sheet->setCellValue('C' . $row, 'AKTIVA');
        $sheet->getStyle("B{$row}:C{$row}")->getFont()->setBold(true);
        $row++;

        // Daftar Akun Standar LKN
        $standardAccounts = [
            // Aktiva
            ['111', 'Kas', 'Aset', 'Debit', 412000, 0],
            ['112', 'Bank', 'Aset', 'Debit', 13475874, 0],
            ['113', 'Modal Usaha Ke Unit Usaha', 'Aset', 'Debit', 1153609819, 0],
            ['117', 'Inventaris', 'Aset', 'Debit', 5380000, 0],
            ['118', 'Ak. Penyusutan', 'Aset', 'Kredit', 0, 0],
            // Hutang
            ['212', 'Hutang pihak lain', 'Kewajiban', 'Kredit', 0, 0],
            ['213', 'Titipan', 'Kewajiban', 'Kredit', 0, 0],
            ['214', 'PADes', 'Kewajiban', 'Kredit', 0, 6152000],
            ['215', 'Utang Usaha', 'Kewajiban', 'Kredit', 0, 0],
            ['216', 'Utang Pajak', 'Kewajiban', 'Kredit', 0, 0],
            ['217', 'Titipan untuk gebyar/cad. Biaya unit', 'Kewajiban', 'Kredit', 0, 0],
            ['218', 'Tunjangan Kinerja', 'Kewajiban', 'Kredit', 0, 0],
            // Modal
            ['311', 'Penyertaan Modal', 'Ekuitas', 'Kredit', 0, 1178206819],
            ['312', 'Modal Dari Pihak Lain', 'Ekuitas', 'Kredit', 0, 0],
            ['313', 'Modal dari cad. Pengemb. Usaha unit', 'Ekuitas', 'Kredit', 0, 11169000],
            // Pendapatan & Beban
            ['413', 'Pendapatan Bunga Bank', 'Pendapatan', 'Kredit', 0, 23625853],
            ['512', 'Beban Operasional & ATK', 'Beban', 'Debit', 150000, 0],
            ['513', 'Pajak & Administrasi Bank', 'Beban', 'Debit', 4000, 0],
        ];

        foreach ($standardAccounts as $item) {
            $code = $item[0];
            $name = $item[1];
            $saldoAwalD = $item[4];
            $saldoAwalK = $item[5];

            // Cari mutasi dari transaksi riil
            $qMutasiD = JurnalDetail::where('kode_akun', $code)
                ->whereHas('journal', function ($q) use ($tahun, $bulan) {
                    $q->whereYear('tanggal', $tahun);
                    if ($bulan > 0) $q->whereMonth('tanggal', $bulan);
                })->sum('debit');

            $qMutasiK = JurnalDetail::where('kode_akun', $code)
                ->whereHas('journal', function ($q) use ($tahun, $bulan) {
                    $q->whereYear('tanggal', $tahun);
                    if ($bulan > 0) $q->whereMonth('tanggal', $bulan);
                })->sum('kredit');

            $mutasiD = (float) $qMutasiD;
            $mutasiK = (float) $qMutasiK;

            // Saldo Akhir
            $saldoAkhirD = 0;
            $saldoAkhirK = 0;
            if ($item[3] === 'Debit') {
                $saldoAkhirD = max(0, $saldoAwalD + $mutasiD - $mutasiK);
            } else {
                $saldoAkhirK = max(0, $saldoAwalK + $mutasiK - $mutasiD);
            }

            $sheet->setCellValue('B' . $row, $code);
            $sheet->setCellValue('C' . $row, $name);
            $sheet->setCellValue('D' . $row, $saldoAwalD > 0 ? $saldoAwalD : 'x');
            $sheet->setCellValue('E' . $row, $saldoAwalK > 0 ? $saldoAwalK : 'x');
            $sheet->setCellValue('F' . $row, $mutasiD);
            $sheet->setCellValue('G' . $row, $mutasiK);
            $sheet->setCellValue('H' . $row, $saldoAkhirD > 0 ? $saldoAkhirD : 'x');
            $sheet->setCellValue('I' . $row, $saldoAkhirK > 0 ? $saldoAkhirK : 'x');
            $row++;
        }

        // Baris Total Neraca Saldo
        $sheet->setCellValue('B' . $row, 'TOTAL');
        $sheet->setCellValue('D' . $row, "=SUM(D9:D" . ($row - 1) . ")");
        $sheet->setCellValue('E' . $row, "=SUM(E9:E" . ($row - 1) . ")");
        $sheet->setCellValue('F' . $row, "=SUM(F9:F" . ($row - 1) . ")");
        $sheet->setCellValue('G' . $row, "=SUM(G9:G" . ($row - 1) . ")");
        $sheet->setCellValue('H' . $row, "=SUM(H9:H" . ($row - 1) . ")");
        $sheet->setCellValue('I' . $row, "=SUM(I9:I" . ($row - 1) . ")");
        $this->applyTotalStyle($sheet, "B{$row}:I{$row}");

        $this->formatCurrencyRange($sheet, "D8:I{$row}");
        $this->autoFitColumns($sheet, ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    // =============================================================
    // SHEET 6: JM (Jurnal Memorial)
    // =============================================================
    protected function buildJurnalMemorialSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('JM');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'JURNAL MEMORIAL');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->setCellValue('B4', 'BUMDESA');
        $sheet->setCellValue('D4', ': KUALA ALAM BANDAR JAYA');
        $sheet->setCellValue('B5', 'DESA');
        $sheet->setCellValue('D5', ': KUALA ALAM');
        $sheet->setCellValue('B6', 'KECAMATAN');
        $sheet->setCellValue('D6', ': BENGKALIS');
        $sheet->setCellValue('B7', 'KABUPATEN');
        $sheet->setCellValue('D7', ': BENGKALIS');
        $sheet->getStyle('B1:B7')->getFont()->setBold(true);

        $sheet->setCellValue('B9', 'DEBET');
        $sheet->setCellValue('F9', 'KREDIT');
        $sheet->setCellValue('B10', 'BUMDESA');
        $sheet->setCellValue('F10', 'BUMDESA');
        $this->applyHeaderStyle($sheet, 'B9:I10');

        $sheet->setCellValue('B11', '1');
        $sheet->setCellValue('C11', 'AKTIVA');
        $sheet->setCellValue('F11', '1');
        $sheet->setCellValue('G11', 'AKTIVA');
        $sheet->getStyle('B11:G11')->getFont()->setBold(true);

        $rows = [
            ['112', 'Bank Bumdesa', 0, '112', 'Bank Bumdesa', 4000],
            ['113', 'Modal Ke Unit Usaha', 0, '113', 'Modal Usaha dari Unit', 0],
            ['117', 'Inventaris', 0, '117', 'Inventaris', 0],
            ['118', 'Ak. Penyusutan', 0, '118', 'Ak. Penyusutan', 0],
            ['214', 'PADes', 0, '214', 'PADes', 0],
            ['215', 'Utang Usaha', 0, '215', 'Utang Usaha', 0],
            ['216', 'Utang Pajak', 0, '216', 'Utang Pajak', 0],
            ['217', 'Titipan untuk gebyar/cad. Biaya unit', 0, '217', 'Titipan untuk gebyar/cad. Biaya unit', 0],
            ['218', 'Tunjangan Kinerja', 0, '218', 'Tunjangan Kinerja', 0],
            ['311', 'Penyertaan Modal', 0, '311', 'Penyertaan Modal', 0],
            ['313', 'Cad. Pengemb. Usaha Unit', 7350000, '313', 'Cad. Pengemb. Usaha Unit', 7350000],
            ['513', 'Pajak & Administrasi Bank', 4000, '413', 'Pendapatan Bunga Bank', 0],
        ];

        $r = 13;
        foreach ($rows as $item) {
            $sheet->setCellValue('B' . $r, $item[0]);
            $sheet->setCellValue('C' . $r, $item[1]);
            $sheet->setCellValue('D' . $r, 'Rp');
            $sheet->setCellValue('E' . $r, $item[2]);

            $sheet->setCellValue('F' . $r, $item[3]);
            $sheet->setCellValue('G' . $r, $item[4]);
            $sheet->setCellValue('H' . $r, 'Rp');
            $sheet->setCellValue('I' . $r, $item[5]);
            $r++;
        }

        $sheet->setCellValue('C' . $r, 'TOTAL');
        $sheet->setCellValue('E' . $r, "=SUM(E13:E" . ($r - 1) . ")");
        $sheet->setCellValue('G' . $r, 'TOTAL');
        $sheet->setCellValue('I' . $r, "=SUM(I13:I" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "B{$r}:I{$r}");

        $this->formatCurrencyRange($sheet, "E13:E{$r}");
        $this->formatCurrencyRange($sheet, "I13:I{$r}");
        $this->autoFitColumns($sheet, ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    // =============================================================
    // SHEET 7: LABARUGI (3 Kolom: Lalu, Bulanan, Kumulatif)
    // =============================================================
    protected function buildLabaRugiSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('LABARUGI');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        $sheet->setCellValue('A1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('A2', 'LAPORAN LABA-RUGI');
        $sheet->setCellValue('A3', "Tahun {$tahun}");
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        $sheet->setCellValue('F6', 'BUKU LAPORAN LABA/RUGI');
        $sheet->setCellValue('F8', 'BUMDESA');
        $sheet->setCellValue('H8', ': KUALA ALAM BANDAR JAYA');
        $sheet->setCellValue('F9', 'DESA');
        $sheet->setCellValue('H9', ': KUALA ALAM');
        $sheet->setCellValue('F10', 'KECAMATAN');
        $sheet->setCellValue('H10', ': BENGKALIS');
        $sheet->setCellValue('F11', 'KABUPATEN');
        $sheet->setCellValue('H11', ': BENGKALIS');
        $sheet->getStyle('F6:H11')->getFont()->setBold(true);

        // Header 3 Kolom Laba Rugi
        $sheet->setCellValue('A13', 'Bulan Lalu');
        $sheet->setCellValue('F13', "Bulanan (Periode : 1 s/d " . date('t') . " {$bulanText} {$tahun})");
        $sheet->setCellValue('K13', "Kumulatif (Periode : 1 Januari s/d " . date('t') . " {$bulanText} {$tahun})");
        $this->applyHeaderStyle($sheet, 'A13:E13');
        $this->applyHeaderStyle($sheet, 'F13:J13');
        $this->applyHeaderStyle($sheet, 'K13:O13');

        $pendapatanRows = [
            ['411', 'Pendapatan peny. Modal utk operasional'],
            ['412', 'Pendapatan Tak Terduga'],
            ['413', 'Bunga Bank'],
            ['414', 'Pendapatan dari Unit USP'],
            ['415', 'Pendapatan dari Unit Pertanian / Kebun'],
            ['416', 'Pendapatan dari Unit Penyewaan'],
            ['417', 'Pendapatan dari Unit Jasa / Wifi'],
            ['418', 'Pendapatan dari Unit Usaha'],
        ];

        $bebanRows = [
            ['511', 'Beban Bunga / Hadiah'],
            ['512', 'Beban Operasional & ATK'],
            ['513', 'Pajak & Administrasi Bank'],
        ];

        // Laba rugi bulan lalu, bulan ini, dan kumulatif
        $incCurrent = $this->accountingService->getIncomeStatement($tahun, $bulan > 0 ? $bulan : null);
        $incLalu = ($bulan > 1) ? $this->accountingService->getIncomeStatement($tahun, $bulan - 1) : ['total_pendapatan' => 0, 'total_beban' => 0, 'laba_bersih' => 0];
        $incKumulatif = $this->accountingService->getIncomeStatement($tahun, null);

        $row = 16;
        $sheet->setCellValue('A' . $row, '4');
        $sheet->setCellValue('B' . $row, 'PENDAPATAN');
        $sheet->setCellValue('F' . $row, '4');
        $sheet->setCellValue('G' . $row, 'PENDAPATAN');
        $sheet->setCellValue('K' . $row, '4');
        $sheet->setCellValue('L' . $row, 'PENDAPATAN');
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($pendapatanRows as $p) {
            $sheet->setCellValue('A' . $row, $p[0]);
            $sheet->setCellValue('B' . $row, $p[1]);
            $sheet->setCellValue('C' . $row, 'Rp');
            $sheet->setCellValue('D' . $row, ($p[0] === '413') ? 23625853 : 0);

            $sheet->setCellValue('F' . $row, $p[0]);
            $sheet->setCellValue('G' . $row, $p[1]);
            $sheet->setCellValue('H' . $row, 'Rp');
            $valNow = 0;
            if ($p[0] === '414') $valNow = $this->accountingService->getIncomeStatement($tahun, $bulan, 2)['total_pendapatan'] ?? 0;
            if ($p[0] === '417') $valNow = $this->accountingService->getIncomeStatement($tahun, $bulan, 3)['total_pendapatan'] ?? 0;
            if ($p[0] === '415') $valNow = $this->accountingService->getIncomeStatement($tahun, $bulan, 4)['total_pendapatan'] ?? 0;
            $sheet->setCellValue('I' . $row, $valNow);

            $sheet->setCellValue('K' . $row, $p[0]);
            $sheet->setCellValue('L' . $row, $p[1]);
            $sheet->setCellValue('M' . $row, 'Rp');
            $sheet->setCellValue('N' . $row, ($p[0] === '413') ? 23625853 + $valNow : $valNow);
            $row++;
        }

        $sheet->setCellValue('B' . $row, 'TOTAL PENDAPATAN');
        $sheet->setCellValue('D' . $row, "=SUM(D17:D" . ($row - 1) . ")");
        $sheet->setCellValue('G' . $row, 'TOTAL PENDAPATAN');
        $sheet->setCellValue('I' . $row, "=SUM(I17:I" . ($row - 1) . ")");
        $sheet->setCellValue('L' . $row, 'TOTAL PENDAPATAN');
        $sheet->setCellValue('N' . $row, "=SUM(N17:N" . ($row - 1) . ")");
        $this->applyTotalStyle($sheet, "A{$row}:N{$row}");
        $row += 2;

        // Beban
        $bebanStart = $row;
        $sheet->setCellValue('A' . $row, '5');
        $sheet->setCellValue('B' . $row, 'BIAYA / BEBAN');
        $sheet->setCellValue('F' . $row, '5');
        $sheet->setCellValue('G' . $row, 'BIAYA / BEBAN');
        $sheet->setCellValue('K' . $row, '5');
        $sheet->setCellValue('L' . $row, 'BIAYA / BEBAN');
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($bebanRows as $b) {
            $sheet->setCellValue('A' . $row, $b[0]);
            $sheet->setCellValue('B' . $row, $b[1]);
            $sheet->setCellValue('C' . $row, 'Rp');
            $sheet->setCellValue('D' . $row, ($b[0] === '513') ? 4000 : 0);

            $sheet->setCellValue('F' . $row, $b[0]);
            $sheet->setCellValue('G' . $row, $b[1]);
            $sheet->setCellValue('H' . $row, 'Rp');
            $bNow = ($b[0] === '512') ? ($incCurrent['total_beban'] ?? 0) : (($b[0] === '513') ? 4000 : 0);
            $sheet->setCellValue('I' . $row, $bNow);

            $sheet->setCellValue('K' . $row, $b[0]);
            $sheet->setCellValue('L' . $row, $b[1]);
            $sheet->setCellValue('M' . $row, 'Rp');
            $sheet->setCellValue('N' . $row, ($b[0] === '513') ? 4000 : $bNow);
            $row++;
        }

        $sheet->setCellValue('B' . $row, 'TOTAL BIAYA');
        $sheet->setCellValue('D' . $row, "=SUM(D" . ($bebanStart + 1) . ":D" . ($row - 1) . ")");
        $sheet->setCellValue('G' . $row, 'TOTAL BIAYA');
        $sheet->setCellValue('I' . $row, "=SUM(I" . ($bebanStart + 1) . ":I" . ($row - 1) . ")");
        $sheet->setCellValue('L' . $row, 'TOTAL BIAYA');
        $sheet->setCellValue('N' . $row, "=SUM(N" . ($bebanStart + 1) . ":N" . ($row - 1) . ")");
        $this->applyTotalStyle($sheet, "A{$row}:N{$row}");
        $row += 2;

        // SHU / Laba Bersih
        $sheet->setCellValue('B' . $row, 'LABA / RUGI BERJALAN (SHU)');
        $sheet->setCellValue('D' . $row, "=D25-D" . ($row - 2));
        $sheet->setCellValue('G' . $row, 'LABA / RUGI BERJALAN (SHU)');
        $sheet->setCellValue('I' . $row, "=I25-I" . ($row - 2));
        $sheet->setCellValue('L' . $row, 'LABA / RUGI BERJALAN (SHU)');
        $sheet->setCellValue('N' . $row, "=N25-N" . ($row - 2));
        $this->applyTotalStyle($sheet, "A{$row}:N{$row}");

        $this->formatCurrencyRange($sheet, "D17:N{$row}");
        $this->autoFitColumns($sheet, ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N']);
    }

    // =============================================================
    // SHEET 8: INV (Daftar Inventaris)
    // =============================================================
    protected function buildInventarisSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('INV');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        $sheet->setCellValue('B1', 'DAFTAR INVENTARIS');
        $sheet->setCellValue('B3', 'BUMDESA');
        $sheet->setCellValue('D3', ': KUALA ALAM BANDAR JAYA');
        $sheet->setCellValue('B4', 'DESA');
        $sheet->setCellValue('D4', ': KUALA ALAM');
        $sheet->setCellValue('B5', 'KECAMATAN');
        $sheet->setCellValue('D5', ': BENGKALIS');
        $sheet->setCellValue('B6', 'KABUPATEN');
        $sheet->setCellValue('D6', ': BENGKALIS');
        $sheet->setCellValue('B7', 'BULAN');
        $sheet->setCellValue('D7', ": {$bulanText} {$tahun}");
        $sheet->getStyle('B1:D7')->getFont()->setBold(true);

        $headers = [
            'No', 'Jenis Inventaris', 'Tanggal Pembelian', 'Bukti Pembelian', 'Unit',
            'Harga Satuan (Rp)', 'Harga Perolehan', 'Umur Ekonomis', 'Penyusutan/bulan',
            'Umur Pakai', 'Akumulasi Penyusutan (Rp)', 'Nilai Buku (Rp)'
        ];
        $cols = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '8', $h);
        }
        $this->applyHeaderStyle($sheet, 'B8:M8');

        // Nomor urut kolom standar template pemerintah
        $subHeaders = ['1', '2', '3', '4', '5', '6', '7=5*6', '8', '9=7/8', '10', '11=9*10', '12=7-11'];
        foreach ($subHeaders as $i => $sh) {
            $sheet->setCellValue($cols[$i] . '9', $sh);
        }
        $sheet->getStyle('B9:M9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data Inventaris Riil BUMDesa Kuala Alam
        $inventarisItems = [
            ['Laptop Kantor Acer Aspire 5', '2022-03-15', 'BKK-01', 1, 4500000, 48, 24],
            ['Printer Epson L3210 All-in-One', '2022-03-20', 'BKK-02', 1, 880000, 36, 24],
        ];

        $r = 10;
        $no = 1;
        foreach ($inventarisItems as $inv) {
            $perolehan = $inv[3] * $inv[4];
            $penyusutanBulan = $inv[5] > 0 ? $perolehan / $inv[5] : 0;
            $akPenyusutan = $penyusutanBulan * $inv[6];
            $nilaiBuku = $perolehan - $akPenyusutan;

            $sheet->setCellValue('B' . $r, $no++);
            $sheet->setCellValue('C' . $r, $inv[0]);
            $sheet->setCellValue('D' . $r, $inv[1]);
            $sheet->setCellValue('E' . $r, $inv[2]);
            $sheet->setCellValue('F' . $r, $inv[3]);
            $sheet->setCellValue('G' . $r, $inv[4]);
            $sheet->setCellValue('H' . $r, $perolehan);
            $sheet->setCellValue('I' . $r, $inv[5]);
            $sheet->setCellValue('J' . $r, $penyusutanBulan);
            $sheet->setCellValue('K' . $r, $inv[6]);
            $sheet->setCellValue('L' . $r, $akPenyusutan);
            $sheet->setCellValue('M' . $r, $nilaiBuku);
            $r++;
        }

        // Baris Total Inventaris
        $sheet->setCellValue('C' . $r, 'TOTAL NILAI ASET INVENTARIS');
        $sheet->setCellValue('H' . $r, "=SUM(H10:H" . ($r - 1) . ")");
        $sheet->setCellValue('J' . $r, "=SUM(J10:J" . ($r - 1) . ")");
        $sheet->setCellValue('L' . $r, "=SUM(L10:L" . ($r - 1) . ")");
        $sheet->setCellValue('M' . $r, "=SUM(M10:M" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "B{$r}:M{$r}");

        // Penandatanganan
        $ttdRow = $r + 3;
        $sheet->setCellValue('C' . $ttdRow, 'Diketahui oleh,');
        $sheet->setCellValue('C' . ($ttdRow + 1), 'DIREKTUR');
        $sheet->setCellValue('C' . ($ttdRow + 5), 'ZULKIFLI');
        $sheet->getStyle('C' . ($ttdRow + 5))->getFont()->setBold(true)->setUnderline(true);

        $this->formatCurrencyRange($sheet, "G10:H{$r}");
        $this->formatCurrencyRange($sheet, "J10:J{$r}");
        $this->formatCurrencyRange($sheet, "L10:M{$r}");
        $this->autoFitColumns($sheet, ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M']);
    }

    // =============================================================
    // SHEET 9: Neraca (Laporan Posisi Keuangan SAK ETAP)
    // =============================================================
    protected function buildNeracaSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Neraca');

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'NERACA');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->getStyle('B1:B3')->getFont()->setBold(true);

        // Header
        $sheet->setCellValue('B5', 'KODE');
        $sheet->setCellValue('C5', 'AKTIVA');
        $sheet->setCellValue('K5', 'JUMLAH (Rp)');
        $this->applyHeaderStyle($sheet, 'B5:K5');

        $sheet->setCellValue('B6', '1');
        $sheet->setCellValue('C6', 'AKTIVA');
        $sheet->getStyle('B6:C6')->getFont()->setBold(true);

        $aktiva = [
            ['111', 'Kas', 412000],
            ['112', 'Bank', 13475874],
            ['113', 'Modal Usaha Ke Unit Usaha', 1153609819],
            ['117', 'Inventaris', 5380000],
            ['118', 'Ak. Penyusutan', 0],
        ];

        $r = 7;
        foreach ($aktiva as $a) {
            $sheet->setCellValue('B' . $r, $a[0]);
            $sheet->setCellValue('C' . $r, $a[1]);
            $sheet->setCellValue('J' . $r, 'Rp');
            $sheet->setCellValue('K' . $r, $a[2]);
            $r++;
        }

        $sheet->setCellValue('C' . $r, 'JUMLAH AKTIVA');
        $sheet->setCellValue('J' . $r, 'Rp');
        $sheet->setCellValue('K' . $r, "=SUM(K7:K" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "B{$r}:K{$r}");
        $r += 2;

        // PASSIVA
        $sheet->setCellValue('B' . $r, '2');
        $sheet->setCellValue('C' . $r, 'PASSIVA');
        $sheet->getStyle("B{$r}:C{$r}")->getFont()->setBold(true);
        $r++;

        $sheet->setCellValue('B' . $r, '2');
        $sheet->setCellValue('C' . $r, 'HUTANG');
        $sheet->getStyle("B{$r}:C{$r}")->getFont()->setBold(true);
        $r++;

        $hutang = [
            ['212', 'Hutang pihak lain', 0],
            ['213', 'Titipan', 0],
            ['214', 'PADes', 6152000],
            ['215', 'Utang Usaha', 0],
            ['216', 'Utang Pajak', 0],
        ];

        $hutangStart = $r;
        foreach ($hutang as $h) {
            $sheet->setCellValue('B' . $r, $h[0]);
            $sheet->setCellValue('C' . $r, $h[1]);
            $sheet->setCellValue('J' . $r, 'Rp');
            $sheet->setCellValue('K' . $r, $h[2]);
            $r++;
        }

        // MODAL
        $sheet->setCellValue('B' . $r, '3');
        $sheet->setCellValue('C' . $r, 'MODAL');
        $sheet->getStyle("B{$r}:C{$r}")->getFont()->setBold(true);
        $r++;

        $modal = [
            ['311', 'Penyertaan Modal', 1178206819],
            ['312', 'Modal Dari Pihak Lain', 0],
            ['313', 'Modal dari cad. Pengemb. Usaha unit', 11169000],
            ['314', 'Modal dari laba / SHU Berjalan', -16498126],
        ];

        $modalStart = $r;
        foreach ($modal as $m) {
            $sheet->setCellValue('B' . $r, $m[0]);
            $sheet->setCellValue('C' . $r, $m[1]);
            $sheet->setCellValue('J' . $r, 'Rp');
            $sheet->setCellValue('K' . $r, $m[2]);
            $r++;
        }

        $sheet->setCellValue('C' . $r, 'JUMLAH PASSIVA');
        $sheet->setCellValue('J' . $r, 'Rp');
        $sheet->setCellValue('K' . $r, "=SUM(K{$hutangStart}:K" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "B{$r}:K{$r}");

        $this->formatCurrencyRange($sheet, "K7:K{$r}");
        $this->autoFitColumns($sheet, ['B', 'C', 'J', 'K']);
    }

    // =============================================================
    // SHEET 10: MODAL KE UNIT USAHA
    // =============================================================
    protected function buildModalKeUnitSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('MODAL KE UNIT USAHA');

        $sheet->setCellValue('A1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('A2', 'LAPORAN PERUBAHAN MODAL UNIT USAHA');
        $sheet->setCellValue('A3', "Tahun {$tahun}");
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        $headers = [
            'NO', 'UNIT', '', 'MODAL UNIT S/D BULAN LALU', 'MODAL UNIT BULAN INI',
            'PENAMBAHAN MODAL DARI SHU', 'PENARIKAN MODAL BULAN INI', 'MODAL UNIT S/D BULAN INI'
        ];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headers as $i => $h) {
            if ($h) $sheet->setCellValue($cols[$i] . '7', $h);
        }
        $this->applyHeaderStyle($sheet, 'A7:H7');

        $units = [
            ['1', 'UNIT JASA KEUANGAN (USP)', 1096609819, 57000000, 0, 0, 1153609819],
            ['2', 'UNIT PERDAGANGAN DAN JASA (WIFI)', 25000000, 0, 0, 0, 25000000],
            ['3', 'UNIT PERKEBUNAN (NANAS)', 15000000, 0, 0, 0, 15000000],
        ];

        $r = 9;
        foreach ($units as $u) {
            $sheet->setCellValue('A' . $r, $u[0]);
            $sheet->setCellValue('B' . $r, $u[1]);
            $sheet->setCellValue('D' . $r, $u[2]);
            $sheet->setCellValue('E' . $r, $u[3]);
            $sheet->setCellValue('F' . $r, $u[4]);
            $sheet->setCellValue('G' . $r, $u[5]);
            $sheet->setCellValue('H' . $r, "=D{$r}+E{$r}+F{$r}-G{$r}");
            $r++;
        }

        $sheet->setCellValue('B' . $r, 'TOTAL MODAL KE UNIT');
        $sheet->setCellValue('D' . $r, "=SUM(D9:D" . ($r - 1) . ")");
        $sheet->setCellValue('E' . $r, "=SUM(E9:E" . ($r - 1) . ")");
        $sheet->setCellValue('F' . $r, "=SUM(F9:F" . ($r - 1) . ")");
        $sheet->setCellValue('G' . $r, "=SUM(G9:G" . ($r - 1) . ")");
        $sheet->setCellValue('H' . $r, "=SUM(H9:H" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "A{$r}:H{$r}");

        // Tabel Kondisi Pajak & Bunga Bank
        $pajakRow = $r + 3;
        $sheet->setCellValue('C' . $pajakRow, 'KONDISI BIAYA PAJAK DAN ADMINISTRASI BANK DAN BUNGA BANK');
        $sheet->getStyle('C' . $pajakRow)->getFont()->setBold(true);

        $headersPajak = ['SALDO PAJAK & ADM LALU', 'SALDO BUNGA LALU', 'PAJAK & ADM INI', 'BUNGA INI', 'PAJAK & ADM S/D INI', 'BUNGA S/D INI'];
        $pCols = ['C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headersPajak as $i => $hp) {
            $sheet->setCellValue($pCols[$i] . ($pajakRow + 1), $hp);
        }
        $this->applyHeaderStyle($sheet, "C" . ($pajakRow + 1) . ":H" . ($pajakRow + 1));

        $sheet->setCellValue('C' . ($pajakRow + 2), 0);
        $sheet->setCellValue('D' . ($pajakRow + 2), 0);
        $sheet->setCellValue('E' . ($pajakRow + 2), 4000);
        $sheet->setCellValue('F' . ($pajakRow + 2), 0);
        $sheet->setCellValue('G' . ($pajakRow + 2), "=C" . ($pajakRow + 2) . "+E" . ($pajakRow + 2));
        $sheet->setCellValue('H' . ($pajakRow + 2), "=D" . ($pajakRow + 2) . "+F" . ($pajakRow + 2));
        $this->applyTotalStyle($sheet, "C" . ($pajakRow + 2) . ":H" . ($pajakRow + 2));

        $this->formatCurrencyRange($sheet, "D9:H{$r}");
        $this->formatCurrencyRange($sheet, "C" . ($pajakRow + 2) . ":H" . ($pajakRow + 2));
        $this->autoFitColumns($sheet, ['A', 'B', 'D', 'E', 'F', 'G', 'H']);
    }

    // =============================================================
    // SHEET 11: AKUM. SHU
    // =============================================================
    protected function buildAkumulasiShuSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('AKUM. SHU');

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'LAPORAN PERKEMBANGAN SHU');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->getStyle('B1:B3')->getFont()->setBold(true);

        // SHU USP
        $sheet->setCellValue('B8', 'SHU USP');
        $sheet->setCellValue('B9', 'JENIS SHU');
        $sheet->setCellValue('C9', 'SALDO TH LALU');
        $sheet->setCellValue('D9', 'TAHUN INI');
        $sheet->setCellValue('E9', 'SALDO AKHIR');
        $this->applyHeaderStyle($sheet, 'B9:E9');

        $sheet->setCellValue('B10', 'PADES');
        $sheet->setCellValue('C10', 0);
        $sheet->setCellValue('D10', 3150000);
        $sheet->setCellValue('E10', '=C10+D10');

        $sheet->setCellValue('B11', 'MODAL CAD. PENGEMBANGAN USAHA');
        $sheet->setCellValue('C11', 0);
        $sheet->setCellValue('D11', 7350000);
        $sheet->setCellValue('E11', '=C11+D11');

        $sheet->setCellValue('B12', 'TOTAL SHU USP');
        $sheet->setCellValue('E12', '=SUM(E10:E11)');
        $this->applyTotalStyle($sheet, 'B12:E12');

        // SHU UNIT DAGANG DAN JASA (WIFI & KEBUN)
        $sheet->setCellValue('B15', 'SHU UNIT DAGANG DAN JASA');
        $sheet->setCellValue('B16', 'JENIS SHU');
        $sheet->setCellValue('C16', 'SALDO TH LALU');
        $sheet->setCellValue('D16', 'TAHUN INI');
        $sheet->setCellValue('E16', 'SALDO AKHIR');
        $this->applyHeaderStyle($sheet, 'B16:E16');

        $sheet->setCellValue('B17', 'PADES');
        $sheet->setCellValue('C17', 0);
        $sheet->setCellValue('D17', 0);
        $sheet->setCellValue('E17', '=C17+D17');

        $sheet->setCellValue('B18', 'MODAL CAD. PENGEMBANGAN USAHA');
        $sheet->setCellValue('C18', 0);
        $sheet->setCellValue('D18', 0);
        $sheet->setCellValue('E18', '=C18+D18');

        $sheet->setCellValue('B19', 'TOTAL SHU UNIT DAGANG & JASA');
        $sheet->setCellValue('E19', '=SUM(E17:E18)');
        $this->applyTotalStyle($sheet, 'B19:E19');

        // Penandatanganan
        $sheet->setCellValue('E23', 'Kuala Alam, 31 Desember ' . $tahun);
        $sheet->setCellValue('B24', 'DIREKTUR');
        $sheet->setCellValue('E24', 'BENDAHARA');
        $sheet->setCellValue('B28', 'ZULKIFLI');
        $sheet->setCellValue('E28', 'ZULFIKAR');
        $sheet->getStyle('B28:E28')->getFont()->setBold(true)->setUnderline(true);

        $this->formatCurrencyRange($sheet, 'C10:E19');
        $this->autoFitColumns($sheet, ['B', 'C', 'D', 'E']);
    }

    // =============================================================
    // SHEET 12: PERUBAHAN MODAL
    // =============================================================
    protected function buildPerubahanModalSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('PERUBAHAN MODAL');

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'LAPORAN PERUBAHAN MODAL USAHA');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->setCellValue('B7', 'PERUBAHAN MODAL');
        $sheet->getStyle('B1:B7')->getFont()->setBold(true);

        $sheet->setCellValue('B9', 'PENDAPATAN');
        $sheet->getStyle('B9')->getFont()->setBold(true);

        $sheet->setCellValue('B10', 'Penyertaan Modal Desa');
        $sheet->setCellValue('C10', 'x');
        $sheet->setCellValue('D10', 1178206819);

        $sheet->setCellValue('B11', 'Modal Dari Pihak Lain');
        $sheet->setCellValue('C11', 'x');
        $sheet->setCellValue('D11', 0);

        $sheet->setCellValue('B12', 'Cad. Pengemb. Usaha');
        $sheet->setCellValue('C12', 'x');
        $sheet->setCellValue('D12', 11169000);

        $sheet->setCellValue('B13', 'TOTAL PENDAPATAN MODAL');
        $sheet->setCellValue('D13', '=SUM(D10:D12)');
        $this->applyTotalStyle($sheet, 'B13:D13');

        $sheet->setCellValue('B14', 'PENGELUARAN');
        $sheet->getStyle('B14')->getFont()->setBold(true);

        $sheet->setCellValue('B15', 'Prive Pihak Lain');
        $sheet->setCellValue('C15', 0);
        $sheet->setCellValue('D15', 'x');

        $sheet->setCellValue('B16', 'Prive Cad. Pengemb. Usaha');
        $sheet->setCellValue('C16', 7350000);
        $sheet->setCellValue('D16', 'x');

        $sheet->setCellValue('B17', 'TOTAL PENGELUARAN MODAL');
        $sheet->setCellValue('D17', '=SUM(C15:C16)');
        $this->applyTotalStyle($sheet, 'B17:D17');

        $sheet->setCellValue('B18', 'Laba / Rugi Berjalan (SHU)');
        $sheet->setCellValue('D18', -16498126);

        $sheet->setCellValue('B19', 'MODAL SEKARANG');
        $sheet->setCellValue('D19', '=D13-D17+D18');
        $this->applyTotalStyle($sheet, 'B19:D19');

        $sheet->setCellValue('D22', 'Kuala Alam, 31 Desember ' . $tahun);
        $sheet->setCellValue('B23', 'DIREKTUR');
        $sheet->setCellValue('D23', 'BENDAHARA');
        $sheet->setCellValue('B27', 'ZULKIFLI');
        $sheet->setCellValue('D27', 'ZULFIKAR');
        $sheet->getStyle('B27:D27')->getFont()->setBold(true)->setUnderline(true);

        $this->formatCurrencyRange($sheet, 'C10:D19');
        $this->autoFitColumns($sheet, ['B', 'C', 'D']);
    }

    // =============================================================
    // SHEET 13: MODAL (Laporan Penyertaan Modal Usaha)
    // =============================================================
    protected function buildPenyertaanModalSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('MODAL');

        $sheet->setCellValue('B1', 'BUMDESA KUALA ALAM');
        $sheet->setCellValue('B2', 'LAPORAN PENYERTAAN MODAL USAHA');
        $sheet->setCellValue('B3', "Tahun {$tahun}");
        $sheet->setCellValue('B9', 'PENYERTAAN MODAL');
        $sheet->getStyle('B1:B9')->getFont()->setBold(true);

        $headers = ['NO.', 'URAIAN', 'TAHAP I', 'TAHAP II', 'TAHAP III', 'dst...', 'TOTAL'];
        $cols = ['B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '10', $h);
        }
        $this->applyHeaderStyle($sheet, 'B10:H10');

        $rows = [
            ['1', 'PENYERTAAN MODAL DESA', 1121206819, 57000000, 0, 0],
            ['2', 'PENYERTAAN MODAL DANA PROVINSI', 0, 0, 0, 0],
            ['3', 'PENYERTAAN MODAL BKK', 0, 0, 0, 0],
            ['4', 'CAD. PENGEMBANGAN USAHA', 11169000, 0, 0, 0],
        ];

        $r = 11;
        foreach ($rows as $item) {
            $sheet->setCellValue('B' . $r, $item[0]);
            $sheet->setCellValue('C' . $r, $item[1]);
            $sheet->setCellValue('D' . $r, $item[2]);
            $sheet->setCellValue('E' . $r, $item[3]);
            $sheet->setCellValue('F' . $r, $item[4]);
            $sheet->setCellValue('G' . $r, $item[5]);
            $sheet->setCellValue('H' . $r, "=SUM(D{$r}:G{$r})");
            $r++;
        }

        $sheet->setCellValue('C' . $r, 'TOTAL PENYERTAAN MODAL');
        $sheet->setCellValue('H' . $r, "=SUM(H11:H" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "B{$r}:H{$r}");

        $sheet->setCellValue('F18', 'Kuala Alam, 31 Desember ' . $tahun);
        $sheet->setCellValue('C19', 'DIREKTUR');
        $sheet->setCellValue('F19', 'BENDAHARA');
        $sheet->setCellValue('C23', 'ZULKIFLI');
        $sheet->setCellValue('F23', 'ZULFIKAR');
        $sheet->getStyle('C23:F23')->getFont()->setBold(true)->setUnderline(true);

        $this->formatCurrencyRange($sheet, "D11:H{$r}");
        $this->autoFitColumns($sheet, ['B', 'C', 'D', 'E', 'F', 'G', 'H']);
    }

    // =============================================================
    // SHEET 14: COVER (Halaman Sampul Resmi)
    // =============================================================
    protected function buildCoverSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('COVER');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        $sheet->setCellValue('B12', 'LAPORAN PERTANGGUNGJAWABAN KEUANGAN');
        $sheet->setCellValue('B14', 'BUMDESA "KUALA ALAM BANDAR JAYA"');
        $sheet->setCellValue('B15', 'DESA KUALA ALAM');
        $sheet->setCellValue('B16', "PERIODE : {$bulanText} {$tahun}");
        $sheet->setCellValue('B17', 'KECAMATAN BENGKALIS');
        $sheet->setCellValue('B18', 'KABUPATEN BENGKALIS');

        $sheet->getStyle('B12')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('B14:B18')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('B12:B18')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('B')->setWidth(50);
    }

    // =============================================================
    // SHEET 15: PERUBAHAN MODAL PERMENDES
    // =============================================================
    protected function buildPerubahanModalPermendesSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('PERUBAHAN MODAL PERMENDES');

        $sheet->setCellValue('B1', '2. PERUBAHAN MODAL (STANDAR PERMENDESA PDTT)');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(12);

        $sheet->setCellValue('B3', 'KOMPONEN PERUBAHAN MODAL');
        $sheet->setCellValue('C3', 'JUMLAH (Rp)');
        $this->applyHeaderStyle($sheet, 'B3:C3');

        $items = [
            ['Modal (Awal) per 1 Januari ' . $tahun, 1121206819],
            ['Modal (Tambahan) untuk tahun berjalan', 57000000],
            ['Saldo Laba Ditahan Awal Periode', 11169000],
            ['Saldo Laba Tahun Berjalan (SHU)', -16498126],
            ['Dividen / PADes Diserahkan', 6152000],
            ['Saldo Laba Ditahan Akhir', '=C7+C8-C9'],
            ['Modal Akhir BUMDesa', '=C5+C6+C10'],
        ];

        $r = 5;
        foreach ($items as $it) {
            $sheet->setCellValue('B' . $r, $it[0]);
            $sheet->setCellValue('C' . $r, $it[1]);
            $r++;
        }

        $this->applyTotalStyle($sheet, 'B11:C11');
        $this->formatCurrencyRange($sheet, 'C5:C11');
        $this->autoFitColumns($sheet, ['B', 'C']);
    }

    // =============================================================
    // SHEET 16: AMPRAH (Insentif Pengurus BUMDesa)
    // =============================================================
    protected function buildAmprahSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('AMPRAH');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        $sheet->setCellValue('A2', 'DAFTAR PENERIMAAN INSENTIF / HONOR PENGURUS');
        $sheet->setCellValue('A3', 'BUMDESA KUALA ALAM BANDAR JAYA');
        $sheet->setCellValue('A4', "PERIODE: {$bulanText} {$tahun}");
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);

        $headers = ['No', 'Nama Penerima', 'Jabatan', 'Jumlah (Rp)', 'Tanda Tangan'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '7', $h);
        }
        $this->applyHeaderStyle($sheet, 'A7:E7');

        $pengurus = [
            ['1', 'MOHD. RADZI', 'Penasehat (Kepala Desa)', 750000],
            ['2', 'H. BAHARUDDIN', 'Ketua Pengawas', 500000],
            ['3', 'SYAFRUDDIN', 'Anggota Pengawas', 400000],
            ['4', 'ZULKIFLI', 'Direktur BUMDesa', 1500000],
            ['5', 'SITI RAHMAH', 'Sekretaris BUMDesa', 1000000],
            ['6', 'ZULFIKAR', 'Bendahara BUMDesa', 1200000],
        ];

        $r = 8;
        foreach ($pengurus as $p) {
            $sheet->setCellValue('A' . $r, $p[0]);
            $sheet->setCellValue('B' . $r, $p[1]);
            $sheet->setCellValue('C' . $r, $p[2]);
            $sheet->setCellValue('D' . $r, $p[3]);
            $sheet->setCellValue('E' . $r, '...');
            $r++;
        }

        $sheet->setCellValue('C' . $r, 'Jumlah Keseluruhan');
        $sheet->setCellValue('D' . $r, "=SUM(D8:D" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "A{$r}:E{$r}");

        $ttdRow = $r + 3;
        $sheet->setCellValue('D' . $ttdRow, "Kuala Alam, " . date('t') . " {$bulanText} {$tahun}");
        $sheet->setCellValue('A' . ($ttdRow + 1), 'Mengetahui,');
        $sheet->setCellValue('D' . ($ttdRow + 1), 'Dibuat Oleh,');
        $sheet->setCellValue('A' . ($ttdRow + 2), 'DIREKTUR');
        $sheet->setCellValue('D' . ($ttdRow + 2), 'BENDAHARA');

        $sheet->setCellValue('A' . ($ttdRow + 6), 'ZULKIFLI');
        $sheet->setCellValue('D' . ($ttdRow + 6), 'ZULFIKAR');
        $sheet->getStyle('A' . ($ttdRow + 6) . ':D' . ($ttdRow + 6))->getFont()->setBold(true)->setUnderline(true);

        $this->formatCurrencyRange($sheet, "D8:D{$r}");
        $this->autoFitColumns($sheet, ['A', 'B', 'C', 'D', 'E']);
    }

    // =============================================================
    // SHEET 17: AMPRAH USP (Insentif Pengelola Unit USP)
    // =============================================================
    protected function buildAmprahUspSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('AMPRAH USP');

        $bulanText = strtoupper($this->namaBulan[$bulan] ?? "BULAN-{$bulan}");

        $sheet->setCellValue('A2', 'INSENTIF PENGURUS & PENGELOLA');
        $sheet->setCellValue('A3', 'UNIT SIMPAN PINJAM "BATHIN ALAM"');
        $sheet->setCellValue('A4', "PERIODE: {$bulanText} {$tahun}");
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);

        $headers = ['No', 'Nama Penerima', 'Jabatan', 'Jumlah (Rp)', 'Tanda Tangan'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '7', $h);
        }
        $this->applyHeaderStyle($sheet, 'A7:E7');

        $pengelolaUsp = [
            ['1', 'FATIMAH', 'Ketua Unit USP', 1200000],
            ['2', 'NURAINI', 'Kasir Unit USP', 1000000],
            ['3', 'HENDRI', 'Staf Administrasi Keuangan (SAK)', 900000],
            ['4', 'RUDI KURNIAWAN', 'Tata Usaha (TU)', 800000],
            ['5', 'SURYA', 'Staf Lapangan 1', 750000],
            ['6', 'DEDI', 'Staf Lapangan 2', 750000],
        ];

        $r = 8;
        foreach ($pengelolaUsp as $p) {
            $sheet->setCellValue('A' . $r, $p[0]);
            $sheet->setCellValue('B' . $r, $p[1]);
            $sheet->setCellValue('C' . $r, $p[2]);
            $sheet->setCellValue('D' . $r, $p[3]);
            $sheet->setCellValue('E' . $r, '...');
            $r++;
        }

        $sheet->setCellValue('C' . $r, 'Jumlah Keseluruhan');
        $sheet->setCellValue('D' . $r, "=SUM(D8:D" . ($r - 1) . ")");
        $this->applyTotalStyle($sheet, "A{$r}:E{$r}");

        $ttdRow = $r + 3;
        $sheet->setCellValue('D' . $ttdRow, "Kuala Alam, " . date('t') . " {$bulanText} {$tahun}");
        $sheet->setCellValue('A' . ($ttdRow + 1), 'Mengetahui,');
        $sheet->setCellValue('D' . ($ttdRow + 1), 'Dibuat Oleh,');
        $sheet->setCellValue('A' . ($ttdRow + 2), 'DIREKTUR');
        $sheet->setCellValue('D' . ($ttdRow + 2), 'BENDAHARA');

        $sheet->setCellValue('A' . ($ttdRow + 6), 'ZULKIFLI');
        $sheet->setCellValue('D' . ($ttdRow + 6), 'ZULFIKAR');
        $sheet->getStyle('A' . ($ttdRow + 6) . ':D' . ($ttdRow + 6))->getFont()->setBold(true)->setUnderline(true);

        $this->formatCurrencyRange($sheet, "D8:D{$r}");
        $this->autoFitColumns($sheet, ['A', 'B', 'C', 'D', 'E']);
    }

    // =============================================================
    // SHEET 18: Sheet1 (Petunjuk Regulasi & Alokasi Cadangan)
    // =============================================================
    protected function buildPetunjukSheet(Spreadsheet $spreadsheet, int $tahun, int $bulan): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Sheet1');

        $sheet->setCellValue('A1', 'PEDOMAN ALOKASI CADANGAN PENGEMBANGAN MODAL & TUNJANGAN KINERJA BUMDESA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        $rules = [
            ['a', 'Peruntukkan Cadangan Pengembangan Modal:'],
            ['', '1. THR (Tunjangan Hari Raya) bagi Pengurus dan Karyawan'],
            ['', '2. Bantuan Sosial Kemasyarakatan Desa'],
            ['', '3. Pengadaan Inventaris dan Sarana Prasarana Usaha'],
            ['', '4. Penambahan Modal Usaha Unit yang Berpotensi Produktif'],
            ['', '5. Penguatan Cadangan Risiko Kredit / Operasional'],
            ['', '6. Hal lain yang tidak disebutkan di atas diputuskan melalui Musyawarah Desa (Musdes) dan dilengkapi Berita Acara.'],
            ['b', 'Tunjangan Kinerja diatur peruntukkannya dan jika untuk dibagi maka disepakati formula pembagian proporsionalnya.'],
            ['c', 'Persentase pembagian insentif per unit dipengaruhi secara langsung oleh realisasi pendapatan yang diserahkan dari unit ke Kas BUMDesa.'],
            ['d', 'Persentase pembagian insentif anggota dalam setiap unit diatur secara mandiri dalam SOP masing-masing unit kerja.'],
            ['e', 'Seluruh pencatatan keuangan wajib diverifikasi dan disetujui oleh Bendahara Umum sebelum diintegrasikan ke Laporan Resmi Tahunan.'],
        ];

        $r = 3;
        foreach ($rules as $rule) {
            $sheet->setCellValue('A' . $r, $rule[0]);
            $sheet->setCellValue('B' . $r, $rule[1]);
            if (!empty($rule[0])) {
                $sheet->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
            }
            $r++;
        }

        $this->autoFitColumns($sheet, ['A', 'B']);
    }

    // =============================================================
    // STYLE HELPERS
    // =============================================================
    protected function applyHeaderStyle($sheet, string $cellRange): void
    {
        $sheet->getStyle($cellRange)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);
    }

    protected function applyTotalStyle($sheet, string $cellRange): void
    {
        $sheet->getStyle($cellRange)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
            ],
        ]);
    }

    protected function formatCurrencyRange($sheet, string $cellRange): void
    {
        $sheet->getStyle($cellRange)->getNumberFormat()->setFormatCode('#,##0');
    }

    protected function autoFitColumns($sheet, array $columns): void
    {
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
