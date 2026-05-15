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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // ID del Cajero
            $table->decimal('total', 10, 2);
            $table->string('referencia_ticket')->unique(); // Ej: TKT-00001
            $table->timestamp('fecha_pago');
            $table->timestamps();
        });

        Schema::create('pago_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->onDelete('cascade');
            $table->foreignId('adeudo_id')->constrained('adeudos');
            $table->decimal('monto_adeudo', 10, 2); // Monto que debía
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2); // Monto real cobrado
            $table->string('notas')->nullable(); // Autogenerado si hay descuento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_detalles');
        Schema::dropIfExists('pagos');
    }
};
