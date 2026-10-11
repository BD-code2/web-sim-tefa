<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'like', '%@smkn4tanjungpinang.sch.id')
            ->update([
                'email' => DB::raw("REPLACE(email, '@smkn4tanjungpinang.sch.id', '@smkn4.com')"),
            ]);
    }

    public function down(): void
    {
        // Tidak dibalik otomatis agar email @smkn4.com yang dibuat setelah migrasi tetap canonical.
    }
};
