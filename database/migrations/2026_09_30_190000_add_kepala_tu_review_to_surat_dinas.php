<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_dinas', function (Blueprint $table) {
            $table->timestamp('diperiksa_kepala_tu_at')->nullable();
            $table->foreignId('diperiksa_kepala_tu_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diteruskan_ke_kepala_sekolah_at')->nullable();
            $table->foreignId('diteruskan_ke_kepala_sekolah_oleh')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat_dinas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diperiksa_kepala_tu_oleh');
            $table->dropColumn(['diperiksa_kepala_tu_at', 'diteruskan_ke_kepala_sekolah_at']);
            $table->dropConstrainedForeignId('diteruskan_ke_kepala_sekolah_oleh');
        });
    }
};
