<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use Illuminate\Http\Request;

class ColegiaturaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $query = Alumno::with('gradoGrupo');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellido_paterno', 'LIKE', "%{$search}%")
                  ->orWhere('apellido_materno', 'LIKE', "%{$search}%");
            });
        }

        // Ordenamos: nulos primero, luego por nombre
        $alumnos = $query->orderByRaw('colegiatura IS NULL DESC')
                         ->orderBy('nombre')
                         ->paginate(15);

        return view('colegiaturas.index', compact('alumnos', 'search'));
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
