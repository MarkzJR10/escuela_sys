<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Alumno;
use App\Models\Materia;
use App\Models\GradoGrupo;
use App\Models\PeriodoControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CalificacionController extends Controller
{
    public function index(Request $request)
    {
        $grado_grupo_id = $request->grado_grupo_id;
        $materia_id = $request->materia_id;
        $alumno_id = $request->alumno_id;

        $alumnosQuery = Alumno::with(['gradoGrupo', 'calificaciones' => function($q) use ($materia_id) {
            if ($materia_id) {
                $q->where('materia_id', $materia_id);
            }
        }, 'calificaciones.materia']);

        if ($grado_grupo_id) {
            $alumnosQuery->where('grado_grupo_id', $grado_grupo_id);
        }

        if ($alumno_id) {
            $alumnosQuery->where('id', $alumno_id);
        }

        $alumnos = $alumnosQuery->get();
        
        $data = [];
        $selectedMateria = $materia_id ? Materia::find($materia_id) : null;

        foreach ($alumnos as $alumno) {
            $materiasAgrupadas = $alumno->calificaciones->groupBy('materia_id');
            
            if ($selectedMateria && $materiasAgrupadas->isEmpty()) {
                // Agregar fila vacía para este alumno y materia
                $data[] = [
                    'alumno' => $alumno,
                    'materia' => $selectedMateria,
                    't1' => null,
                    't2' => null,
                    't3' => null,
                    'ultima' => null
                ];
            }

            foreach ($materiasAgrupadas as $m_id => $califs) {
                $materia = $califs->first()->materia;
                $data[] = [
                    'alumno' => $alumno,
                    'materia' => $materia,
                    't1' => $califs->where('trimestre', 1)->first(),
                    't2' => $califs->where('trimestre', 2)->first(),
                    't3' => $califs->where('trimestre', 3)->first(),
                    'ultima' => $califs->sortByDesc('updated_at')->first()->updated_at
                ];
            }
        }

        $gradoGrupos = GradoGrupo::all();
        $materias = Materia::all();
        $periodosControl = PeriodoControl::pluck('activo', 'trimestre')->toArray();

        return view('calificaciones.index', compact('data', 'gradoGrupos', 'materias', 'periodosControl'));
    }

    public function captura(Request $request)
    {
        // Solo Admin y Profesor pueden capturar
        if (!Auth::user()->hasAnyRole(['administrador', 'profesor'])) {
            return redirect()->route('calificaciones.index')->with('error', 'No tienes permiso para capturar calificaciones.');
        }

        $gradoGrupos = GradoGrupo::all();
        $materias = Materia::all();
        $alumnos = [];

        if ($request->filled(['grado_grupo_id', 'materia_id'])) {
            $alumnos = Alumno::where('grado_grupo_id', $request->grado_grupo_id)
                ->with(['calificaciones' => function($q) use ($request) {
                    $q->where('materia_id', $request->materia_id);
                }])
                ->get();
        }

        $periodosControl = PeriodoControl::pluck('activo', 'trimestre')->toArray();

        return view('calificaciones.captura', compact('gradoGrupos', 'materias', 'alumnos', 'periodosControl'));
    }

    public function bulkStore(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['administrador', 'profesor'])) {
            return redirect()->route('calificaciones.index')->with('error', 'No tienes permiso para capturar calificaciones.');
        }

        $request->validate([
            'grado_grupo_id' => 'nullable|exists:grado_grupos,id',
            'materia_id' => 'required|exists:materias,id',
            'notas' => 'required|array',
            'notas.*' => 'array',
            'notas.*.*' => 'nullable|numeric|min:0|max:10'
        ]);

        $periodosActivos = PeriodoControl::where('activo', true)->pluck('trimestre')->toArray();

        DB::transaction(function () use ($request, $periodosActivos) {
            foreach ($request->notas as $alumno_id => $trimestres) {
                foreach ($trimestres as $trimestre => $puntaje) {
                    // Solo guardar si el trimestre está activo y el puntaje no es nulo (Admin puede bypass)
                    if ($puntaje !== null && (in_array($trimestre, $periodosActivos) || Auth::user()->hasRole('administrador'))) {
                        Calificacion::updateOrCreate(
                            [
                                'alumno_id' => $alumno_id,
                                'materia_id' => $request->materia_id,
                                'trimestre' => $trimestre,
                            ],
                            ['puntaje' => $puntaje]
                        );
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Calificaciones actualizadas exitosamente.');
    }

    public function create()
    {
        // Redirigir a la nueva vista de captura masiva
        return redirect()->route('calificaciones.captura');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['administrador', 'profesor'])) {
            return abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'materia_id' => 'required|exists:materias,id',
            'trimestre' => 'required|in:1,2,3',
            'puntaje' => 'required|numeric|min:0|max:10'
        ]);

        Calificacion::create($request->all());

        return redirect()->route('calificaciones.index')->with('success', 'Calificación registrada exitosamente.');
    }

    public function show(Calificacion $calificacione)
    {
        // En español `calificacione` es el nombre de variable generado por artisan para resource route
        return view('calificaciones.show', compact('calificacione'));
    }

    public function edit(Calificacion $calificacione)
    {
        // Solo Admin, Coordinador y Profesor pueden editar
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor'])) {
            return redirect()->route('calificaciones.index')->with('error', 'No tienes permiso para editar calificaciones.');
        }

        $periodoActivo = PeriodoControl::where('trimestre', $calificacione->trimestre)->value('activo');
        if (!$periodoActivo && !Auth::user()->hasRole('administrador')) {
            return redirect()->route('calificaciones.index')->with('error', 'El periodo para esta calificación está cerrado.');
        }

        $alumnos = Alumno::all();
        $materias = Materia::all();
        return view('calificaciones.edit', compact('calificacione', 'alumnos', 'materias'));
    }

    public function update(Request $request, Calificacion $calificacione)
    {
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor'])) {
            return abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $periodoActivo = PeriodoControl::where('trimestre', $calificacione->trimestre)->value('activo');
        if (!$periodoActivo && !Auth::user()->hasRole('administrador')) {
            return redirect()->route('calificaciones.index')->with('error', 'No se puede actualizar una calificación de un periodo cerrado.');
        }

        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'materia_id' => 'required|exists:materias,id',
            'trimestre' => 'required|in:1,2,3',
            'puntaje' => 'required|numeric|min:0|max:10'
        ]);

        $calificacione->update($request->all());

        return redirect()->route('calificaciones.index')->with('success', 'Calificación actualizada exitosamente.');
    }

    public function destroy(Calificacion $calificacione)
    {
        $calificacione->delete();
        return redirect()->route('calificaciones.index')->with('success', 'Calificación eliminada exitosamente.');
    }
}
