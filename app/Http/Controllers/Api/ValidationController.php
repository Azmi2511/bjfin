<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Exception;

class ValidationController extends Controller
{
    protected ValidationService $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function pending(Request $request)
    {
        $query = Transaksi::with(['unit', 'coa', 'user'])
            ->where('status', 'menunggu');

        if ($request->has('id_unit') || $request->has('unit_id')) {
            $query->where('id_unit', $request->id_unit ?? $request->unit_id);
        }

        $pending = $query->orderBy('tanggal', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $pending,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, ['bendahara_umum', 'direktur'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Otorisasi ditolak: Hanya Bendahara Umum atau Direktur yang dapat memvalidasi transaksi.',
            ], 403);
        }

        try {
            $transaksi = $this->validationService->validateTransaction(
                (int) $id,
                'disetujui',
                $request->input('catatan', 'Disetujui oleh Bendahara Umum.')
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil divalidasi dan jurnal umum otomatis dibukukan seimbang (double-entry).',
                'data' => [
                    'transaksi' => $transaksi,
                    'jurnal' => $transaksi->journal?->load('details.coa'),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|min:3',
        ]);

        $user = $request->user();

        if (!in_array($user->role, ['bendahara_umum', 'direktur'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Otorisasi ditolak.',
            ], 403);
        }

        try {
            $transaksi = $this->validationService->validateTransaction(
                (int) $id,
                'ditolak',
                $request->alasan
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi ditolak dan dikembalikan ke Bendahara Unit dengan catatan revisi.',
                'data' => $transaksi,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
