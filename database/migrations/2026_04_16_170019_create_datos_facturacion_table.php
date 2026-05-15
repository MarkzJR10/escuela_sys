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
        Schema::create('datos_facturacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('padre_id')->constrained('padres')->onDelete('cascade');
            $table->string('rfc', 13)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('calle')->nullable();
            $table->string('numero')->nullable();
            $table->string('colonia')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('sep')->nullable();
            $table->string('sat')->nullable();
            $table->string('estado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datos_facturacion');
    }
};
