<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\GradoGrupo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materias = Materia::all();
        return view('materias.index', compact('materias'));
    }

    public function create()
    {
        $grados = GradoGrupo::distinct()->orderBy('grado')->pluck('grado');
        return view('materias.create', compact('grados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'grado' => 'required|string'
        ]);
        Materia::create($request->all());
        return redirect()->route('materias.index')->with('success', 'Materia creada exitosamente.');
    }

    public function show(Materia $materia)
    {
        return view('materias.show', compact('materia'));
    }

    public function edit(Materia $materia)
    {
        $grados = GradoGrupo::distinct()->orderBy('grado')->pluck('grado');
        return view('materias.edit', compact('materia', 'grados'));
    }

    public function update(Request $request, Materia $materia)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'grado' => 'required|string'
        ]);
        $materia->update($request->all());
        return redirect()->route('materias.index')->with('success', 'Materia actualizada exitosamente.');
    }

    public function destroy(Materia $materia)
    {
        $materia->delete();
        return redirect()->route('materias.index')->with('success', 'Materia eliminada exitosamente.');
    }
}
