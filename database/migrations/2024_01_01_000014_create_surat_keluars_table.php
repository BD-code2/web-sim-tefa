<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Surat keluar kini fokus untuk Surat Undangan / Surat Umum.
     * Surat Keputusan (SK) dan Surat Perintah Tugas (SPT) pindah ke tabel surat_dinas.
     */
    public function up(): void
    {
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat')->unique()->nullable();
            $table->string('tujuan');
            $table->string('perihal');
            $table->date('tgl_pelaksanaan')->nullable();
            $table->string('waktu')->nullable();
            $table->string('tempat')->nullable();
            $table->string('acara')->nullable();
            $table->text('isi_surat')->nullable();
            $table->string('lampiran_path')->nullable();
            $table->foreignId('referensi_surat_masuk_id')->nullable()->constrained('surat_masuks')->nullOnDelete();
            $table->enum('status', [
                'draft',
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
                'dikirim',
                'diarsipkan',
            ])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluars');
    }
};
