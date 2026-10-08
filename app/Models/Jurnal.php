<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;

    protected $table = 'journals';
    protected $primaryKey = 'id_jurnal';

    protected $fillable = [
        'id_transaksi',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'id_jurnal', 'id_jurnal');
    }
}
