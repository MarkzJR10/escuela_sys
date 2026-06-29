<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Matutino, Vespertino, etc
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
        });

        // Add turno_id to grado_grupos
        Schema::table('grado_grupos', function (Blueprint $table) {
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('grado_grupos', function (Blueprint $table) {
            $table->dropForeign(['turno_id']);
            $table->dropColumn('turno_id');
        });
        Schema::dropIfExists('turnos');
    }
};
