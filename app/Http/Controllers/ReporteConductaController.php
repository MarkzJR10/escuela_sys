<?php

namespace App\Http\Controllers;

use App\Models\ReporteConducta;
use App\Models\Alumno;
use App\Models\GradoGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteConductaController extends Controller
{
    /**
     * Listado de reportes de conducta por fecha.
     */
    public function index(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        $reportes = ReporteConducta::with(['alumno.gradoGrupo', 'usuario'])
            ->whereDate('fecha', $fecha)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reportes_conducta.index', compact('reportes', 'fecha'));
    }

    /**
     * Reportes de conducta pendientes (no leídos).
     */
    public function pendientes()
    {
        $reportes = ReporteConducta::with(['alumno.gradoGrupo', 'usuario'])
            ->where('estatus', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reportes_conducta.pendientes', compact('reportes'));
    }

    /**
     * Formulario de captura de reporte de conducta para un alumno.
     */
    public function create(Request $request)
    {
        $alumno = Alumno::with('gradoGrupo')->findOrFail($request->query('alumno_id'));
        $contadorReportes = ReporteConducta::where('alumno_id', $alumno->id)->count() + 1;

        return view('reportes_conducta.create', compact('alumno', 'contadorReportes'));
    }

    /**
     * Guardar un nuevo reporte de conducta.
     */
    public function store(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'otro' => 'nullable|string|max:500',
        ]);

        $alumno = Alumno::findOrFail($request->alumno_id);
        $noReporte = ReporteConducta::where('alumno_id', $alumno->id)->count() + 1;

        ReporteConducta::create([
            'alumno_id'  => $alumno->id,
            'user_id'    => Auth::id(),
            'no_reporte' => $noReporte,
            'fecha'      => now()->toDateString(),
            'razon1'     => $request->boolean('razon1'),
            'razon2'     => $request->boolean('razon2'),
            'razon3'     => $request->boolean('razon3'),
            'razon4'     => $request->boolean('razon4'),
            'razon5'     => $request->boolean('razon5'),
            'razon6'     => $request->boolean('razon6'),
            'razon7'     => $request->boolean('razon7'),
            'razon8'     => $request->boolean('razon8'),
            'razon9'     => $request->boolean('razon9'),
            'razon10'    => $request->boolean('razon10'),
            'razon11'    => $request->boolean('razon11'),
            'otro'       => $request->otro,
        ]);

        return redirect()->route('reportes_conducta.index')
            ->with('success', 'Reporte de conducta guardado exitosamente. Reporte #' . $noReporte);
    }

    /**
     * Ver detalle de un reporte individual.
     */
    public function show(ReporteConducta $reportes_conductum)
    {
        $reporte = $reportes_conductum;
        $reporte->load(['alumno.gradoGrupo', 'usuario']);

        // Marcar como leído
        if ($reporte->estatus === 'pendiente') {
            $reporte->update(['estatus' => 'leido']);
        }

        return view('reportes_conducta.show', compact('reporte'));
    }

    /**
     * Ver todos los reportes de un alumno específico.
     */
    public function porAlumno($alumnoId)
    {
        $alumno = Alumno::with('gradoGrupo')->findOrFail($alumnoId);
        $reportes = ReporteConducta::with('usuario')
            ->where('alumno_id', $alumnoId)
            ->orderBy('fecha', 'desc')
            ->get();

        return view('reportes_conducta.por_alumno', compact('alumno', 'reportes'));
    }

    /**
     * Seleccionar alumno para capturar reporte (buscar alumnos por grado).
     */
    public function seleccionarAlumno(Request $request)
    {
        $gradoGrupos = GradoGrupo::all();
        $alumnos = collect();

        if ($request->filled('grado_grupo_id')) {
            $alumnos = Alumno::withCount(['reportesConducta', 'reportesTareas'])
                ->where('grado_grupo_id', $request->grado_grupo_id)
                ->where('activo', true)
                ->orderBy('apellido_paterno')
                ->get();
        }

        return view('reportes_conducta.seleccionar_alumno', compact('gradoGrupos', 'alumnos'));
    }
}
