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
        Schema::table('alumnos', function (Blueprint $table) {
            $table->unsignedBigInteger('colegiatura_id')->nullable()->after('grado_grupo_id');
            $table->foreign('colegiatura_id')->references('id')->on('colegiaturas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['colegiatura_id']);
            $table->dropColumn('colegiatura_id');
        });
    }
};
