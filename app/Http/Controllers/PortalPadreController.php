<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use App\Models\Alumno;
use App\Models\Boleta;
use App\Models\ReporteConducta;
use App\Models\Adeudo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalPadreController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();

        if (!$padre) {
            return redirect('/home')->with('error', 'Su usuario no está vinculado a una cuenta de tutor.');
        }

        $hijos = $padre->alumnos()->with('gradoGrupo')->get();

        return view('portal_padre.dashboard', compact('padre', 'hijos'));
    }

    public function boleta(Alumno $alumno)
    {
        // Verificar que el hijo pertenece al padre
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if ($alumno->padre_id !== $padre->id) {
            abort(403, 'Acceso denegado');
        }

        $boletas = Boleta::where('alumno_id', $alumno->id)->get();
        return view('portal_padre.boleta', compact('alumno', 'boletas'));
    }

    public function conducta(Alumno $alumno)
    {
        // Verificar
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if ($alumno->padre_id !== $padre->id) {
            abort(403);
        }

        $reportes = ReporteConducta::where('alumno_id', $alumno->id)->orderBy('fecha', 'desc')->get();
        return view('portal_padre.conducta', compact('alumno', 'reportes'));
    }

    public function estadoCuenta(Alumno $alumno)
    {
        // Verificar
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if ($alumno->padre_id !== $padre->id) {
            abort(403);
        }

        $adeudos = Adeudo::where('alumno_id', $alumno->id)
            ->whereIn('status', ['pendiente', 'vencido'])
            ->orderBy('periodo', 'asc')
            ->get();

        $colegiaturas = $adeudos->where('tipo', 'colegiatura');
        $especiales = $adeudos->where('tipo', 'especial');
        $ventas = $adeudos->where('tipo', 'venta');

        $totalAdeudo = $adeudos->sum('monto_calculado');

        return view('portal_padre.estado_cuenta', compact('alumno', 'colegiaturas', 'especiales', 'ventas', 'totalAdeudo'));
    }

    public function recibo(Alumno $alumno)
    {
        // Verificar que el hijo pertenece al padre
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if (!$padre || $alumno->padre_id !== $padre->id) {
            abort(403, 'Acceso denegado');
        }

        // Generar o tomar la referencia del alumno
        $referencia = $alumno->matricula ? $alumno->matricula : ('2677' . str_pad($alumno->id, 8, '0', STR_PAD_LEFT));

        // Montos de pago (pronto pago vs pago regular)
        $montoPronto = $alumno->colegiatura ? floatval($alumno->colegiatura) : 2750;
        $montoRegular = round($montoPronto * 1.10);

        return view('portal_padre.recibo', compact('alumno', 'referencia', 'montoPronto', 'montoRegular'));
    }
}
