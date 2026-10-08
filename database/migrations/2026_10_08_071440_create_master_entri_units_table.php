<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_entri_units', function (Blueprint $table) {
            $table->id('id_master');
            $table->unsignedBigInteger('id_unit');
            $table->string('jenis_entri', 50); // 'pelanggan_wifi', 'pemanfaat_usp', 'mutasi_panen', 'komoditas_kebun', 'lainnya'
            $table->string('kode_referensi', 50)->nullable(); // No SPPK, ID Pelanggan (WF-01), atau Kode Blok
            $table->string('nama', 150); // Nama pelanggan, nama pemanfaat, atau nama komoditas
            $table->string('kategori_sub', 100)->nullable(); // Dusun, Jenis Usaha Warga, Lokasi Kebun
            $table->decimal('nominal_standar', 15, 2)->default(0); // Paket harga, pinjaman pokok, atau harga per satuan
            $table->decimal('nominal_tambahan', 15, 2)->default(0); // Safety alat, bunga, dll
            $table->json('metadata')->nullable(); // detail tambahan fleksibel (satuan, kontak, dll)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_unit')->references('id_unit')->on('units')->onDelete('cascade');
            $table->index(['id_unit', 'jenis_entri']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_entri_units');
    }
};
