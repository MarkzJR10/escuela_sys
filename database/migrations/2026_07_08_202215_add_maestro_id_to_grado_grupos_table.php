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
        Schema::table('grado_grupos', function (Blueprint $table) {
            $table->foreignId('maestro_id')->nullable()->constrained('profesores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grado_grupos', function (Blueprint $table) {
            $table->dropForeign(['maestro_id']);
            $table->dropColumn('maestro_id');
        });
    }
};
