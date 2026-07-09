<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->renameColumn('curp', 'rfc');
        });

        // Trim existing data to avoid truncation errors
        DB::statement("UPDATE padres SET rfc = SUBSTRING(rfc, 1, 13)");

        Schema::table('padres', function (Blueprint $table) {
            $table->string('rfc', 13)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->string('rfc', 18)->change();
        });

        Schema::table('padres', function (Blueprint $table) {
            $table->renameColumn('rfc', 'curp');
        });
    }
};
