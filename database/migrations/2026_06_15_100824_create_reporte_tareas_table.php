<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // maestro
            $table->date('fecha');
            $table->string('materia')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('estatus', ['pendiente', 'leido'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_tareas');
    }
};
