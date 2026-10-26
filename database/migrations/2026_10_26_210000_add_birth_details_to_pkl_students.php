<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['data_siswas', 'sertifikat_siswas'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('tempat_tgl_lahir')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['data_siswas', 'sertifikat_siswas'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('tempat_tgl_lahir');
            });
        }
    }
};
