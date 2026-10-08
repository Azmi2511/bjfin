<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    /**
     * Audit transaction and auto-post double-entry journal if approved.
     */
    public function validateTransaction(int $transactionId, string $decision, ?string $notes = null): Transaksi
    {
        $tx = Transaksi::findOrFail($transactionId);

        if ($decision === 'ditolak' && empty($notes)) {
            throw ValidationException::withMessages([
                'catatan_validasi' => ['Wajib menyertakan catatan/alasan penolakan revisi.'],
            ]);
        }

        return DB::transaction(function () use ($tx, $decision, $notes) {
            if ($decision === 'disetujui') {
                $tx->update([
                    'status' => 'disetujui',
                    'catatan_validasi' => $notes ?: 'Disetujui Bendahara Umum',
                ]);

                // Delete old journal if re-evaluated
                if ($tx->journal) {
                    $tx->journal->details()->delete();
                    $tx->journal()->delete();
                }

                // Create Journal
                $journal = Jurnal::create([
                    'id_transaksi' => $tx->id_transaksi,
                    'tanggal' => $tx->tanggal,
                    'keterangan' => $tx->keterangan,
                ]);

                $nom = (float) $tx->nominal;
                $coa = $tx->kode_akun;

                $unit = $tx->unit;
                $kasAkun = match ($unit?->kode_unit) {
                    'WIFI' => '11-WF',
                    'USP' => '11-USP',
                    'KEBUN' => '11-KBN',
                    default => '111',
                };

                if ($tx->jenis_transaksi === 'masuk') {
                    // Kas Masuk: Debit Kas, Kredit Akun Transaksi
                    JurnalDetail::create([
                        'id_jurnal' => $journal->id_jurnal,
                        'kode_akun' => $kasAkun,
                        'debit' => $nom,
                        'kredit' => 0.00,
                    ]);
                    JurnalDetail::create([
                        'id_jurnal' => $journal->id_jurnal,
                        'kode_akun' => $coa,
                        'debit' => 0.00,
                        'kredit' => $nom,
                    ]);
                } else {
                    // Kas Keluar: Debit Akun Beban/Aset, Kredit Kas
                    JurnalDetail::create([
                        'id_jurnal' => $journal->id_jurnal,
                        'kode_akun' => $coa,
                        'debit' => $nom,
                        'kredit' => 0.00,
                    ]);
                    JurnalDetail::create([
                        'id_jurnal' => $journal->id_jurnal,
                        'kode_akun' => $kasAkun,
                        'debit' => 0.00,
                        'kredit' => $nom,
                    ]);
                }
            } else {
                $tx->update([
                    'status' => 'ditolak',
                    'catatan_validasi' => $notes,
                ]);

                if ($tx->journal) {
                    $tx->journal->details()->delete();
                    $tx->journal()->delete();
                }
            }

            return $tx->fresh(['coa', 'unit', 'user']);
        });
    }
}
