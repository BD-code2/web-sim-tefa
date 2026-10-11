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
        // Status enum already exists in base migration 2024_01_01_000014;
        // values will be expanded in model code
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op - status column unchanged in reverse
    }
};
