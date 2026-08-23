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
            'costo_reinscripcion' => Configuracion::get('costo_reinscripcion', '0'),
            'costo_papeleria' => Configuracion::get('costo_papeleria', '500.00'),
            'costo_seguro_escolar' => Configuracion::get('costo_seguro_escolar', '500.00'),
            'costo_cuota_limpieza' => Configuracion::get('costo_cuota_limpieza', '650.00'),
            'ciclo_actual' => Configuracion::get('ciclo_actual', date('Y') . '-' . (date('Y') + 1)),
        ];

        return view('configuraciones.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'costo_inscripcion' => 'required|numeric|min:0',
            'costo_reinscripcion' => 'required|numeric|min:0',
            'costo_papeleria' => 'required|numeric|min:0',
            'costo_seguro_escolar' => 'required|numeric|min:0',
            'costo_cuota_limpieza' => 'required|numeric|min:0',
            'ciclo_actual' => 'required|string|max:20',
        ]);

        Configuracion::set('costo_inscripcion', $request->costo_inscripcion, 'Costo de inscripción general');
        Configuracion::set('costo_reinscripcion', $request->costo_reinscripcion, 'Costo de reinscripción general');
        Configuracion::set('costo_papeleria', $request->costo_papeleria, 'Costo de papelería al inscribir');
        Configuracion::set('costo_seguro_escolar', $request->costo_seguro_escolar, 'Costo de seguro escolar al inscribir');
        Configuracion::set('costo_cuota_limpieza', $request->costo_cuota_limpieza, 'Costo de cuota de limpieza general al inscribir');
        Configuracion::set('ciclo_actual', $request->ciclo_actual, 'Ciclo escolar vigente para nuevos alumnos');

        return redirect()->route('configuraciones.index')->with('success', 'Configuración actualizada correctamente.');
    }
}
