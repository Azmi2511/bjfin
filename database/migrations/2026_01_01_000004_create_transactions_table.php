<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->unsignedBigInteger('id_unit');
            $table->unsignedBigInteger('id_user');
            $table->date('tanggal');
            $table->enum('jenis_transaksi', ['masuk', 'keluar']);
            $table->string('kode_akun', 20);
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan');
            $table->string('bukti_transaksi')->nullable();
            $table->json('data_tambahan')->nullable(); // auto calculation info, e.g. bunga, paket, nasabah
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_validasi')->nullable();
            $table->timestamps();

            $table->foreign('id_unit')->references('id_unit')->on('units')->onDelete('restrict');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('kode_akun')->references('kode_akun')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
