<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\Materia;
use App\Models\GradoGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaestroMateriaController extends Controller
{
    /**
     * Mostrar asignaciones y formulario.
     */
    public function index()
    {
        $asignaciones = DB::table('maestro_materia')
            ->join('profesores', 'maestro_materia.profesor_id', '=', 'profesores.id')
            ->join('materias', 'maestro_materia.materia_id', '=', 'materias.id')
            ->leftJoin('grado_grupos', 'maestro_materia.grado_grupo_id', '=', 'grado_grupos.id')
            ->select(
                'maestro_materia.id',
                'profesores.nombre as profesor_nombre',
                'profesores.apellido_paterno as profesor_apellido',
                'materias.nombre as materia_nombre',
                'grado_grupos.grado',
                'grado_grupos.grupo'
            )
            ->orderBy('profesores.apellido_paterno')
            ->get();

        $profesores = Profesor::orderBy('apellido_paterno')->get();
        $materias = Materia::orderBy('nombre')->get();
        $gradoGrupos = GradoGrupo::orderBy('grado')->orderBy('grupo')->get();

        return view('maestro_materia.index', compact('asignaciones', 'profesores', 'materias', 'gradoGrupos'));
    }

    /**
     * Guardar nueva asignación.
     */
    public function store(Request $request)
    {
        $request->validate([
            'profesor_id'    => 'required|exists:profesores,id',
            'materia_id'     => 'required|exists:materias,id',
            'grado_grupo_id' => 'nullable|exists:grado_grupos,id',
        ]);

        DB::table('maestro_materia')->insert([
            'profesor_id'    => $request->profesor_id,
            'materia_id'     => $request->materia_id,
            'grado_grupo_id' => $request->grado_grupo_id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('maestro_materia.index')
            ->with('success', 'Asignación Maestro-Materia creada exitosamente.');
    }

    /**
     * Eliminar asignación.
     */
    public function destroy($id)
    {
        DB::table('maestro_materia')->where('id', $id)->delete();

        return redirect()->route('maestro_materia.index')
            ->with('success', 'Asignación eliminada exitosamente.');
    }
}
