<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use Illuminate\Http\Request;

class ColegiaturaController extends Controller
{
    public function index(Request $request)
    {
        $alumnos = Alumno::with(['gradoGrupo', 'colegiaturaBase'])
                         ->orderByRaw('colegiatura IS NULL DESC')
                         ->orderBy('nombre')
                         ->get();
        $colegiaturas = \App\Models\Colegiatura::all();

        return view('colegiaturas.index', compact('alumnos', 'colegiaturas'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $request->validate([
            'colegiatura_id' => 'nullable|exists:colegiaturas,id',
            'colegiatura' => 'nullable|numeric|between:0,999999.99',
        ]);

        $data = [];
        if ($request->filled('colegiatura_id')) {
            $colegiaturaBase = \App\Models\Colegiatura::find($request->colegiatura_id);
            if ($colegiaturaBase) {
                $data['colegiatura_id'] = $colegiaturaBase->id;
                $data['colegiatura'] = $colegiaturaBase->monto;
            }
        } else {
            $data['colegiatura_id'] = null;
            $data['colegiatura'] = $request->colegiatura;
        }

        $alumno->update($data);

        return redirect()->back()->with('success', 'Monto de colegiatura actualizado.');
    }

    public function adeudos(Alumno $alumno)
    {
        $adeudos = $alumno->adeudos()->orderBy('periodo', 'desc')->get();
        return view('adeudos.index', compact('alumno', 'adeudos'));
    }
}
