<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Alumno;
use App\Models\Materia;
use App\Models\GradoGrupo;
use App\Models\PeriodoControl;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CalificacionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isTeacher = !$user->hasAnyRole(['administrador', 'coordinador']) 
            && $user->hasAnyRole(['profesor', 'maestro']);

        $asignacionesDocente = collect();
        $asignacionKey = $request->input('asignacion');

        if ($isTeacher) {
            $profesor = $user->profesor ?? Profesor::where('user_id', $user->id)->first();
            if (!$profesor) {
                $profesor = Profesor::syncFromUser($user);
            }

            if ($profesor) {
                // 1. Asignaciones directas en maestro_materia
                $mmList = DB::table('maestro_materia')
                    ->join('materias', 'maestro_materia.materia_id', '=', 'materias.id')
                    ->leftJoin('grado_grupos', 'maestro_materia.grado_grupo_id', '=', 'grado_grupos.id')
                    ->where('maestro_materia.profesor_id', $profesor->id)
                    ->select(
                        'maestro_materia.materia_id',
                        'materias.nombre as materia_nombre',
                        'materias.grado as materia_grado',
                        'maestro_materia.grado_grupo_id',
                        'grado_grupos.grado as grupo_grado',
                        'grado_grupos.grupo as grupo_nombre'
                    )
                    ->orderBy('materias.nombre')
                    ->get();

                foreach ($mmList as $mm) {
                    if ($mm->grado_grupo_id && $mm->grupo_grado) {
                        $key = "{$mm->materia_id}_{$mm->grado_grupo_id}";
                        $label = "{$mm->materia_nombre} - {$mm->grupo_grado}° \"{$mm->grupo_nombre}\"";
                        $asignacionesDocente->put($key, [
                            'key' => $key,
                            'materia_id' => $mm->materia_id,
                            'grado_grupo_id' => $mm->grado_grupo_id,
                            'label' => $label,
                        ]);
                    } elseif (!empty($mm->materia_grado)) {
                        // Aplica a todos los grupos de ese grado
                        $grupos = GradoGrupo::where('grado', $mm->materia_grado)->orderBy('grupo')->get();
                        foreach ($grupos as $g) {
                            $key = "{$mm->materia_id}_{$g->id}";
                            $label = "{$mm->materia_nombre} - {$g->grado}° \"{$g->grupo}\"";
                            $asignacionesDocente->put($key, [
                                'key' => $key,
                                'materia_id' => $mm->materia_id,
                                'grado_grupo_id' => $g->id,
                                'label' => $label,
                            ]);
                        }
                    }
                }

                // 2. Si no tiene en maestro_materia pero es maestro de planta en grado_grupos
                if ($asignacionesDocente->isEmpty()) {
                    $gruposPlanta = GradoGrupo::where('maestro_id', $profesor->id)->get();
                    foreach ($gruposPlanta as $gp) {
                        $materiasGrado = Materia::where('grado', $gp->grado)->get();
                        foreach ($materiasGrado as $mg) {
                            $key = "{$mg->id}_{$gp->id}";
                            $label = "{$mg->nombre} - {$gp->grado}° \"{$gp->grupo}\"";
                            $asignacionesDocente->put($key, [
                                'key' => $key,
                                'materia_id' => $mg->id,
                                'grado_grupo_id' => $gp->id,
                                'label' => $label,
                            ]);
                        }
                    }
                }
            }

            // Determinar materia y grupo activos para el maestro
            if ($asignacionesDocente->isNotEmpty()) {
                if (!$asignacionKey || !$asignacionesDocente->has($asignacionKey)) {
                    $candidateKey = "{$request->materia_id}_{$request->grado_grupo_id}";
                    if ($asignacionesDocente->has($candidateKey)) {
                        $asignacionKey = $candidateKey;
                    } else {
                        $asignacionKey = $asignacionesDocente->keys()->first();
                    }
                }
                $active = $asignacionesDocente->get($asignacionKey);
                $materia_id = $active['materia_id'];
                $grado_grupo_id = $active['grado_grupo_id'];
            } else {
                $asignacionKey = null;
                $materia_id = null;
                $grado_grupo_id = null;
            }
        } else {
            // Administrador / Coordinador
            if ($asignacionKey && str_contains($asignacionKey, '_')) {
                [$m_id, $gg_id] = explode('_', $asignacionKey, 2);
                $materia_id = $m_id;
                $grado_grupo_id = $gg_id;
            } else {
                $materia_id = $request->materia_id;
                $grado_grupo_id = $request->grado_grupo_id;
            }
        }

        // Obtener listado de alumnos
        if ($isTeacher && $asignacionesDocente->isEmpty()) {
            // Si el maestro no tiene ninguna asignación, no debe ver alumnos
            $alumnos = collect();
        } else {
            $alumnosQuery = Alumno::with(['gradoGrupo', 'calificaciones' => function($q) use ($materia_id) {
                if ($materia_id) {
                    $q->where('materia_id', $materia_id);
                }
            }, 'calificaciones.materia']);

            if ($grado_grupo_id) {
                $alumnosQuery->where('grado_grupo_id', $grado_grupo_id);
            } elseif ($isTeacher) {
                $assignedGroupIds = $asignacionesDocente->pluck('grado_grupo_id')->unique()->all();
                $alumnosQuery->whereIn('grado_grupo_id', $assignedGroupIds);
            }

            if ($request->alumno_id) {
                $alumnosQuery->where('id', $request->alumno_id);
            }

            $alumnos = $alumnosQuery->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombre')
                ->get();
        }

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

        $gradoGrupos = GradoGrupo::orderBy('grado')->orderBy('grupo')->get();
        $materias = Materia::orderBy('grado')->orderBy('nombre')->get();
        $periodosControl = PeriodoControl::pluck('activo', 'trimestre')->toArray();

        return view('calificaciones.index', compact(
            'data',
            'gradoGrupos',
            'materias',
            'periodosControl',
            'isTeacher',
            'asignacionesDocente',
            'asignacionKey',
            'materia_id',
            'grado_grupo_id'
        ));
    }

    public function captura(Request $request)
    {
        // Solo Admin, Coordinador, Profesor y Maestro pueden capturar
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor', 'maestro'])) {
            return redirect()->route('calificaciones.index')->with('error', 'No tienes permiso para capturar calificaciones.');
        }

        $user = Auth::user();
        $isTeacher = !$user->hasAnyRole(['administrador', 'coordinador']) && $user->hasAnyRole(['profesor', 'maestro']);

        if ($isTeacher) {
            // El maestro captura directamente a través del index simplificado con 1 solo select
            return redirect()->route('calificaciones.index', $request->all());
        }

        $gradoGrupos = GradoGrupo::orderBy('grado')->orderBy('grupo')->get();
        $materias = Materia::orderBy('grado')->orderBy('nombre')->get();
        $alumnos = [];

        if ($request->filled(['grado_grupo_id', 'materia_id'])) {
            $alumnos = Alumno::where('grado_grupo_id', $request->grado_grupo_id)
                ->with(['calificaciones' => function($q) use ($request) {
                    $q->where('materia_id', $request->materia_id);
                }])
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombre')
                ->get();
        }

        $periodosControl = PeriodoControl::pluck('activo', 'trimestre')->toArray();

        return view('calificaciones.captura', compact('gradoGrupos', 'materias', 'alumnos', 'periodosControl'));
    }

    public function bulkStore(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor', 'maestro'])) {
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
                    // Guardar si el trimestre está activo o si es admin/coordinador
                    if ($puntaje !== null && (in_array($trimestre, $periodosActivos) || Auth::user()->hasAnyRole(['administrador', 'coordinador']))) {
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

        $redirectParams = [];
        if ($request->filled('asignacion')) {
            $redirectParams['asignacion'] = $request->asignacion;
        } elseif ($request->filled(['materia_id', 'grado_grupo_id'])) {
            $redirectParams['materia_id'] = $request->materia_id;
            $redirectParams['grado_grupo_id'] = $request->grado_grupo_id;
        }

        return redirect()->route('calificaciones.index', $redirectParams)
            ->with('success', 'Calificaciones actualizadas exitosamente.');
    }

    public function create()
    {
        return redirect()->route('calificaciones.index');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor', 'maestro'])) {
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
        return view('calificaciones.show', compact('calificacione'));
    }

    public function edit(Calificacion $calificacione)
    {
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor', 'maestro'])) {
            return redirect()->route('calificaciones.index')->with('error', 'No tienes permiso para editar calificaciones.');
        }

        $periodoActivo = PeriodoControl::where('trimestre', $calificacione->trimestre)->value('activo');
        if (!$periodoActivo && !Auth::user()->hasAnyRole(['administrador', 'coordinador'])) {
            return redirect()->route('calificaciones.index')->with('error', 'El periodo para esta calificación está cerrado.');
        }

        $alumnos = Alumno::all();
        $materias = Materia::all();
        return view('calificaciones.edit', compact('calificacione', 'alumnos', 'materias'));
    }

    public function update(Request $request, Calificacion $calificacione)
    {
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor', 'maestro'])) {
            return abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $periodoActivo = PeriodoControl::where('trimestre', $calificacione->trimestre)->value('activo');
        if (!$periodoActivo && !Auth::user()->hasAnyRole(['administrador', 'coordinador'])) {
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
        if (!Auth::user()->hasAnyRole(['administrador', 'coordinador'])) {
            return abort(403, 'No tienes permiso para eliminar calificaciones.');
        }

        $calificacione->delete();
        return redirect()->route('calificaciones.index')->with('success', 'Calificación eliminada exitosamente.');
    }
}
