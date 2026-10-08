<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterEntriUnit extends Model
{
    protected $table = 'master_entri_units';
    protected $primaryKey = 'id_master';

    protected $fillable = [
        'id_unit',
        'jenis_entri',
        'kode_referensi',
        'nama',
        'kategori_sub',
        'nominal_standar',
        'nominal_tambahan',
        'metadata',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_standar' => 'float',
            'nominal_tambahan' => 'float',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(UnitUsaha::class, 'id_unit', 'id_unit');
    }
}
