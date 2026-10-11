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
            $table->string('role')->default('siswa')->change();
        });

        Schema::table('disposisis', function (Blueprint $table) {
            $table->string('tujuan_role')->nullable()->change();
        });

        DB::table('users')->where('email', 'admin@smkn4tanjungpinang.sch.id')->update([
            'role' => 'tu',
            'is_active' => false,
            'updated_at' => now(),
        ]);
        DB::table('users')->where('email', 'guru@smkn4tanjungpinang.sch.id')->update([
            'role' => 'siswa',
            'is_active' => false,
            'updated_at' => now(),
        ]);

        DB::table('users')->where('role', 'admin')->update(['role' => 'tu']);
        DB::table('users')->where('role', 'guru')->update(['role' => 'siswa', 'is_active' => false]);
        DB::table('disposisis')->where('tujuan_role', 'admin')->update(['tujuan_role' => 'tu']);
        DB::table('disposisis')->where('tujuan_role', 'guru')->update(['tujuan_role' => 'siswa']);
    }

    public function down(): void
    {
        // Role dinamis tidak bisa dipulihkan dengan aman ke enum lama.
    }
};
