<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LaporanKeuangan;
use App\Models\Transaksi;
use App\Services\AccountingService;
use App\Services\ApprovalService;
use App\Services\KonverterKecamatan;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ReportController extends Controller
{
    protected AccountingService $accounting;
    protected KonverterKecamatan $konverter;

    public function __construct(AccountingService $accounting, KonverterKecamatan $konverter)
    {
        $this->accounting = $accounting;
        $this->konverter = $konverter;
    }

    /**
     * Tampilan Laporan Keuangan SAK ETAP Terkonsolidasi (Laba Rugi, Posisi Keuangan, Jurnal).
     */
    public function index(Request $request)
    {
        $availableYears = Transaksi::selectRaw('DISTINCT YEAR(tanggal) as thn')
            ->orderByDesc('thn')
            ->pluck('thn')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [2025, 2024, 2023];
        }

        $tahun = (int) $request->query('tahun', $availableYears[0]);
        $bulan = (int) $request->query('bulan', 1);

        $income = $this->accounting->getIncomeStatement($tahun, $bulan);
        $balance = $this->accounting->getBalanceSheet($tahun, $bulan);
        $journal = $this->accounting->getGeneralJournal($tahun, $bulan);

        $report = LaporanKeuangan::with('director')
            ->where('periode_tahun', $tahun)
            ->where('periode_bulan', $bulan)
            ->first();

        return view('reports', compact('tahun', 'bulan', 'availableYears', 'income', 'balance', 'journal', 'report'));
    }

    /**
     * Unduh Laporan Keuangan Multi-sheet Excel Standar Resmi Pemerintah (18 Sheet Lengkap).
     */
    public function downloadExcel(Request $request)
    {
        $availableYears = Transaksi::selectRaw('DISTINCT YEAR(tanggal) as thn')
            ->orderByDesc('thn')
            ->pluck('thn')
            ->toArray();

        $defaultYear = !empty($availableYears) ? $availableYears[0] : 2025;
        $tahun = (int) $request->query('tahun', $defaultYear);
        $bulanQuery = $request->query('bulan');
        $bulan = ($bulanQuery === 'all' || $bulanQuery === '0' || $bulanQuery === '') ? 0 : (int) ($bulanQuery ?? 1);

        $filePath = $this->konverter->exportToFile($tahun, $bulan);
        $fileName = ($bulan > 0)
            ? sprintf('Laporan_BUMDes_Kuala_Alam_%04d_%02d.xlsx', $tahun, $bulan)
            : sprintf('Laporan_BUMDes_Kuala_Alam_%04d_Tahunan.xlsx', $tahun);

        Alert::success('Laporan Siap Diunduh', 'File Excel 18 sheet resmi pemerintah berhasil digenerate.');

        return response()->download($filePath, $fileName);
    }

    /**
     * Ajukan Laporan Keuangan ke Direktur BUMDesa untuk Otorisasi.
     */
    public function submitReport(Request $request, int $id, ApprovalService $approvalService)
    {
        try {
            $report = $approvalService->submitReportForReview($id);
            Alert::success('Laporan Diajukan', 'Laporan keuangan periode ' . $report->periode_bulan . '/' . $report->periode_tahun . ' telah diajukan ke Direktur.');
            return redirect()->back()->with('success', 'Laporan berhasil diajukan untuk ditinjau Direktur.');
        } catch (\Throwable $e) {
            Alert::error('Gagal Mengajukan', $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Pengesahan & Penguncian Digital SHA-256 Laporan Keuangan oleh Direktur BUMDesa.
     */
    public function approveReport(Request $request, int $id, ApprovalService $approvalService)
    {
        try {
            $user = auth()->user();
            $report = $approvalService->approveReport($id, $user->id, $request->input('catatan', 'Disahkan oleh Direktur BUMDesa Kuala Alam.'));
            Alert::success('Laporan Disahkan Digital', 'Laporan keuangan telah dikunci permanen dengan tanda tangan digital SHA-256.');
            return redirect()->back()->with('success', 'Laporan berhasil disahkan secara digital!');
        } catch (\Throwable $e) {
            Alert::error('Gagal Mengesahkan', $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Verifikasi Keaslian Hash Digital SHA-256 Laporan Keuangan.
     */
    public function verifyReport(Request $request, int $id, ApprovalService $approvalService)
    {
        $result = $approvalService->verifyReportSignature($id);
        if ($result['is_valid']) {
            Alert::success('Integritas Valid', $result['message']);
        } else {
            Alert::warning('Verifikasi Digital', $result['message']);
        }
        return redirect()->back();
    }
}

