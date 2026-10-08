<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKeuangan;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Exception;

class ApprovalController extends Controller
{
    protected ApprovalService $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function draft(Request $request)
    {
        $tahun = (int) $request->query('tahun', date('Y'));
        $bulan = (int) $request->query('bulan', date('n'));
        $unitId = $request->query('id_unit') ?? $request->query('unit_id');

        $report = $this->approvalService->prepareReportDraft($tahun, $bulan, $unitId ? (int) $unitId : null);

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ]);
    }

    public function submit(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, ['bendahara_umum', 'direktur'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya Bendahara Umum yang dapat mengajukan laporan ke Direktur.',
            ], 403);
        }

        try {
            $report = $this->approvalService->submitReportForReview((int) $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil diajukan kepada Direktur untuk pengesahan.',
                'data' => $report,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function approve(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'direktur') {
            return response()->json([
                'status' => 'error',
                'message' => 'Otorisasi ditolak: Hanya Direktur BUMDes yang berwenang menandatangani dan mengunci laporan keuangan.',
            ], 403);
        }

        try {
            $report = $this->approvalService->approveReport((int) $id, $user->id, $request->input('catatan'));

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan keuangan berhasil disahkan secara digital (SHA-256 Valid). Periode transaksi resmi dikunci (Period Lock).',
                'data' => $report->load('director'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function verify($id)
    {
        $result = $this->approvalService->verifyReportSignature((int) $id);

        return response()->json([
            'status' => $result['is_valid'] ? 'success' : 'warning',
            'data' => $result,
        ]);
    }
}
