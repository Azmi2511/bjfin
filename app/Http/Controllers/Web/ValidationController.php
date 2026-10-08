<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ValidationController extends Controller
{
    /**
     * Tampilan antrean transaksi menunggu validasi Bendahara Umum.
     */
    public function index()
    {
        $pending = Transaksi::with(['unit', 'coa', 'user'])
            ->where('status', 'menunggu')
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('validation', compact('pending'));
    }

    /**
     * Setujui transaksi unit dari antrean validasi (langsung membukukan jurnal umum).
     */
    public function approve(Request $request, int|string $id, ValidationService $validationService)
    {
        try {
            $transaksi = $validationService->validateTransaction(
                (int) $id,
                'disetujui',
                $request->input('catatan', 'Disetujui oleh Bendahara Umum melalui Web Portal.')
            );

            Alert::success('Transaksi Disetujui', 'Transaksi #' . $id . ' berhasil divalidasi dan jurnal berpasangan otomatis dibukukan.');

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi berhasil divalidasi dan jurnal umum otomatis dibukukan seimbang (double-entry).',
                    'data' => [
                        'transaksi' => $transaksi,
                        'jurnal' => $transaksi->journal?->load('details.coa'),
                    ],
                ]);
            }

            return redirect()->route('validation')->with('success', 'Transaksi berhasil divalidasi dan jurnal otomatis dibukukan!');
        } catch (\Throwable $e) {
            Alert::error('Gagal Memvalidasi', $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('validation')->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak transaksi unit dengan menyertakan catatan revisi.
     */
    public function reject(Request $request, int|string $id, ValidationService $validationService)
    {
        $request->validate([
            'alasan' => 'required|string|min:3',
        ]);

        try {
            $transaksi = $validationService->validateTransaction(
                (int) $id,
                'ditolak',
                $request->alasan
            );

            Alert::info('Transaksi Ditolak', 'Transaksi #' . $id . ' telah ditolak dan dikembalikan ke Bendahara Unit dengan catatan revisi.');

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi ditolak dan dikembalikan ke Bendahara Unit dengan catatan revisi.',
                    'data' => $transaksi,
                ]);
            }

            return redirect()->route('validation')->with('success', 'Transaksi berhasil ditolak.');
        } catch (\Throwable $e) {
            Alert::error('Gagal Menolak', $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('validation')->with('error', $e->getMessage());
        }
    }
}
