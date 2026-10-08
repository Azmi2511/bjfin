<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Exception;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Transaksi::with(['unit', 'coa', 'user']);

        if ($user->role === 'bendahara_unit') {
            $query->where('id_unit', $user->id_unit);
        } elseif ($request->has('id_unit') || $request->has('unit_id')) {
            $query->where('id_unit', $request->id_unit ?? $request->unit_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } elseif ($request->has('status_validasi')) {
            $val = $request->status_validasi === 'approved' ? 'disetujui' : ($request->status_validasi === 'rejected' ? 'ditolak' : 'menunggu');
            $query->where('status', $val);
        }

        if ($request->has('jenis_transaksi') || $request->has('jenis')) {
            $query->where('jenis_transaksi', $request->jenis_transaksi ?? $request->jenis);
        }

        if ($request->has('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->has('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        $transactions = $query->orderBy('tanggal', 'desc')
            ->orderBy('id_transaksi', 'desc')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $unitId = $request->input('id_unit') ?? $request->input('unit_id');
        $kodeAkun = $request->input('kode_akun') ?? $request->input('coa_id');
        $jenis = $request->input('jenis_transaksi') ?? $request->input('jenis');
        $nominal = $request->input('nominal') ?? $request->input('jumlah');

        if (empty($kodeAkun)) {
            $unit = \App\Models\UnitUsaha::find($unitId);
            if ($unit) {
                if ($unit->kode_unit === 'WIFI') {
                    $kodeAkun = ($jenis === 'masuk') ? '41-WF' : '51-WF';
                } elseif ($unit->kode_unit === 'USP') {
                    $kodeAkun = ($jenis === 'masuk') ? '41-USP' : '51-USP';
                } elseif ($unit->kode_unit === 'KEBUN') {
                    $kodeAkun = ($jenis === 'masuk') ? '41-KBN' : '51-KBN';
                }
            }
        }

        $request->merge([
            'id_unit' => $unitId,
            'kode_akun' => $kodeAkun,
            'jenis_transaksi' => $jenis,
            'nominal' => $nominal,
        ]);

        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'kode_akun' => 'required|exists:chart_of_accounts,kode_akun',
            'tanggal' => 'required|date',
            'jenis_transaksi' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|gt:0',
            'keterangan' => 'required|string',
            'bukti_foto' => 'nullable|image|max:5120',
            'bukti_transaksi' => 'nullable|image|max:5120',
        ]);

        $data = [
            'id_unit' => $unitId,
            'kode_akun' => $kodeAkun,
            'tanggal' => $request->tanggal,
            'jenis_transaksi' => $jenis,
            'nominal' => (float) $nominal,
            'keterangan' => $request->keterangan,
        ];

        // Parse JSON or array data_tambahan / metadata
        $meta = $request->input('data_tambahan') ?? $request->input('metadata');
        if ($meta) {
            $data['data_tambahan'] = is_string($meta) ? json_decode($meta, true) : $meta;
        }

        // Handle physical receipt image upload
        $photoFile = $request->file('bukti_foto') ?? $request->file('bukti_transaksi');
        if ($photoFile) {
            $filename = 'bukti_' . time() . '_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();
            $path = $photoFile->storeAs('receipts', $filename, 'public');
            $data['bukti_transaksi'] = '/storage/' . $path;
        }

        try {
            $transaksi = $this->transactionService->createTransaction($user, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dicatat dan masuk ke antrean validasi Bendahara Umum.',
                'data' => $transaksi->load(['unit', 'coa']),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $transaksi = Transaksi::with(['unit', 'coa', 'user', 'journal.details.coa'])->findOrFail($id);

        if ($user->role === 'bendahara_unit' && $user->id_unit != $transaksi->id_unit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Otorisasi ditolak.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transaksi,
        ]);
    }

    public function cashBook(Request $request)
    {
        $user = $request->user();
        $unitId = ($user->role === 'bendahara_unit') 
            ? (int) $user->id_unit 
            : (int) ($request->query('id_unit') ?? $request->query('unit_id', 1));

        $tahun = (int) $request->query('tahun', date('Y'));
        $bulan = (int) $request->query('bulan', date('n'));

        $bku = $this->transactionService->getCashBook($unitId, $bulan, $tahun);

        return response()->json([
            'status' => 'success',
            'data' => $bku,
        ]);
    }

    public function calculateGuided(Request $request)
    {
        $result = $this->transactionService->calculateGuided($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
