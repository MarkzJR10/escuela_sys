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
        Schema::create('adeudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->decimal('monto_base', 10, 2);
            $table->decimal('monto_actual', 10, 2);
            $table->string('periodo', 7); // Formato YYYY-MM
            $table->enum('status', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adeudos');
    }
};
