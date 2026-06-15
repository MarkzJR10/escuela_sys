<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletas', function (Blueprint $table) {
            $table->id();
            $table->string('matricula');
            $table->string('nombre');
            $table->string('grado')->nullable();
            $table->string('grupo')->nullable();
            $table->string('materia');
            $table->decimal('p1', 5, 2)->nullable(); // Trimestre 1
            $table->decimal('p2', 5, 2)->nullable(); // Trimestre 2
            $table->decimal('p3', 5, 2)->nullable(); // Trimestre 3
            $table->decimal('p_final', 5, 2)->nullable(); // Promedio final
            $table->string('ciclo')->nullable(); // ej: 2025-2026
            $table->timestamps();

            $table->index(['matricula', 'ciclo']);
            $table->index(['grado', 'grupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletas');
    }
};
