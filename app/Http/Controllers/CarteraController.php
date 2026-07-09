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
        $alumnos = Alumno::with('gradoGrupo')
            ->orderBy('nombre')
            ->get();

        return view('cartera.index', compact('alumnos'));
    }
}
