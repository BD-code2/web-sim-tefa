<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->foreignId('kerja_sama_industri_id')
                ->nullable()
                ->after('id')
                ->constrained('kerja_sama_industris')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kerja_sama_industri_id');
        });
    }
};
