<?php

namespace App\Http\Controllers;

use App\Models\SatConcepto;
use Illuminate\Http\Request;

class SatConceptoController extends Controller
{
    public function index()
    {
        $conceptos = SatConcepto::all();
        return view('sat_conceptos.index', compact('conceptos'));
    }

    public function create()
    {
        return view('sat_conceptos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'clave' => 'required|string',
            'descripcion' => 'required|string',
        ]);

        SatConcepto::create($request->all());

        return redirect()->route('sat_conceptos.index')->with('success', 'Concepto SAT creado exitosamente.');
    }

    public function edit(SatConcepto $satConcepto)
    {
        return view('sat_conceptos.edit', compact('satConcepto'));
    }

    public function update(Request $request, SatConcepto $satConcepto)
    {
        $request->validate([
            'clave' => 'required|string',
            'descripcion' => 'required|string',
        ]);

        $satConcepto->update($request->all());

        return redirect()->route('sat_conceptos.index')->with('success', 'Concepto SAT actualizado exitosamente.');
    }

    public function destroy(SatConcepto $satConcepto)
    {
        $satConcepto->delete();
        return redirect()->route('sat_conceptos.index')->with('success', 'Concepto SAT eliminado exitosamente.');
    }
}
