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
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('apellido_paterno')->after('nombre')->nullable();
            $table->string('apellido_materno')->after('apellido_paterno')->nullable();
            $table->string('curp')->after('genero')->nullable();
            $table->date('fecha_nacimiento')->after('curp')->nullable();
            $table->text('domicilio')->after('fecha_nacimiento')->nullable();
            $table->text('alergias')->after('domicilio')->nullable();
            $table->string('telefono')->after('alergias')->nullable();
            $table->string('celular')->after('telefono')->nullable();
            $table->string('fotografia')->after('colegiatura')->nullable();
        });

        // Migrate data from apellidos to apellido_paterno
        DB::table('alumnos')->get()->each(function ($alumno) {
            DB::table('alumnos')->where('id', $alumno->id)->update([
                'apellido_paterno' => $alumno->apellidos,
            ]);
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('apellidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('apellidos')->after('nombre')->nullable();
        });

        DB::table('alumnos')->get()->each(function ($alumno) {
            DB::table('alumnos')->where('id', $alumno->id)->update([
                'apellidos' => trim($alumno->apellido_paterno . ' ' . $alumno->apellido_materno),
            ]);
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn([
                'apellido_paterno',
                'apellido_materno',
                'curp',
                'fecha_nacimiento',
                'domicilio',
                'alergias',
                'telefono',
                'celular',
                'fotografia'
            ]);
        });
    }
};
