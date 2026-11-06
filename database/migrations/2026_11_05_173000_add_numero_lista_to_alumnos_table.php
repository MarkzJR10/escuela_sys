<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('alumnos', 'numero_lista')) {
            Schema::table('alumnos', function (Blueprint $table) {
                $table->unsignedInteger('numero_lista')->nullable()->after('matricula');
            });
        }

        // Poblar inicialmente el numero_lista para los alumnos actuales por cada grupo
        $grupos = DB::table('alumnos')
            ->whereNotNull('grado_grupo_id')
            ->select('grado_grupo_id')
            ->distinct()
            ->pluck('grado_grupo_id');

        foreach ($grupos as $grupoId) {
            $alumnos = DB::table('alumnos')
                ->where('grado_grupo_id', $grupoId)
                ->orderBy('apellido_paterno', 'asc')
                ->orderBy('apellido_materno', 'asc')
                ->orderBy('nombre', 'asc')
                ->get(['id']);

            $num = 1;
            foreach ($alumnos as $alumno) {
                DB::table('alumnos')
                    ->where('id', $alumno->id)
                    ->update(['numero_lista' => $num++]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('alumnos', 'numero_lista')) {
            Schema::table('alumnos', function (Blueprint $table) {
                $table->dropColumn('numero_lista');
            });
        }
    }
};

