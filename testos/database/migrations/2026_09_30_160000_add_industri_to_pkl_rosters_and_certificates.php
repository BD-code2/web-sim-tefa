<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_siswas', function (Blueprint $table) {
            $table->string('industri')->nullable()->after('kelas');
        });

        Schema::table('sertifikat_siswas', function (Blueprint $table) {
            $table->string('nama_industri_pkl')->nullable()->after('kerja_sama_industri_id');
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat_siswas', function (Blueprint $table) {
            $table->dropColumn('nama_industri_pkl');
        });

        Schema::table('data_siswas', function (Blueprint $table) {
            $table->dropColumn('industri');
        });
    }
};
