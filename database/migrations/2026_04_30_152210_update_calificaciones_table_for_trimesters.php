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
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->tinyInteger('trimestre')->after('materia_id')->default(1);
        });

        // Migrate data if possible (e.g. if 'periodo' was '1', '2', '3')
        DB::table('calificaciones')->update([
            'trimestre' => DB::raw('CAST(periodo AS UNSIGNED)')
        ]);

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropColumn('periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->string('periodo')->after('materia_id')->nullable();
        });

        DB::table('calificaciones')->update([
            'periodo' => DB::raw('CAST(trimestre AS CHAR)')
        ]);

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropColumn('trimestre');
        });
    }
};
