<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('tu')->change();
        });

        // Pertahankan akun dan relasi historis, tetapi nonaktifkan akun lama.
        DB::table('users')->where('role', 'siswa')->update([
            'role' => 'nonaktif',
            'is_active' => false,
            'updated_at' => now(),
        ]);

        // Disposisi lama kepada akun yang dinonaktifkan tidak lagi punya role penerima.
        DB::table('disposisis')->where('tujuan_role', 'siswa')->update(['tujuan_role' => null]);
    }

    public function down(): void
    {
        // Pemulihan akun dan disposisi lama tidak dapat dilakukan dengan aman.
    }
};
