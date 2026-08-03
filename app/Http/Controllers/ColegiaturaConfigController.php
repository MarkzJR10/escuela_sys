<?php

namespace App\Http\Controllers;

use App\Models\Colegiatura;
use Illuminate\Http\Request;

class ColegiaturaConfigController extends Controller
{
    public function index()
    {
        $colegiaturas = Colegiatura::all();
        return view('colegiaturas_config.index', compact('colegiaturas'));
    }

    public function create()
    {
        return view('colegiaturas_config.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:colegiaturas,nombre',
            'monto' => 'required|numeric|between:0,999999.99',
        ]);

        Colegiatura::create($request->all());

        return redirect()->route('colegiaturas-config.index')
            ->with('success', 'Colegiatura creada exitosamente.');
    }

    public function edit(Colegiatura $colegiaturas_config)
    {
        $colegiatura = $colegiaturas_config;
        return view('colegiaturas_config.edit', compact('colegiatura'));
    }

    public function update(Request $request, Colegiatura $colegiaturas_config)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:colegiaturas,nombre,' . $colegiaturas_config->id,
            'monto' => 'required|numeric|between:0,999999.99',
        ]);

        $colegiaturas_config->update($request->all());

        // Actualizar el monto numérico en los alumnos asociados para mantener consistencia
        foreach ($colegiaturas_config->alumnos as $alumno) {
            $alumno->update(['colegiatura' => $colegiaturas_config->monto]);
        }

        return redirect()->route('colegiaturas-config.index')
            ->with('success', 'Colegiatura actualizada exitosamente y montos de alumnos sincronizados.');
    }

    public function destroy(Colegiatura $colegiaturas_config)
    {
        $colegiaturas_config->delete();

        return redirect()->route('colegiaturas-config.index')
            ->with('success', 'Colegiatura eliminada exitosamente del catálogo.');
    }
}
