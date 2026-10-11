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
        Schema::create('surat_dinas', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_surat', ['SK', 'SPT'])->default('SK');
            $table->string('no_surat')->unique()->nullable();
            $table->text('tentang_judul');
            $table->date('tgl_ditetapkan')->nullable();
            $table->enum('status', [
                'draft',
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
            ])->default('draft');
            // Detail khusus per jenis surat (menimbang/mengingat/dictum_list untuk SK,
            // pegawai/tugas/peserta untuk SPT) — lihat model SuratDinas.
            $table->json('detail_json')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            // Hasil persetujuan Kepala Sekolah
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_dinas');
    }
};
