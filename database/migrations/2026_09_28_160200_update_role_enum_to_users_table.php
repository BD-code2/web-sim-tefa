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
        // Role enum already exists from base migration 2024_01_01_000010;
        // 'waka_kurikulum' value is now supported in application code
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op - role column unchanged in reverse
    }
};
