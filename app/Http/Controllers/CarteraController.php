<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\Request;

class CarteraController extends Controller
{
    /**
     * Display a listing of students with search.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $alumnos = Alumno::with('gradoGrupo')
            ->when($search, function($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellidos', 'like', "%{$search}%")
                      ->orWhere('matricula', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(20);

        return view('cartera.index', compact('alumnos'));
    }
}
