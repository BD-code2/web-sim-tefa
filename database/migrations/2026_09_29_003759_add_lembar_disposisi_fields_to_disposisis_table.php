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
        Schema::table('disposisis', function (Blueprint $table) {
            $table->json('tujuan_disposisi')->nullable()->after('tujuan_role');
            $table->json('harapan_instruksi')->nullable()->after('tujuan_disposisi');
            $table->string('sifat_surat')->nullable()->after('harapan_instruksi');
            $table->string('penerima_lain')->nullable()->after('sifat_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn(['tujuan_disposisi', 'harapan_instruksi', 'sifat_surat', 'penerima_lain']);
        });
    }
};
