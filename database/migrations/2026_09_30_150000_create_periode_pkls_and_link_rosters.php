<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_pkls', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('agenda')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('aktif')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('data_siswas', function (Blueprint $table) {
            $table->foreignId('periode_pkl_id')->nullable()->after('kelas')->constrained('periode_pkls')->nullOnDelete();
            $table->index(['periode_pkl_id', 'kelas']);
        });

        Schema::table('sertifikat_siswas', function (Blueprint $table) {
            $table->foreignId('periode_pkl_id')->nullable()->after('kelas')->constrained('periode_pkls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat_siswas', function (Blueprint $table) {
            $table->dropForeign(['periode_pkl_id']);
            $table->dropColumn('periode_pkl_id');
        });

        Schema::table('data_siswas', function (Blueprint $table) {
            $table->dropForeign(['periode_pkl_id']);
            $table->dropIndex(['periode_pkl_id', 'kelas']);
            $table->dropColumn('periode_pkl_id');
        });

        Schema::dropIfExists('periode_pkls');
    }
};
