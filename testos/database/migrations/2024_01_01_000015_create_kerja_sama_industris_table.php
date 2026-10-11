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
        Schema::create('kerja_sama_industris', function (Blueprint $table) {
            $table->id();
            $table->string('nama_industri');
            $table->text('alamat');
            $table->string('kontak')->nullable();
            $table->string('email')->nullable();
            $table->string('program_keahlian');
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->enum('status', ['aktif', 'akan_berakhir', 'berakhir'])->default('aktif');
            $table->string('dokumen_mou_path')->nullable();
            $table->text('catatan')->nullable();
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
        Schema::dropIfExists('kerja_sama_industris');
    }
};
