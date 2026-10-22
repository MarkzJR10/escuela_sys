<?php

namespace App\Http\Controllers;

use App\Models\Boleta;
use App\Models\Alumno;
use App\Models\GradoGrupo;
use Illuminate\Http\Request;

use App\Models\Materia;
use Barryvdh\DomPDF\Facade\Pdf;

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
     * Ver boleta de un alumno específico (boleta actual).
     */
    public function show($alumnoId)
    {
        $alumno = Alumno::with(['gradoGrupo.maestro'])->findOrFail($alumnoId);
        $boletaData = $alumno->getBoletaActualData();

        return view('boletas.show', compact('alumno', 'boletaData'));
    }

    /**
     * Ver histórico académico del alumno (Kardex escolar).
     */
    public function kardex($alumnoId)
    {
        $alumno = Alumno::with(['gradoGrupo.maestro'])->findOrFail($alumnoId);
        $kardexData = $alumno->getKardexData();
        $materiasCatalog = Materia::orderBy('nombre')->get();

        return view('boletas.kardex', compact('alumno', 'kardexData', 'materiasCatalog'));
    }

    /**
     * Descargar Kardex escolar en formato PDF.
     */
    public function kardexPdf($alumnoId)
    {
        $alumno = Alumno::with(['gradoGrupo.maestro'])->findOrFail($alumnoId);
        $kardexData = $alumno->getKardexData();

        $pdf = Pdf::loadView('boletas.kardex_pdf', compact('alumno', 'kardexData'));
        return $pdf->download('Kardex_' . $alumno->matricula . '.pdf');
    }

    /**
     * Registrar calificación histórica para un grado anterior en el Kardex.
     */
    public function storeHistorico(Request $request, $alumnoId)
    {
        $request->validate([
            'grado'    => 'required|string|max:50',
            'materia'  => 'required|string|max:255',
            'ciclo'    => 'nullable|string|max:50',
            'p1'       => 'nullable|numeric|min:0|max:10',
            'p2'       => 'nullable|numeric|min:0|max:10',
            'p3'       => 'nullable|numeric|min:0|max:10',
            'p_final'  => 'nullable|numeric|min:0|max:10',
        ]);

        $alumno = Alumno::findOrFail($alumnoId);

        $p1 = $request->filled('p1') ? floatval($request->p1) : null;
        $p2 = $request->filled('p2') ? floatval($request->p2) : null;
        $p3 = $request->filled('p3') ? floatval($request->p3) : null;

        $validScores = array_values(array_filter([$p1, $p2, $p3], fn($v) => !is_null($v)));
        $pFinal = $request->filled('p_final') 
            ? floatval($request->p_final) 
            : (count($validScores) > 0 ? round(array_sum($validScores) / count($validScores), 1) : null);

        Boleta::updateOrCreate(
            [
                'matricula' => $alumno->matricula,
                'grado'     => $request->grado,
                'materia'   => $request->materia,
            ],
            [
                'nombre'    => $alumno->nombre_completo,
                'grupo'     => $alumno->gradoGrupo ? $alumno->gradoGrupo->grupo : '',
                'ciclo'     => $request->ciclo ?: '',
                'p1'        => $p1,
                'p2'        => $p2,
                'p3'        => $p3,
                'p_final'   => $pFinal,
            ]
        );

        return redirect()->route('boletas.kardex', $alumno->id)
            ->with('success', 'Materia registrada exitosamente en el historial del grado.');
    }

    /**
     * Eliminar registro de materia histórica de boletas.
     */
    public function destroyHistorico($boletaId)
    {
        $boleta = Boleta::findOrFail($boletaId);
        $alumno = Alumno::where('matricula', $boleta->matricula)->first();
        $boleta->delete();

        $target = $alumno ? route('boletas.kardex', $alumno->id) : route('boletas.index');
        return redirect($target)->with('success', 'Registro histórico eliminado.');
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
