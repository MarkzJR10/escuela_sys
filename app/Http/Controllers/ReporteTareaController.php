<?php

namespace App\Http\Controllers;

use App\Models\ReporteTarea;
use App\Models\Alumno;
use App\Models\GradoGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteTareaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        $reportes = ReporteTarea::with(['alumno.gradoGrupo', 'usuario'])
            ->whereDate('fecha', $fecha)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reportes_tareas.index', compact('reportes', 'fecha'));
    }

    public function create(Request $request)
    {
        $alumno = Alumno::with('gradoGrupo')->findOrFail($request->query('alumno_id'));
        return view('reportes_tareas.create', compact('alumno'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'alumno_id'   => 'required|exists:alumnos,id',
            'materia'     => 'nullable|string|max:255',
            'descripcion' => 'required|string|max:500',
        ]);

        ReporteTarea::create([
            'alumno_id'   => $request->alumno_id,
            'user_id'     => Auth::id(),
            'fecha'       => now()->toDateString(),
            'materia'     => $request->materia,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('reportes_tareas.index')
            ->with('success', 'Reporte de tarea guardado exitosamente.');
    }

    public function show(ReporteTarea $reportes_tarea)
    {
        $reporte = $reportes_tarea;
        $reporte->load(['alumno.gradoGrupo', 'usuario']);

        if ($reporte->estatus === 'pendiente') {
            $reporte->update(['estatus' => 'leido']);
        }

        return view('reportes_tareas.show', compact('reporte'));
    }
}
