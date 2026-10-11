<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->unsignedInteger('nomor_urut')->nullable()->unique();
            $table->text('disposisi_tindak_lanjut')->nullable();
            $table->text('keterangan')->nullable();
        });

        DB::table('surat_masuks')->orderBy('id')->each(function ($surat) {
            DB::table('surat_masuks')->where('id', $surat->id)->update(['nomor_urut' => $surat->id]);
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropUnique(['nomor_urut']);
            $table->dropColumn(['nomor_urut', 'disposisi_tindak_lanjut', 'keterangan']);
        });
    }
};
