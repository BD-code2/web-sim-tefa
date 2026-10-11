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
            $table->string('status_persetujuan')->default('pending')->after('status');
        });

        DB::table('surat_masuks')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('disposisis')
                    ->whereColumn('disposisis.surat_masuk_id', 'surat_masuks.id');
            })
            ->update(['status_persetujuan' => 'setuju']);
    }

    public function down(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropColumn('status_persetujuan');
        });
    }
};
