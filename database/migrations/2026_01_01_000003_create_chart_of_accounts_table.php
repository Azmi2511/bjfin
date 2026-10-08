<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->string('kode_akun', 20)->primary();
            $table->unsignedBigInteger('id_unit')->nullable()->index(); // null = akun konsolidasi / umum
            $table->string('nama_akun', 150);
            $table->enum('tipe_saldo_normal', ['Debit', 'Kredit']);
            $table->string('kategori', 50); // Aset Lancar, Aset Tetap, Kewajiban, Ekuitas, Pendapatan, Beban
            $table->timestamps();

            $table->foreign('id_unit')->references('id_unit')->on('units')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
