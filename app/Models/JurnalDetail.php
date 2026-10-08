<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    use HasFactory;

    protected $table = 'journal_details';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_jurnal',
        'kode_akun',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'debit' => 'float',
        'kredit' => 'float',
    ];

    public function journal()
    {
        return $this->belongsTo(Jurnal::class, 'id_jurnal', 'id_jurnal');
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_akun', 'kode_akun');
    }
}
