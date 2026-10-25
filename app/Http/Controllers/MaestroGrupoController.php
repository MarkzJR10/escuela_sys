<?php

namespace App\Http\Controllers;

use App\Models\GradoGrupo;
use App\Models\Profesor;
use Illuminate\Http\Request;

class MaestroGrupoController extends Controller
{
    /**
     * Muestra la pantalla de asignación de maestros de planta a grupos.
     */
    public function index()
    {
        Profesor::syncAllProfesorUsers();

        // Obtener todos los grados/grupos con su maestro de planta actual
        $gradoGrupos = GradoGrupo::with('maestro')->orderBy('grado')->orderBy('grupo')->get();
        
        // Obtener todos los profesores disponibles para la selección
        $profesores = Profesor::with('user')->get()->sortBy('nombre_completo', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('maestro_grupo.index', compact('gradoGrupos', 'profesores'));
    }

    /**
     * Guarda o actualiza la relación del maestro de planta con el grupo.
     */
    public function store(Request $request)
    {
        $request->validate([
            'grado_grupo_id' => 'required|exists:grado_grupos,id',
            'profesor_id'    => 'required|exists:profesores,id',
        ]);

        $grupo = GradoGrupo::findOrFail($request->grado_grupo_id);
        $grupo->update([
            'maestro_id' => $request->profesor_id,
        ]);

        $profesor = Profesor::findOrFail($request->profesor_id);

        return redirect()->route('maestro_grupo.index')
            ->with('success', "Se asignó a {$profesor->nombre_completo} como Maestro de Planta del grupo {$grupo->grado}° \"{$grupo->grupo}\".");
    }

    /**
     * Remueve la relación del maestro de planta con el grupo.
     */
    public function destroy($id)
    {
        $grupo = GradoGrupo::findOrFail($id);
        $maestroNombre = $grupo->maestro 
            ? $grupo->maestro->nombre_completo 
            : "maestro";
        
        $grupo->update([
            'maestro_id' => null,
        ]);

        return redirect()->route('maestro_grupo.index')
            ->with('success', "Se desasignó al {$maestroNombre} del grupo {$grupo->grado}° \"{$grupo->grupo}\".");
    }
}
