<?php

namespace App\Http\Controllers;

use App\Models\Boleta;
use App\Models\Alumno;
use App\Models\GradoGrupo;
use Illuminate\Http\Request;

class BoletaController extends Controller
{
    /**
     * Vista principal de boletas — buscar por grado/grupo.
     */
    public function index(Request $request)
    {
        $gradoGrupos = GradoGrupo::all();
        $alumnos = collect();

        if ($request->filled('grado_grupo_id')) {
            $alumnos = Alumno::where('grado_grupo_id', $request->grado_grupo_id)
                ->where('activo', true)
                ->orderBy('apellido_paterno')
                ->get();
        }

        return view('boletas.index', compact('gradoGrupos', 'alumnos'));
    }

    /**
     * Ver boleta de un alumno específico.
     */
    public function show($alumnoId)
    {
        $alumno = Alumno::with('gradoGrupo')->findOrFail($alumnoId);
        $boletas = Boleta::where('matricula', $alumno->matricula)
            ->orderBy('materia')
            ->get();

        return view('boletas.show', compact('alumno', 'boletas'));
    }

    /**
     * Editar boleta (calificaciones).
     */
    public function edit($alumnoId)
    {
        $alumno = Alumno::with('gradoGrupo')->findOrFail($alumnoId);
        $boletas = Boleta::where('matricula', $alumno->matricula)
            ->orderBy('materia')
            ->get();

        return view('boletas.edit', compact('alumno', 'boletas'));
    }

    /**
     * Actualizar calificaciones de boleta.
     */
    public function update(Request $request, $alumnoId)
    {
        $alumno = Alumno::findOrFail($alumnoId);

        if ($request->has('boletas')) {
            foreach ($request->boletas as $boletaId => $data) {
                $boleta = Boleta::findOrFail($boletaId);
                $boleta->update([
                    'p1'      => $data['p1'] ?? null,
                    'p2'      => $data['p2'] ?? null,
                    'p3'      => $data['p3'] ?? null,
                    'p_final' => $data['p_final'] ?? null,
                ]);
            }
        }

        return redirect()->route('boletas.show', $alumno->id)
            ->with('success', 'Boleta actualizada exitosamente.');
    }

    /**
     * Gestionar boletas — crear materias para un alumno.
     */
    public function gestionar($alumnoId)
    {
        $alumno = Alumno::with('gradoGrupo')->findOrFail($alumnoId);
        $boletasExistentes = Boleta::where('matricula', $alumno->matricula)->pluck('materia')->toArray();

        return view('boletas.gestionar', compact('alumno', 'boletasExistentes'));
    }

    /**
     * Crear materias en boleta.
     */
    public function storeMateria(Request $request, $alumnoId)
    {
        $request->validate([
            'materia' => 'required|string|max:255',
        ]);

        $alumno = Alumno::with('gradoGrupo')->findOrFail($alumnoId);
        $grado = $alumno->gradoGrupo;

        Boleta::create([
            'matricula' => $alumno->matricula,
            'nombre'    => $alumno->nombre . ' ' . $alumno->apellido_paterno . ' ' . $alumno->apellido_materno,
            'grado'     => $grado ? $grado->grado : null,
            'grupo'     => $grado ? $grado->grupo : null,
            'materia'   => $request->materia,
        ]);

        return redirect()->route('boletas.gestionar', $alumno->id)
            ->with('success', 'Materia agregada a la boleta.');
    }
}
