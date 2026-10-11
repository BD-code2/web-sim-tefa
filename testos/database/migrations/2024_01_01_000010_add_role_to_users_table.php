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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'kepala_sekolah', 'tu', 'humas', 'guru', 'waka_kurikulum', 'siswa'])
                ->default('guru')
                ->after('password');
            $table->string('avatar')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->string('nisn')->nullable()->after('is_active');
            $table->string('kelas')->nullable()->after('nisn');
            $table->string('program_keahlian')->nullable()->after('kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rollback the added columns
            $table->dropColumn(['role', 'avatar', 'is_active', 'nisn', 'kelas', 'program_keahlian']);
        });
    }
};
