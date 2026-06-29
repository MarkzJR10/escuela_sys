<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();

        // Padre: redirigir al portal
        if ($user->hasRole('padre')) {
            return redirect()->route('portal_padre.dashboard');
        }

        // Contadores base
        $alumnosCount = \App\Models\Alumno::where('activo', true)->count();
        $materiasCount = \App\Models\Materia::count();
        $gruposCount = \App\Models\GradoGrupo::count();
        $usersCount = \App\Models\User::count();

        // Datos financieros (admin, socio, cajero)
        $isFinance = $user->hasRole('administrador') || $user->hasRole('socio') || $user->hasRole('cajero');
        $adeudosVencidos = 0;
        $ingresosHoy = 0;
        $ticketsHoy = 0;

        if ($isFinance) {
            $adeudosVencidos = \App\Models\Adeudo::where('status', 'vencido')->count();
            $ingresosHoy = \App\Models\Pago::whereDate('fecha_pago', today())
                ->where('status', 'completado')
                ->sum('total');
            $ticketsHoy = \App\Models\Pago::whereDate('fecha_pago', today())
                ->where('status', 'completado')
                ->count();
        }

        // Datos académicos (maestro, coordinador)
        $isAcademic = $user->hasRole('administrador') || $user->hasRole('coordinador') || $user->hasRole('maestro');
        $conductaPendiente = 0;
        $profesoresCount = 0;

        if ($isAcademic) {
            $conductaPendiente = \App\Models\ReporteConducta::where('estatus', 'pendiente')->count();
            $profesoresCount = \App\Models\Profesor::count();
        }

        return view('home', compact(
            'alumnosCount', 'materiasCount', 'gruposCount', 'usersCount',
            'isFinance', 'adeudosVencidos', 'ingresosHoy', 'ticketsHoy',
            'isAcademic', 'conductaPendiente', 'profesoresCount'
        ));
    }
}
