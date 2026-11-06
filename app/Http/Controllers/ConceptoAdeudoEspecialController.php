<?php

namespace App\Http\Controllers;

use App\Models\ConceptoAdeudoEspecial;
use Illuminate\Http\Request;

class ConceptoAdeudoEspecialController extends Controller
{
    public function index()
    {
        $conceptos = ConceptoAdeudoEspecial::orderBy('nombre')->get();
        return view('conceptos_adeudos_especiales.index', compact('conceptos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:conceptos_adeudos_especiales,nombre',
        ], [
            'nombre.required' => 'El nombre del concepto es obligatorio.',
            'nombre.unique' => 'Este concepto ya se encuentra dado de alta.',
        ]);

        ConceptoAdeudoEspecial::create([
            'nombre' => trim($request->nombre),
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('conceptos_adeudos_especiales.index')
            ->with('success', 'Concepto de adeudo especial dado de alta correctamente.');
    }

    public function toggle(ConceptoAdeudoEspecial $conceptoAdeudoEspecial)
    {
        $conceptoAdeudoEspecial->activo = !$conceptoAdeudoEspecial->activo;
        $conceptoAdeudoEspecial->save();

        return response()->json([
            'success' => true,
            'activo' => (bool) $conceptoAdeudoEspecial->activo,
        ]);
    }
}

