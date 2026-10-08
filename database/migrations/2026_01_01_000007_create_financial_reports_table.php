<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->tinyInteger('periode_bulan');
            $table->integer('periode_tahun');
            $table->enum('status_laporan', ['draft', 'diajukan', 'disetujui', 'ditolak'])->default('draft');
            $table->boolean('is_locked')->default(false);
            $table->text('catatan_direktur')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('signature_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['periode_bulan', 'periode_tahun']);
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
