<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function generalJournal(Request $request)
    {
        $tahun = (int)$request->query('tahun', date('Y'));
        $bulan = $request->has('bulan') ? (int)$request->query('bulan') : null;
        $unitId = $request->has('unit_id') ? (int)$request->query('unit_id') : null;

        $journals = $this->accountingService->getGeneralJournal($tahun, $bulan, $unitId);

        return response()->json([
            'status' => 'success',
            'data' => $journals,
        ]);
    }

    public function ledger(Request $request)
    {
        $tahun = (int)$request->query('tahun', date('Y'));
        $bulan = $request->has('bulan') ? (int)$request->query('bulan') : null;
        $unitId = $request->has('unit_id') ? (int)$request->query('unit_id') : null;

        $ledger = $this->accountingService->getGeneralLedger($tahun, $bulan, $unitId);

        return response()->json([
            'status' => 'success',
            'data' => $ledger,
        ]);
    }

    public function trialBalance(Request $request)
    {
        $tahun = (int)$request->query('tahun', date('Y'));
        $bulan = $request->has('bulan') ? (int)$request->query('bulan') : null;
        $unitId = $request->has('unit_id') ? (int)$request->query('unit_id') : null;

        $tb = $this->accountingService->getTrialBalance($tahun, $bulan, $unitId);

        return response()->json([
            'status' => 'success',
            'data' => $tb,
        ]);
    }

    public function incomeStatement(Request $request)
    {
        $tahun = (int)$request->query('tahun', date('Y'));
        $bulan = $request->has('bulan') ? (int)$request->query('bulan') : null;
        $unitId = $request->has('unit_id') ? (int)$request->query('unit_id') : null;

        $income = $this->accountingService->getIncomeStatement($tahun, $bulan, $unitId);

        return response()->json([
            'status' => 'success',
            'data' => $income,
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $tahun = (int)$request->query('tahun', date('Y'));
        $bulan = $request->has('bulan') ? (int)$request->query('bulan') : null;
        $unitId = $request->has('unit_id') ? (int)$request->query('unit_id') : null;

        $balance = $this->accountingService->getBalanceSheet($tahun, $bulan, $unitId);

        return response()->json([
            'status' => 'success',
            'data' => $balance,
        ]);
    }
}
