<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_unit',
        'id_user',
        'tanggal',
        'jenis_transaksi',
        'kode_akun',
        'nominal',
        'keterangan',
        'bukti_transaksi',
        'data_tambahan',
        'status',
        'catatan_validasi',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'nominal' => 'float',
        'data_tambahan' => 'array',
    ];

    public function unit()
    {
        return $this->belongsTo(UnitUsaha::class, 'id_unit', 'id_unit');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_akun', 'kode_akun');
    }

    public function journal()
    {
        return $this->hasOne(Jurnal::class, 'id_transaksi', 'id_transaksi');
    }
}
