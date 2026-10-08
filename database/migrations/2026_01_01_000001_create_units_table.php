<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id('id_unit');
            $table->string('kode_unit', 20)->unique();
            $table->string('nama_unit')->unique();
            $table->enum('jenis_unit', ['pusat', 'simpan_pinjam', 'jasa_wifi', 'perkebunan', 'lainnya']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
