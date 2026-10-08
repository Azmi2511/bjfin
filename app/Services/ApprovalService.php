<?php

namespace App\Services;

use App\Models\LaporanKeuangan;
use App\Models\User;
use Exception;

class ApprovalService
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Generate or fetch draft report for approval.
     */
    public function prepareReportDraft(int $tahun, int $bulan): LaporanKeuangan
    {
        $report = LaporanKeuangan::firstOrCreate([
            'periode_tahun' => $tahun,
            'periode_bulan' => $bulan,
        ], [
            'status_laporan' => 'draft',
            'is_locked' => false,
        ]);

        return $report;
    }

    /**
     * Submit report to Director for review.
     */
    public function submitReportForReview(int $reportId): LaporanKeuangan
    {
        $report = LaporanKeuangan::findOrFail($reportId);

        if ($report->is_locked) {
            throw new Exception("Laporan periode ini sudah terkunci (locked).");
        }

        $report->update([
            'status_laporan' => 'diajukan',
        ]);

        return $report;
    }

    /**
     * Approve report by Direktur with SHA-256 digital signature and period lock.
     */
    public function approveReport(int $reportId, int $direkturUserId, ?string $catatan = null): LaporanKeuangan
    {
        $report = LaporanKeuangan::findOrFail($reportId);

        if ($report->is_locked) {
            throw new Exception("Laporan periode ini sudah disetujui dan terkunci permanen.");
        }

        $direktur = User::findOrFail($direkturUserId);
        if ($direktur->role !== 'direktur') {
            throw new Exception("Hanya Direktur BUMDes yang memiliki otoritas menyetujui laporan keuangan.");
        }

        $income = $this->accountingService->getIncomeStatement($report->periode_tahun, $report->periode_bulan);
        $approvedAt = now();
        $salt = config('app.key', 'BUMDES_KUALA_ALAM_SECRET_2026');

        // Deterministic SHA-256 cryptographic hash
        $signatureRaw = implode('|', [
            $report->id_laporan,
            $report->periode_tahun,
            $report->periode_bulan,
            'KONSOLIDASI',
            number_format($income['total_pendapatan'], 2, '.', ''),
            number_format($income['total_beban'], 2, '.', ''),
            number_format($income['laba_bersih'], 2, '.', ''),
            $direkturUserId,
            $approvedAt->toIso8601String(),
            $salt
        ]);

        $signatureHash = hash('sha256', $signatureRaw);

        $report->update([
            'status_laporan' => 'disetujui',
            'approved_by' => $direkturUserId,
            'approved_at' => $approvedAt,
            'catatan_direktur' => $catatan ?: 'Disahkan oleh Direktur BUMDesa Kuala Alam.',
            'signature_hash' => $signatureHash,
            'is_locked' => true,
        ]);

        return $report;
    }

    /**
     * Verify cryptographic signature of a financial report.
     */
    public function verifyReportSignature(int $reportId): array
    {
        $report = LaporanKeuangan::with('director')->findOrFail($reportId);

        if (!$report->is_locked || !$report->signature_hash || !$report->approved_at) {
            return [
                'is_valid' => false,
                'message' => 'Laporan belum disahkan secara digital atau belum berstatus terkunci.',
            ];
        }

        $income = $this->accountingService->getIncomeStatement($report->periode_tahun, $report->periode_bulan);
        $salt = config('app.key', 'BUMDES_KUALA_ALAM_SECRET_2026');

        $expectedRaw = implode('|', [
            $report->id_laporan,
            $report->periode_tahun,
            $report->periode_bulan,
            'KONSOLIDASI',
            number_format($income['total_pendapatan'], 2, '.', ''),
            number_format($income['total_beban'], 2, '.', ''),
            number_format($income['laba_bersih'], 2, '.', ''),
            $report->approved_by,
            $report->approved_at->toIso8601String(),
            $salt
        ]);

        $calculatedHash = hash('sha256', $expectedRaw);
        $isValid = hash_equals($report->signature_hash, $calculatedHash);

        return [
            'is_valid' => $isValid,
            'signature_hash' => $report->signature_hash,
            'approved_by' => $report->director ? $report->director->nama : 'Direktur BUMDes',
            'approved_at' => $report->approved_at->format('Y-m-d H:i:s'),
            'message' => $isValid 
                ? 'Integritas laporan terverifikasi asli (SHA-256 Valid).' 
                : 'Peringatan: Hash digital tidak cocok! Integritas data mungkin telah dimodifikasi.',
        ];
    }
}
