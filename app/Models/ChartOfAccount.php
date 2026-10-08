<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';
    protected $primaryKey = 'kode_akun';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_akun',
        'id_unit',
        'nama_akun',
        'tipe_saldo_normal',
        'kategori',
    ];

    public function unit()
    {
        return $this->belongsTo(UnitUsaha::class, 'id_unit', 'id_unit');
    }

    public function transactions()
    {
        return $this->hasMany(Transaksi::class, 'kode_akun', 'kode_akun');
    }

    public function journalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'kode_akun', 'kode_akun');
    }
}
