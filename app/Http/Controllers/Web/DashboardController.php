<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use App\Models\LaporanKeuangan;
use App\Services\AccountingService;
use App\Services\KonverterKecamatan;
use App\Services\ValidationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected AccountingService $accounting;
    protected KonverterKecamatan $konverter;

    public function __construct(AccountingService $accounting, KonverterKecamatan $konverter)
    {
        $this->accounting = $accounting;
        $this->konverter = $konverter;
    }

    /**
     * Dashboard Utama Eksekutif BUMDesa: KPI Finansial, Tren, dan Ringkasan Unit Usaha.
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

        $currentYear = (int) date('Y');
        if (!in_array($currentYear, $availableYears)) {
            $availableYears[] = $currentYear;
            rsort($availableYears);
        }

        $tahun = (int) $request->query('tahun', $availableYears[0]);
        $bulanQuery = $request->query('bulan');
        
        // Default ke 'all' (konsolidasi tahunan) jika query bulan tidak diset atau eksplisit 'all'/'0'
        $bulan = ($bulanQuery === null || $bulanQuery === 'all' || $bulanQuery === '0' || $bulanQuery === '') 
            ? null 
            : (int) $bulanQuery;

        $selectedUnitId = $request->filled('id_unit') && $request->query('id_unit') !== 'all' 
            ? (int) $request->query('id_unit') 
            : null;

        $units = UnitUsaha::all();
        $income = $this->accounting->getIncomeStatement($tahun, $bulan, $selectedUnitId);
        $balance = $this->accounting->getBalanceSheet($tahun, $bulan, $selectedUnitId);

        // Ringkasan Keuangan Riil per Unit Usaha untuk Kartu Dashboard
        $unitPerformances = [];
        foreach ($units as $u) {
            $uTxQuery = Transaksi::where('id_unit', $u->id_unit)
                ->whereYear('tanggal', $tahun);
            if ($bulan !== null) {
                $uTxQuery->whereMonth('tanggal', $bulan);
            }
            $penerimaan = (clone $uTxQuery)->where('jenis_transaksi', 'masuk')->sum('nominal');
            $pengeluaran = (clone $uTxQuery)->where('jenis_transaksi', 'keluar')->sum('nominal');
            $count = (clone $uTxQuery)->count();

            $unitPerformances[$u->kode_unit] = [
                'id_unit' => $u->id_unit,
                'nama' => $u->nama_unit,
                'penerimaan' => (float) $penerimaan,
                'pengeluaran' => (float) $pengeluaran,
                'surplus' => (float) ($penerimaan - $pengeluaran),
                'total_transaksi' => $count,
                'is_selected' => $selectedUnitId === $u->id_unit,
            ];
        }

        // Hitung total transaksi dan frekuensi per bulan pada tahun & unit terpilih
        $monthCountsQuery = Transaksi::whereYear('tanggal', $tahun);
        if ($selectedUnitId !== null) {
            $monthCountsQuery->where('id_unit', $selectedUnitId);
        }
        $monthTxCounts = (clone $monthCountsQuery)
            ->selectRaw('MONTH(tanggal) as bln, count(*) as total')
            ->groupBy('bln')
            ->pluck('total', 'bln')
            ->toArray();

        $totalFilterTxQuery = Transaksi::whereYear('tanggal', $tahun);
        if ($bulan !== null) {
            $totalFilterTxQuery->whereMonth('tanggal', $bulan);
        }
        if ($selectedUnitId !== null) {
            $totalFilterTxQuery->where('id_unit', $selectedUnitId);
        }
        $totalTxCount = $totalFilterTxQuery->count();

        $pendingTransactions = Transaksi::with(['unit', 'coa', 'user'])
            ->where('status', 'menunggu')
            ->orderBy('tanggal', 'desc')
            ->take(6)
            ->get();

        $latestReportQuery = LaporanKeuangan::where('periode_tahun', $tahun);
        if ($bulan !== null) {
            $latestReportQuery->where('periode_bulan', $bulan);
        }
        $latestReport = $latestReportQuery->first();

        return view('dashboard', compact(
            'tahun', 
            'bulan', 
            'availableYears', 
            'units', 
            'selectedUnitId', 
            'income', 
            'balance', 
            'pendingTransactions', 
            'latestReport', 
            'unitPerformances',
            'monthTxCounts',
            'totalTxCount'
        ));
    }

    // =========================================================================
    // FORWARDING HELPERS (Untuk backward compatibility)
    // =========================================================================

    public function validationQueue(ValidationController $controller)
    {
        return $controller->index();
    }

    public function approveTransaction(Request $request, $id, ValidationService $service, ValidationController $controller)
    {
        return $controller->approve($request, $id, $service);
    }

    public function rejectTransaction(Request $request, $id, ValidationService $service, ValidationController $controller)
    {
        return $controller->reject($request, $id, $service);
    }

    public function reports(Request $request, ReportController $controller)
    {
        return $controller->index($request);
    }

    public function downloadExcel(Request $request, ReportController $controller)
    {
        return $controller->downloadExcel($request);
    }

    public function unitInput(Request $request, TransaksiController $controller)
    {
        return $controller->unitInput($request);
    }

    public function storeUnitTransaction(Request $request, TransaksiController $controller)
    {
        return $controller->storeUnitTransaction($request);
    }

    public function umumInput(Request $request, TransaksiController $controller)
    {
        return $controller->umumInput($request);
    }

    public function storeUmumTransaction(Request $request, TransaksiController $controller)
    {
        return $controller->storeUmumTransaction($request);
    }
}
