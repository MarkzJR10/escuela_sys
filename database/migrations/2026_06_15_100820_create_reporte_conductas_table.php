<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_conductas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // maestro que reporta
            $table->integer('no_reporte')->default(1);
            $table->date('fecha');
            $table->boolean('razon1')->default(false); // Faltar el respeto al maestro
            $table->boolean('razon2')->default(false); // Molestar a sus compañeros
            $table->boolean('razon3')->default(false); // Pelear
            $table->boolean('razon4')->default(false); // Jugar dentro del aula
            $table->boolean('razon5')->default(false); // Utilizar lenguaje inadecuado
            $table->boolean('razon6')->default(false); // Hacer caso omiso de indicaciones
            $table->boolean('razon7')->default(false); // Incumplimiento de más de 3 tareas
            $table->boolean('razon8')->default(false); // No atender la clase por hacer tarea de otra materia
            $table->boolean('razon9')->default(false); // Indisciplina
            $table->boolean('razon10')->default(false); // Dañar instalaciones
            $table->boolean('razon11')->default(false); // Promedio semanal de conducta menor de 7
            $table->text('otro')->nullable();
            $table->enum('estatus', ['pendiente', 'leido'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_conductas');
    }
};
