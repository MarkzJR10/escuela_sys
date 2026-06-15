<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maestro_materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('profesores')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('grado_grupo_id')->nullable()->constrained('grado_grupos')->onDelete('set null');
            $table->timestamps();

            $table->unique(['profesor_id', 'materia_id', 'grado_grupo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maestro_materia');
    }
};
