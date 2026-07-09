<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use Illuminate\Http\Request;

class ColegiaturaController extends Controller
{
    public function index(Request $request)
    {
        $alumnos = Alumno::with('gradoGrupo')
                         ->orderByRaw('colegiatura IS NULL DESC')
                         ->orderBy('nombre')
                         ->get();

        return view('colegiaturas.index', compact('alumnos'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $request->validate([
            'colegiatura' => 'nullable|numeric|between:0,999999.99',
        ]);

        $alumno->update([
            'colegiatura' => $request->colegiatura
        ]);

        return redirect()->back()->with('success', 'Monto de colegiatura actualizado.');
    }

    public function adeudos(Alumno $alumno)
    {
        $adeudos = $alumno->adeudos()->orderBy('periodo', 'desc')->get();
        return view('adeudos.index', compact('alumno', 'adeudos'));
    }
}
