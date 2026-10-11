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
        Schema::create('surat_masuks', function (Blueprint $table) {
            $table->id();
            $table->string('no_agenda')->unique();
            $table->string('no_surat');
            $table->date('tanggal_surat');
            $table->date('tanggal_diterima');
            $table->string('pengirim');
            $table->string('perihal');
            $table->text('isi_ringkas')->nullable();
            $table->enum('sifat', ['biasa', 'segera', 'rahasia'])->default('biasa');
            $table->string('lampiran_path')->nullable();
            $table->enum('status', ['diterima', 'didisposisi', 'diproses', 'selesai', 'diarsipkan'])->default('diterima');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuks');
    }
};
