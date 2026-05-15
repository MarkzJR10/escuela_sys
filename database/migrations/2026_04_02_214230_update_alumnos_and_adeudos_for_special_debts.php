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
        Schema::table('alumnos', function (Blueprint $table) {
            $table->enum('genero', ['M', 'F'])->nullable()->after('apellidos');
        });

        Schema::table('adeudos', function (Blueprint $table) {
            $table->enum('tipo', ['colegiatura', 'especial'])->default('colegiatura')->after('alumno_id');
            $table->string('concepto')->nullable()->after('tipo');
            $table->string('periodo', 7)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('genero');
        });

        Schema::table('adeudos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'concepto']);
            $table->string('periodo', 7)->nullable(false)->change();
        });
    }
};
