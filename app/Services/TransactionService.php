<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\LaporanKeuangan;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /**
     * Guided-Input calculation assistance conforming to authentic templates.
     */
    public function calculateGuided(array $data): array
    {
        $unitType = $data['unit_type'] ?? 'umum';

        if ($unitType === 'simpan_pinjam' || $unitType === 'USP') {
            $pokok = (float) ($data['pokok'] ?? 0.0);
            $bungaPersen = (float) ($data['bunga_persen'] ?? 2.0);
            $nominalBunga = $pokok * ($bungaPersen / 100.0);
            $total = $pokok + $nominalBunga;

            return [
                'nominal_otomatis' => round($total, 2),
                'rincian_kalkulasi' => [
                    'angsuran_pokok' => round($pokok, 2),
                    'persentase_bunga' => $bungaPersen,
                    'nominal_bunga' => round($nominalBunga, 2),
                    'total_setoran' => round($total, 2),
                    'nlpp_pokok' => '339P',
                    'nlpp_bunga' => '339B',
                    'pos_akun_pokok' => '13',
                    'pos_akun_bunga' => '41',
                ],
                'formula_deskripsi' => "Pokok (Rp " . number_format($pokok, 0, ',', '.') . ") + Bunga {$bungaPersen}% (Rp " . number_format($nominalBunga, 0, ',', '.') . ") = Rp " . number_format($total, 0, ',', '.'),
            ];
        }

        if ($unitType === 'jasa_wifi' || $unitType === 'WIFI') {
            $tarifCatalog = [
                '2 MBps' => ['total' => 100000.0, 'jasa' => 93000.0, 'safety' => 7000.0],
                '3 MBps' => ['total' => 175000.0, 'jasa' => 168000.0, 'safety' => 7000.0],
                '5 MBps' => ['total' => 250000.0, 'jasa' => 243000.0, 'safety' => 7000.0],
                '10 MBps' => ['total' => 350000.0, 'jasa' => 343000.0, 'safety' => 7000.0],
                '25 MBps' => ['total' => 1298000.0, 'jasa' => 1291000.0, 'safety' => 7000.0],
            ];

            $paketKey = $data['paket'] ?? ($data['paket_mbps'] ? "{$data['paket_mbps']} MBps" : '3 MBps');
            $selected = $tarifCatalog[$paketKey] ?? ['total' => 175000.0, 'jasa' => 168000.0, 'safety' => 7000.0];

            return [
                'nominal_otomatis' => $selected['total'],
                'rincian_kalkulasi' => [
                    'paket' => $paketKey,
                    'biaya_jasa' => $selected['jasa'],
                    'safety_peralatan' => $selected['safety'],
                    'total_tagihan' => $selected['total'],
                    'nlpp' => '105J',
                    'pos_akun_jasa' => '41',
                    'pos_akun_safety' => '22',
                ],
                'formula_deskripsi' => "Paket {$paketKey}: Jasa Rp " . number_format($selected['jasa'], 0, ',', '.') . " + Safety Rp " . number_format($selected['safety'], 0, ',', '.') . " = Rp " . number_format($selected['total'], 0, ',', '.'),
            ];
        }

        if ($unitType === 'perkebunan' || $unitType === 'KEBUN') {
            $subTipe = $data['sub_tipe'] ?? 'penjualan_nanas';

            if ($subTipe === 'penjualan_nanas') {
                $jmlBuah = (int) ($data['jumlah_buah'] ?? 0);
                $hargaPerBuah = (float) ($data['harga_per_buah'] ?? 3200.0);
                $total = $jmlBuah * $hargaPerBuah;

                return [
                    'nominal_otomatis' => round($total, 2),
                    'rincian_kalkulasi' => [
                        'sub_tipe' => 'penjualan_nanas',
                        'jumlah_buah' => $jmlBuah,
                        'harga_per_buah' => $hargaPerBuah,
                        'total' => round($total, 2),
                        'kode_akun' => '41-KBN',
                    ],
                    'formula_deskripsi' => "Panen Nanas {$jmlBuah} Buah x Rp " . number_format($hargaPerBuah, 0, ',', '.') . " = Rp " . number_format($total, 0, ',', '.'),
                ];
            } else {
                $biaya = (float) ($data['nominal_biaya'] ?? 0.0);
                $kategoriBeban = $data['kategori_beban'] ?? 'pupuk';

                return [
                    'nominal_otomatis' => round($biaya, 2),
                    'rincian_kalkulasi' => [
                        'sub_tipe' => 'beban_operasional',
                        'kategori_beban' => $kategoriBeban,
                        'nominal' => round($biaya, 2),
                        'kode_akun' => ($kategoriBeban === 'upah') ? '51-KBN' : '52-KBN',
                    ],
                    'formula_deskripsi' => "Pengeluaran Operasional Perkebunan: {$kategoriBeban} (Rp " . number_format($biaya, 0, ',', '.') . ")",
                ];
            }
        }

        return [
            'nominal_otomatis' => (float) ($data['nominal'] ?? 0.0),
            'rincian_kalkulasi' => [],
            'formula_deskripsi' => 'Transaksi BUMDesa standar',
        ];
    }

    /**
     * Create transaction with RBAC unit isolation and Period Lock enforcement.
     */
    public function createTransaction(User $user, array $data): Transaksi
    {
        // 1. Enforce Unit Isolation: Unit treasurers can only input for their assigned unit
        if ($user->isBendaharaUnit()) {
            if (!$user->id_unit || (int) $data['id_unit'] !== (int) $user->id_unit) {
                throw ValidationException::withMessages([
                    'id_unit' => ['Anda hanya diperbolehkan mencatat transaksi untuk unit usaha Anda sendiri.'],
                ]);
            }
        }

        // 2. Validate Unit & COA existence
        if (!UnitUsaha::where('id_unit', $data['id_unit'])->exists()) {
            throw ValidationException::withMessages([
                'id_unit' => ['Unit usaha tidak ditemukan.'],
            ]);
        }

        if (!ChartOfAccount::where('kode_akun', $data['kode_akun'])->exists()) {
            throw ValidationException::withMessages([
                'kode_akun' => ['Bagan akun (COA) tidak terdaftar dalam sistem.'],
            ]);
        }

        // 3. Period Lock Check
        $date = Carbon::parse($data['tanggal']);
        $isLocked = LaporanKeuangan::where('periode_bulan', $date->month)
            ->where('periode_tahun', $date->year)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            throw ValidationException::withMessages([
                'tanggal' => ["Periode {$date->month}/{$date->year} telah dikunci oleh Direktur BUMDes. Transaksi baru tidak dapat disimpan."],
            ]);
        }

        return Transaksi::create([
            'id_unit' => $data['id_unit'],
            'id_user' => $user->id,
            'tanggal' => $data['tanggal'],
            'jenis_transaksi' => $data['jenis_transaksi'],
            'kode_akun' => $data['kode_akun'],
            'nominal' => $data['nominal'],
            'keterangan' => $data['keterangan'],
            'bukti_transaksi' => $data['bukti_transaksi'] ?? null,
            'data_tambahan' => $data['data_tambahan'] ?? null,
            'status' => 'menunggu',
        ]);
    }

    /**
     * Generate daily cash book with running balances.
     */
    public function getCashBook(int $unitId, ?int $month = null, ?int $year = null, float $saldoAwal = 0.0): array
    {
        $query = Transaksi::with(['coa', 'unit'])
            ->where('id_unit', $unitId);

        if ($month) {
            $query->whereMonth('tanggal', $month);
        }
        if ($year) {
            $query->whereYear('tanggal', $year);
        }

        $transactions = $query->orderBy('tanggal', 'asc')->orderBy('id_transaksi', 'asc')->get();
        $unit = UnitUsaha::find($unitId);

        $running = $saldoAwal;
        $totalMasuk = 0.0;
        $totalKeluar = 0.0;
        $entries = [];

        foreach ($transactions as $t) {
            $nom = (float) $t->nominal;
            if ($t->jenis_transaksi === 'masuk') {
                $running += $nom;
                $totalMasuk += $nom;
                $m = $nom;
                $k = 0.0;
            } else {
                $running -= $nom;
                $totalKeluar += $nom;
                $m = 0.0;
                $k = $nom;
            }

            $entries[] = [
                'id_transaksi' => $t->id_transaksi,
                'tanggal' => $t->tanggal->format('Y-m-d'),
                'keterangan' => $t->keterangan,
                'kode_akun' => $t->kode_akun,
                'nama_akun' => $t->coa?->nama_akun ?? '',
                'masuk' => $m,
                'keluar' => $k,
                'saldo' => $running,
                'status' => $t->status,
                'bukti_transaksi' => $t->bukti_transaksi,
            ];
        }

        return [
            'id_unit' => $unitId,
            'nama_unit' => $unit?->nama_unit ?? 'Unit Usaha',
            'bulan' => $month,
            'tahun' => $year,
            'saldo_awal' => $saldoAwal,
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo_akhir' => $running,
            'entries' => $entries,
        ];
    }
}
