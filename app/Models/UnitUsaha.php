<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitUsaha extends Model
{
    use HasFactory;

    protected $table = 'units';
    protected $primaryKey = 'id_unit';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'jenis_unit',
        'keterangan',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_unit', 'id_unit');
    }

    public function transactions()
    {
        return $this->hasMany(Transaksi::class, 'id_unit', 'id_unit');
    }

    public function masterEntries()
    {
        return $this->hasMany(MasterEntriUnit::class, 'id_unit', 'id_unit');
    }
}
