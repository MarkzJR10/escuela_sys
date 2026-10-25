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
        // Garantizar que todos los usuarios con rol profesor estén en la tabla profesores
        Profesor::syncAllProfesorUsers();

        $asignaciones = DB::table('maestro_materia')
            ->join('profesores', 'maestro_materia.profesor_id', '=', 'profesores.id')
            ->leftJoin('users', 'profesores.user_id', '=', 'users.id')
            ->join('materias', 'maestro_materia.materia_id', '=', 'materias.id')
            ->leftJoin('grado_grupos', 'maestro_materia.grado_grupo_id', '=', 'grado_grupos.id')
            ->select(
                'maestro_materia.id',
                'profesores.id as profesor_id',
                'profesores.nombre as profesor_nombre',
                'profesores.apellido_paterno as profesor_apellido',
                'profesores.apellido_materno as profesor_materno',
                'users.name as user_name',
                'materias.nombre as materia_nombre',
                'materias.grado as materia_grado',
                'grado_grupos.grado',
                'grado_grupos.grupo'
            )
            ->orderBy('profesores.nombre')
            ->get()
            ->map(function ($item) {
                $partes = array_filter([
                    $item->profesor_nombre,
                    $item->profesor_apellido,
                    $item->profesor_materno
                ], fn($v) => !is_null($v) && trim((string)$v) !== '');

                $item->profesor_completo = !empty($partes) 
                    ? implode(' ', $partes) 
                    : ($item->user_name ?: 'Profesor #' . $item->profesor_id);
                return $item;
            });

        $profesores = Profesor::with('user')->get()->sortBy('nombre_completo', SORT_NATURAL | SORT_FLAG_CASE)->values();
        $materias = Materia::orderBy('grado')->orderBy('nombre')->get();
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

        $materia = Materia::findOrFail($request->materia_id);

        // Validar que el grupo seleccionado pertenezca al grado de la materia
        if ($request->filled('grado_grupo_id') && !empty($materia->grado)) {
            $gradoGrupo = GradoGrupo::find($request->grado_grupo_id);
            if ($gradoGrupo && (string)$gradoGrupo->grado !== (string)$materia->grado) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['grado_grupo_id' => "El grupo seleccionado ({$gradoGrupo->grado}° \"{$gradoGrupo->grupo}\") no corresponde al grado de la materia ({$materia->nombre_con_grado})."]);
            }
        }

        // Evitar duplicados
        $existe = DB::table('maestro_materia')
            ->where('profesor_id', $request->profesor_id)
            ->where('materia_id', $request->materia_id)
            ->where('grado_grupo_id', $request->grado_grupo_id)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['materia_id' => 'Esta asignación ya existe para el profesor, materia y grado/grupo seleccionados.']);
        }

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
