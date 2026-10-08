<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKeuangan extends Model
{
    use HasFactory;

    protected $table = 'financial_reports';
    protected $primaryKey = 'id_laporan';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'status_laporan',
        'is_locked',
        'catatan_direktur',
        'approved_at',
        'approved_by',
        'signature_hash',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'approved_at' => 'datetime',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
    ];

    public function director()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
