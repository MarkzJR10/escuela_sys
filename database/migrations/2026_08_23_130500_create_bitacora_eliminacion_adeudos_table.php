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
        Schema::create('bitacora_eliminacion_adeudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos')->onDelete('set null');
            $table->string('matricula')->nullable();
            $table->string('nombre_alumno');
            $table->string('ciclo', 20);
            $table->decimal('monto_anterior', 10, 2)->default(0);
            $table->decimal('monto_eliminado', 10, 2)->default(0);
            $table->decimal('monto_nuevo', 10, 2)->default(0);
            $table->string('meses_afectados')->nullable();
            $table->integer('total_registros_eliminados')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_eliminacion_adeudos');
    }
};
