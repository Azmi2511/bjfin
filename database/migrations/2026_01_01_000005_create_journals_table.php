<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id('id_jurnal');
            $table->unsignedBigInteger('id_transaksi')->nullable()->unique();
            $table->date('tanggal');
            $table->text('keterangan');
            $table->timestamps();

            $table->foreign('id_transaksi')->references('id_transaksi')->on('transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
