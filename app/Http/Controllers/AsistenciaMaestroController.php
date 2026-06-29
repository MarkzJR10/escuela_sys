<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaMaestro;
use App\Models\Profesor;
use Illuminate\Http\Request;

class AsistenciaMaestroController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        
        $profesores = Profesor::orderBy('apellido_paterno')->get();
        $asistencias = AsistenciaMaestro::whereDate('fecha', $fecha)->get()->keyBy('profesor_id');

        return view('asistencia_maestros.index', compact('profesores', 'asistencias', 'fecha'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'asistencias' => 'array',
        ]);

        $fecha = $request->fecha;

        if ($request->has('asistencias')) {
            foreach ($request->asistencias as $profesor_id => $data) {
                AsistenciaMaestro::updateOrCreate(
                    ['profesor_id' => $profesor_id, 'fecha' => $fecha],
                    [
                        'estado' => $data['estado'],
                        'hora_entrada' => $data['hora_entrada'] ?? null,
                        'hora_salida' => $data['hora_salida'] ?? null,
                        'observaciones' => $data['observaciones'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('asistencia_maestros.index', ['fecha' => $fecha])
            ->with('success', 'Asistencia de maestros guardada exitosamente.');
    }
}
