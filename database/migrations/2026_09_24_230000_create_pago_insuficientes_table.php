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
        Schema::create('pago_insuficientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos')->onDelete('cascade');
            $table->string('matricula')->nullable();
            $table->string('alumno_nombre')->nullable();
            $table->string('grado')->nullable();
            $table->string('periodo', 10)->nullable();
            $table->date('fecha_pago')->nullable();
            $table->text('referencia')->nullable();
            $table->text('referencia_leyenda')->nullable();
            $table->decimal('monto_abonado', 10, 2);
            $table->decimal('monto_debido', 10, 2);
            $table->decimal('diferencia', 10, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_insuficientes');
    }
};
