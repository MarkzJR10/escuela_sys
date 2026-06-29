<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discrepancias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // cajero
            $table->decimal('monto_sistema', 10, 2);
            $table->decimal('monto_fisico', 10, 2);
            $table->decimal('diferencia', 10, 2); // fisico - sistema (negativo = faltante, positivo = sobrante)
            $table->text('motivo')->nullable();
            $table->date('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discrepancias');
    }
};
