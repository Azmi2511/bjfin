<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_details', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('id_jurnal');
            $table->string('kode_akun', 20);
            $table->decimal('debit', 15, 2)->default(0.00);
            $table->decimal('kredit', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('id_jurnal')->references('id_jurnal')->on('journals')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_details');
    }
};
