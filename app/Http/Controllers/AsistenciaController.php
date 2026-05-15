<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Alumno;
use App\Models\GradoGrupo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $gradoGrupos = GradoGrupo::all();
        $fecha = $request->fecha ?? date('Y-m-d');
        $grado_grupo_id = $request->grado_grupo_id;
        
        $alumnos = [];
        if ($grado_grupo_id) {
            $alumnos = Alumno::where('grado_grupo_id', $grado_grupo_id)->get();
            
            // Cargar asistencias existentes para esa fecha y grupo si las hay
            foreach ($alumnos as $alumno) {
                $asistenciaExistente = Asistencia::where('alumno_id', $alumno->id)
                    ->where('fecha', $fecha)
                    ->first();
                $alumno->asistencia_estado = $asistenciaExistente ? $asistenciaExistente->estado : 'Presente';
            }
        }

        return view('asistencias.index', compact('gradoGrupos', 'alumnos', 'fecha', 'grado_grupo_id'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('asistencias.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'asistencias' => 'required|array',
            'asistencias.*' => 'required|in:Presente,Ausente,Retardo,Justificado'
        ]);

        foreach ($request->asistencias as $alumno_id => $estado) {
            Asistencia::updateOrCreate(
                [
                    'alumno_id' => $alumno_id,
                    'fecha' => $request->fecha
                ],
                [
                    'estado' => $estado
                ]
            );
        }

        return redirect()->route('asistencias.index', [
                'fecha' => $request->fecha, 
                'grado_grupo_id' => $request->grado_grupo_id
            ])->with('success', 'Asistencias guardadas correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asistencia $asistencia)
    {
        return redirect()->route('asistencias.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asistencia $asistencia)
    {
        return redirect()->route('asistencias.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asistencia $asistencia)
    {
        return redirect()->route('asistencias.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asistencia $asistencia)
    {
        return redirect()->route('asistencias.index');
    }
}
