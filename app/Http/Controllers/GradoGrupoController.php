<?php

namespace App\Http\Controllers;

use App\Models\GradoGrupo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GradoGrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gradoGrupos = GradoGrupo::withCount('alumnos')->with('alumnos')->get();
        return view('grado_grupos.index', compact('gradoGrupos'));
    }

    public function create()
    {
        return view('grado_grupos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'grado' => 'required|string|max:255',
            'grupo' => 'required|string|max:255',
        ]);
        GradoGrupo::create($request->all());
        return redirect()->route('grado_grupos.index')->with('success', 'Grado/Grupo creado exitosamente.');
    }

    public function show(GradoGrupo $gradoGrupo)
    {
        return view('grado_grupos.show', compact('gradoGrupo'));
    }

    public function edit(GradoGrupo $gradoGrupo)
    {
        return view('grado_grupos.edit', compact('gradoGrupo'));
    }

    public function update(Request $request, GradoGrupo $gradoGrupo)
    {
        $request->validate([
            'grado' => 'required|string|max:255',
            'grupo' => 'required|string|max:255',
        ]);
        $gradoGrupo->update($request->all());
        return redirect()->route('grado_grupos.index')->with('success', 'Grado/Grupo actualizado exitosamente.');
    }

    public function destroy(GradoGrupo $gradoGrupo)
    {
        $gradoGrupo->delete();
        return redirect()->route('grado_grupos.index')->with('success', 'Grado/Grupo eliminado exitosamente.');
    }
}
