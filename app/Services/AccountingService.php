<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\ChartOfAccount;
use App\Models\UnitUsaha;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Generate General Journal (Jurnal Umum) for a specific period and unit.
     */
    public function getGeneralJournal(int $tahun, ?int $bulan = null, ?int $unitId = null): Collection
    {
        $query = Jurnal::with(['transaction.unit', 'details.coa'])
            ->whereYear('tanggal', $tahun);

        if ($bulan !== null) {
            $query->whereMonth('tanggal', $bulan);
        }

        if ($unitId !== null) {
            $query->whereHas('transaction', function ($q) use ($unitId) {
                $q->where('id_unit', $unitId);
            });
        }

        return $query->orderBy('tanggal', 'asc')
            ->orderBy('id_jurnal', 'asc')
            ->get();
    }

    /**
     * Generate General Ledger (Buku Besar) per Account.
     */
    public function getGeneralLedger(int $tahun, ?int $bulan = null, ?int $unitId = null): array
    {
        $accounts = ChartOfAccount::orderBy('kode_akun', 'asc')->get();
        $ledger = [];

        foreach ($accounts as $account) {
            $query = JurnalDetail::with('journal')
                ->where('kode_akun', $account->kode_akun)
                ->whereHas('journal', function ($q) use ($tahun, $bulan, $unitId) {
                    $q->whereYear('tanggal', $tahun);
                    if ($bulan !== null) {
                        $q->whereMonth('tanggal', $bulan);
                    }
                    if ($unitId !== null) {
                        $q->whereHas('transaction', function ($tq) use ($unitId) {
                            $tq->where('id_unit', $unitId);
                        });
                    }
                });

            $entries = $query->get()->sortBy(function ($item) {
                return $item->journal?->tanggal;
            });

            if ($entries->isNotEmpty()) {
                $runningBalance = 0;
                $formattedEntries = [];
                $isDebitNormal = ($account->tipe_saldo_normal === 'Debit');

                foreach ($entries as $entry) {
                    $d = (float) $entry->debit;
                    $k = (float) $entry->kredit;

                    if ($isDebitNormal) {
                        $runningBalance += ($d - $k);
                    } else {
                        $runningBalance += ($k - $d);
                    }

                    $formattedEntries[] = [
                        'tanggal' => $entry->journal?->tanggal?->format('Y-m-d') ?? '',
                        'keterangan' => $entry->journal?->keterangan ?? '',
                        'debit' => $d,
                        'kredit' => $k,
                        'saldo_berjalan' => (float) $runningBalance,
                    ];
                }

                $ledger[] = [
                    'kode_akun' => $account->kode_akun,
                    'nama_akun' => $account->nama_akun,
                    'kategori' => $account->kategori,
                    'saldo_normal' => $account->tipe_saldo_normal,
                    'total_debit' => (float) $entries->sum('debit'),
                    'total_kredit' => (float) $entries->sum('kredit'),
                    'saldo_akhir' => (float) $runningBalance,
                    'mutasi' => $formattedEntries,
                ];
            }
        }

        return $ledger;
    }

    /**
     * Generate Trial Balance (Neraca Saldo).
     */
    public function getTrialBalance(int $tahun, ?int $bulan = null, ?int $unitId = null): array
    {
        $accounts = ChartOfAccount::orderBy('kode_akun', 'asc')->get();
        $trialBalance = [];
        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($accounts as $account) {
            $query = JurnalDetail::where('kode_akun', $account->kode_akun)
                ->whereHas('journal', function ($q) use ($tahun, $bulan, $unitId) {
                    $q->whereYear('tanggal', $tahun);
                    if ($bulan !== null) {
                        $q->whereMonth('tanggal', $bulan);
                    }
                    if ($unitId !== null) {
                        $q->whereHas('transaction', function ($tq) use ($unitId) {
                            $tq->where('id_unit', $unitId);
                        });
                    }
                });

            $sumDebit = (float) $query->sum('debit');
            $sumKredit = (float) $query->sum('kredit');

            if ($sumDebit > 0 || $sumKredit > 0) {
                $netDebit = 0;
                $netKredit = 0;

                if ($account->tipe_saldo_normal === 'Debit') {
                    $net = $sumDebit - $sumKredit;
                    if ($net >= 0) {
                        $netDebit = $net;
                    } else {
                        $netKredit = abs($net);
                    }
                } else {
                    $net = $sumKredit - $sumDebit;
                    if ($net >= 0) {
                        $netKredit = $net;
                    } else {
                        $netDebit = abs($net);
                    }
                }

                $trialBalance[] = [
                    'kode_akun' => $account->kode_akun,
                    'nama_akun' => $account->nama_akun,
                    'kategori' => $account->kategori,
                    'debit' => $netDebit,
                    'kredit' => $netKredit,
                ];

                $totalDebit += $netDebit;
                $totalKredit += $netKredit;
            }
        }

        return [
            'accounts' => $trialBalance,
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            'is_balanced' => abs($totalDebit - $totalKredit) < 0.01,
        ];
    }

    /**
     * Generate SAK ETAP Income Statement (Laporan Laba Rugi).
     */
    public function getIncomeStatement(int $tahun, ?int $bulan = null, ?int $unitId = null): array
    {
        $pendapatanAccounts = ChartOfAccount::where('kategori', 'Pendapatan')->get();
        $bebanAccounts = ChartOfAccount::where('kategori', 'Beban')->get();

        $pendapatanList = [];
        $totalPendapatan = 0;

        foreach ($pendapatanAccounts as $acc) {
            $q = JurnalDetail::where('kode_akun', $acc->kode_akun)
                ->whereHas('journal', function ($j) use ($tahun, $bulan, $unitId) {
                    $j->whereYear('tanggal', $tahun);
                    if ($bulan !== null) $j->whereMonth('tanggal', $bulan);
                    if ($unitId !== null) {
                        $j->whereHas('transaction', function ($t) use ($unitId) {
                            $t->where('id_unit', $unitId);
                        });
                    }
                });

            $sumKredit = (float) $q->sum('kredit');
            $sumDebit = (float) $q->sum('debit');
            $net = $sumKredit - $sumDebit;

            if ($net > 0) {
                $pendapatanList[] = [
                    'kode_akun' => $acc->kode_akun,
                    'nama_akun' => $acc->nama_akun,
                    'jumlah' => $net,
                ];
                $totalPendapatan += $net;
            }
        }

        $bebanList = [];
        $totalBeban = 0;

        foreach ($bebanAccounts as $acc) {
            $q = JurnalDetail::where('kode_akun', $acc->kode_akun)
                ->whereHas('journal', function ($j) use ($tahun, $bulan, $unitId) {
                    $j->whereYear('tanggal', $tahun);
                    if ($bulan !== null) $j->whereMonth('tanggal', $bulan);
                    if ($unitId !== null) {
                        $j->whereHas('transaction', function ($t) use ($unitId) {
                            $t->where('id_unit', $unitId);
                        });
                    }
                });

            $sumDebit = (float) $q->sum('debit');
            $sumKredit = (float) $q->sum('kredit');
            $net = $sumDebit - $sumKredit;

            if ($net > 0) {
                $bebanList[] = [
                    'kode_akun' => $acc->kode_akun,
                    'nama_akun' => $acc->nama_akun,
                    'jumlah' => $net,
                ];
                $totalBeban += $net;
            }
        }

        $labaBersih = $totalPendapatan - $totalBeban;

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'id_unit' => $unitId,
            'pendapatan' => $pendapatanList,
            'total_pendapatan' => $totalPendapatan,
            'beban' => $bebanList,
            'total_beban' => $totalBeban,
            'laba_bersih' => $labaBersih,
        ];
    }

    /**
     * Generate SAK ETAP Balance Sheet (Neraca).
     */
    public function getBalanceSheet(int $tahun, ?int $bulan = null, ?int $unitId = null): array
    {
        $asetAccounts = ChartOfAccount::where('kategori', 'like', 'Aset%')->orderBy('kode_akun')->get();
        $kewajibanAccounts = ChartOfAccount::where('kategori', 'Kewajiban')->orderBy('kode_akun')->get();
        $ekuitasAccounts = ChartOfAccount::where('kategori', 'Ekuitas')->orderBy('kode_akun')->get();

        $calcBalance = function ($accounts) use ($tahun, $bulan, $unitId) {
            $list = [];
            $total = 0;
            foreach ($accounts as $acc) {
                $d = (float) JurnalDetail::where('kode_akun', $acc->kode_akun)
                    ->whereHas('journal', function ($q) use ($tahun, $bulan, $unitId) {
                        $q->whereYear('tanggal', $tahun);
                        if ($bulan !== null) $q->whereMonth('tanggal', $bulan);
                        if ($unitId !== null) {
                            $q->whereHas('transaction', function ($t) use ($unitId) {
                                $t->where('id_unit', $unitId);
                            });
                        }
                    })->sum('debit');

                $k = (float) JurnalDetail::where('kode_akun', $acc->kode_akun)
                    ->whereHas('journal', function ($q) use ($tahun, $bulan, $unitId) {
                        $q->whereYear('tanggal', $tahun);
                        if ($bulan !== null) $q->whereMonth('tanggal', $bulan);
                        if ($unitId !== null) {
                            $q->whereHas('transaction', function ($t) use ($unitId) {
                                $t->where('id_unit', $unitId);
                            });
                        }
                    })->sum('kredit');

                $saldo = ($acc->tipe_saldo_normal === 'Debit') ? ($d - $k) : ($k - $d);
                if ($saldo != 0) {
                    $list[] = [
                        'kode_akun' => $acc->kode_akun,
                        'nama_akun' => $acc->nama_akun,
                        'saldo' => $saldo,
                    ];
                    $total += $saldo;
                }
            }
            return [$list, $total];
        };

        [$asetList, $totalAset] = $calcBalance($asetAccounts);
        [$kewajibanList, $totalKewajiban] = $calcBalance($kewajibanAccounts);
        [$ekuitasList, $totalEkuitasAwal] = $calcBalance($ekuitasAccounts);

        $incomeData = $this->getIncomeStatement($tahun, $bulan, $unitId);
        $labaPeriodeBerjalan = $incomeData['laba_bersih'];

        $totalEkuitas = $totalEkuitasAwal + $labaPeriodeBerjalan;
        $totalPassiva = $totalKewajiban + $totalEkuitas;

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'id_unit' => $unitId,
            'aset' => $asetList,
            'total_aset' => $totalAset,
            'kewajiban' => $kewajibanList,
            'total_kewajiban' => $totalKewajiban,
            'ekuitas' => $ekuitasList,
            'laba_periode_berjalan' => $labaPeriodeBerjalan,
            'total_ekuitas' => $totalEkuitas,
            'total_passiva' => $totalPassiva,
            'is_balanced' => abs($totalAset - $totalPassiva) < 0.01,
        ];
    }
}
