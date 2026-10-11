<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->timestamp('diteruskan_ke_kepala_sekolah_at')->nullable();
            $table->foreignId('diteruskan_ke_kepala_sekolah_oleh')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('disposisis', function (Blueprint $table) {
            $table->text('instruksi_tambahan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn('instruksi_tambahan');
        });

        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diteruskan_ke_kepala_sekolah_oleh');
            $table->dropColumn('diteruskan_ke_kepala_sekolah_at');
        });
    }
};
