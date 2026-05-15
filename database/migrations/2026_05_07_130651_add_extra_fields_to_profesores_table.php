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
        Schema::table('profesores', function (Blueprint $table) {
            $table->string('apellido_paterno')->after('nombre')->nullable();
            $table->string('apellido_materno')->after('apellido_paterno')->nullable();
            $table->string('genero')->after('apellido_materno')->nullable();
            $table->string('curp')->after('genero')->nullable();
            $table->date('fecha_nacimiento')->after('curp')->nullable();
            $table->text('domicilio')->after('fecha_nacimiento')->nullable();
            $table->string('celular')->after('telefono')->nullable();
            $table->string('fotografia')->after('user_id')->nullable();
        });

        // Migrate data from apellidos to apellido_paterno
        DB::table('profesores')->get()->each(function ($profesor) {
            DB::table('profesores')->where('id', $profesor->id)->update([
                'apellido_paterno' => $profesor->apellidos,
            ]);
        });

        Schema::table('profesores', function (Blueprint $table) {
            $table->dropColumn('apellidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesores', function (Blueprint $table) {
            $table->string('apellidos')->after('nombre')->nullable();
        });

        DB::table('profesores')->get()->each(function ($profesor) {
            DB::table('profesores')->where('id', $profesor->id)->update([
                'apellidos' => trim($profesor->apellido_paterno . ' ' . $profesor->apellido_materno),
            ]);
        });

        Schema::table('profesores', function (Blueprint $table) {
            $table->dropColumn([
                'apellido_paterno',
                'apellido_materno',
                'genero',
                'curp',
                'fecha_nacimiento',
                'domicilio',
                'celular',
                'fotografia'
            ]);
        });
    }
};
