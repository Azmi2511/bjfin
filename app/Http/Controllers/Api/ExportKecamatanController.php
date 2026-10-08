<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KonverterKecamatan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Exception;

class ExportKecamatanController extends Controller
{
    protected KonverterKecamatan $konverter;

    public function __construct(KonverterKecamatan $konverter)
    {
        $this->konverter = $konverter;
    }

    public function export(Request $request): BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2099',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $tahun = (int)$request->tahun;
        $bulan = (int)$request->bulan;

        try {
            $filePath = $this->konverter->exportToFile($tahun, $bulan);
            $fileName = sprintf('Laporan_BUMDes_Kuala_Alam_%04d_%02d.xlsx', $tahun, $bulan);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengenerate file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
