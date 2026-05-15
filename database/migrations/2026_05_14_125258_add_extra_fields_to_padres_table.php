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
        Schema::table('padres', function (Blueprint $table) {
            $table->string('nombre')->after('user_id')->nullable();
            $table->string('apellido_paterno')->after('nombre')->nullable();
            $table->string('apellido_materno')->after('apellido_paterno')->nullable();
            $table->string('genero')->after('apellido_materno')->nullable();
            $table->date('fecha_nacimiento')->after('curp')->nullable();
            $table->text('domicilio')->after('fecha_nacimiento')->nullable();
            $table->string('celular')->after('telefono')->nullable();
            $table->string('fotografia')->after('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->dropColumn([
                'nombre',
                'apellido_paterno',
                'apellido_materno',
                'genero',
                'fecha_nacimiento',
                'domicilio',
                'celular',
                'fotografia'
            ]);
        });
    }
};
