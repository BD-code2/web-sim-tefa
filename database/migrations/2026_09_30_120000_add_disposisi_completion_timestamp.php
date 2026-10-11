<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->timestamp('selesai_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropIndex(['selesai_at']);
            $table->dropColumn('selesai_at');
        });
    }
};
