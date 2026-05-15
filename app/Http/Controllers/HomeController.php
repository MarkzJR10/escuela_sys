<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $alumnosCount = \App\Models\Alumno::count();
        $materiasCount = \App\Models\Materia::count();
        $gruposCount = \App\Models\GradoGrupo::count();
        $usersCount = \App\Models\User::count();

        return view('home', compact('alumnosCount', 'materiasCount', 'gruposCount', 'usersCount'));
    }
}
