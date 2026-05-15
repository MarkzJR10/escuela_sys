<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configs = [
            'costo_inscripcion' => Configuracion::get('costo_inscripcion', '0'),
            'ciclo_actual' => Configuracion::get('ciclo_actual', date('Y') . '-' . (date('Y') + 1)),
        ];

        return view('configuraciones.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'costo_inscripcion' => 'required|numeric|min:0',
            'ciclo_actual' => 'required|string|max:20',
        ]);

        Configuracion::set('costo_inscripcion', $request->costo_inscripcion, 'Costo de inscripción general');
        Configuracion::set('ciclo_actual', $request->ciclo_actual, 'Ciclo escolar vigente para nuevos alumnos');

        return redirect()->route('configuraciones.index')->with('success', 'Configuración actualizada correctamente.');
    }
}
