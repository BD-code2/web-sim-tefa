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
        Schema::create('sertifikat_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_siswa');
            $table->string('nisn');
            $table->string('kelas');
            $table->string('program_keahlian');
            $table->foreignId('kerja_sama_industri_id')->nullable()->constrained('kerja_sama_industris')->nullOnDelete();
            $table->date('tanggal_mulai_pkl');
            $table->date('tanggal_selesai_pkl');
            $table->string('nilai')->nullable();
            $table->string('predikat')->nullable();
            $table->string('no_sertifikat')->unique()->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->enum('status', ['draft', 'terverifikasi', 'diterbitkan'])->default('draft');
            $table->foreignId('diterbitkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diterbitkan_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikat_siswas');
    }
};
