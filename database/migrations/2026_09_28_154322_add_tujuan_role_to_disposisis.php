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
            $table->enum('tujuan_role', ['kepala_sekolah', 'tu', 'humas', 'waka_kurikulum', 'guru'])->nullable()->after('status_persetujuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn('tujuan_role');
        });
    }
};
