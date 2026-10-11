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
        Schema::create('data_siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 30)->index();
            $table->string('nama');
            $table->string('peran')->nullable();
            $table->date('tanggal')->nullable();
            // Status pembuatan sertifikat: Pending / Selesai / Gagal
            $table->string('status_sertifikat', 20)->default('Pending')->index();
            // Path relatif terhadap disk public, mis. certificates/xxx.png
            $table->string('sertifikat_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_siswas');
    }
};
