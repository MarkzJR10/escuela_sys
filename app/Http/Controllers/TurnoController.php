<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    public function index()
    {
        $turnos = Turno::all();
        return view('turnos.index', compact('turnos'));
    }

    public function create()
    {
        return view('turnos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        Turno::create($request->all());

        return redirect()->route('turnos.index')->with('success', 'Turno creado exitosamente.');
    }

    public function edit(Turno $turno)
    {
        return view('turnos.edit', compact('turno'));
    }

    public function update(Request $request, Turno $turno)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $turno->update($request->all());

        return redirect()->route('turnos.index')->with('success', 'Turno actualizado exitosamente.');
    }

    public function destroy(Turno $turno)
    {
        $turno->delete();
        return redirect()->route('turnos.index')->with('success', 'Turno eliminado exitosamente.');
    }
}
