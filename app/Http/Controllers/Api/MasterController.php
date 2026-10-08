<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function units()
    {
        $units = UnitUsaha::all();
        return response()->json([
            'status' => 'success',
            'data' => $units,
        ]);
    }

    public function accounts(Request $request)
    {
        $query = ChartOfAccount::query();

        if ($request->has('kategori')) {
            $query->where('kategori', 'like', '%' . $request->kategori . '%');
        }

        $accounts = $query->orderBy('kode_akun', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $accounts,
        ]);
    }

    public function summaryDashboard(Request $request)
    {
        $user = $request->user();
        $unitId = ($user->role === 'bendahara_unit') ? $user->id_unit : ($request->query('id_unit') ?? $request->query('unit_id'));

        $tahun = (int) ($request->query('tahun', date('Y')));
        $bulan = (int) ($request->query('bulan', date('n')));

        $txQuery = Transaksi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan);

        if ($unitId) {
            $txQuery->where('id_unit', $unitId);
        }

        $approvedTxs = (clone $txQuery)->where('status', 'disetujui')->get();
        $totalMasuk = $approvedTxs->where('jenis_transaksi', 'masuk')->sum('nominal');
        $totalKeluar = $approvedTxs->where('jenis_transaksi', 'keluar')->sum('nominal');
        $surplusDefisit = $totalMasuk - $totalKeluar;

        $pendingCount = (clone $txQuery)->where('status', 'menunggu')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'periode' => ['tahun' => $tahun, 'bulan' => $bulan],
                'id_unit' => $unitId,
                'total_penerimaan' => (float) $totalMasuk,
                'total_pengeluaran' => (float) $totalKeluar,
                'surplus_defisit' => (float) $surplusDefisit,
                'total_transaksi_approved' => $approvedTxs->count(),
                'pending_validasi_count' => $pendingCount,
            ],
        ]);
    }
}
