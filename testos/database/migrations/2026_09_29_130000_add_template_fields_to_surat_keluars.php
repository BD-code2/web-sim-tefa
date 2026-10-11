<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->string('jenis_surat')->default('undangan')->after('id');
            $table->json('detail_json')->nullable()->after('isi_surat');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->dropColumn(['jenis_surat', 'detail_json']);
        });
    }
};
