<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use App\Models\Alumno;
use App\Models\Boleta;
use App\Models\ReporteConducta;
use App\Models\Adeudo;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PortalPadreController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();

        if (!$padre) {
            return redirect('/home')->with('error', 'Su usuario no está vinculado a una cuenta de tutor.');
        }

        $hijos = $padre->alumnos()
            ->where('activo', true)
            ->where(function ($query) {
                $query->whereNull('estatus')
                      ->orWhereNotIn('estatus', ['baja', 'egresado']);
            })
            ->with('gradoGrupo')
            ->get();

        return view('portal_padre.dashboard', compact('padre', 'hijos'));
    }

    public function boleta(Alumno $alumno)
    {
        if (Configuracion::get('portal_padre_ver_boleta', '1') !== '1') {
            return redirect()->route('portal_padre.dashboard')->with('error', 'La opción de Boleta de Calificaciones no está disponible actualmente.');
        }

        // Verificar que el hijo pertenece al padre
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if (!$padre || $alumno->padre_id !== $padre->id) {
            abort(403, 'Acceso denegado: este alumno no está vinculado a su cuenta.');
        }

        $boletaData = $alumno->getBoletaActualData();
        return view('portal_padre.boleta', compact('alumno', 'boletaData'));
    }

    public function boletaPdf(Alumno $alumno)
    {
        if (Configuracion::get('portal_padre_ver_boleta', '1') !== '1') {
            return redirect()->route('portal_padre.dashboard')->with('error', 'La opción de Boleta de Calificaciones no está disponible actualmente.');
        }

        // Verificar que el hijo pertenece al padre
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if (!$padre || $alumno->padre_id !== $padre->id) {
            abort(403, 'Acceso denegado');
        }

        $pdfContent = \App\Services\BoletaPdfService::renderOfficialPdf($alumno);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Boleta_' . $alumno->matricula . '.pdf"',
        ]);
    }

    public function kardex(Alumno $alumno)
    {
        if (Configuracion::get('portal_padre_ver_kardex', '1') !== '1') {
            return redirect()->route('portal_padre.dashboard')->with('error', 'La opción de Historial Académico (Kardex) no está disponible actualmente.');
        }

        // Verificar que el hijo pertenece al padre
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if (!$padre || $alumno->padre_id !== $padre->id) {
            abort(403, 'Acceso denegado: este alumno no está vinculado a su cuenta.');
        }

        $kardexData = $alumno->getKardexData();
        return view('portal_padre.kardex', compact('alumno', 'kardexData'));
    }

    public function kardexPdf(Alumno $alumno)
    {
        if (Configuracion::get('portal_padre_ver_kardex', '1') !== '1') {
            return redirect()->route('portal_padre.dashboard')->with('error', 'La opción de Historial Académico (Kardex) no está disponible actualmente.');
        }

        // Verificar que el hijo pertenece al padre
        $user = Auth::user();
        $padre = Padre::where('user_id', $user->id)->first();
        if (!$padre || $alumno->padre_id !== $padre->id) {
            abort(403, 'Acceso denegado');
        }

        $kardexData = $alumno->getKardexData();
        $pdf = Pdf::loadView('boletas.kardex_pdf', compact('alumno', 'kardexData'));
        return $pdf->download('Kardex_'.$alumno->matricula.'.pdf');
    }

    public function conducta(Alumno $alumno)
    {
        if (Configuracion::get('portal_padre_ver_conducta', '1') !== '1') {
            return redirect()->route('portal_padre.dashboard')->with('error', 'La opción de Reportes de Conducta no está disponible actualmente.');
        }

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
        if (Configuracion::get('portal_padre_ver_estado_cuenta', '1') !== '1') {
            return redirect()->route('portal_padre.dashboard')->with('error', 'La opción de Estado de Cuenta no está disponible actualmente.');
        }

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
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('administrador');

        if (!$isAdmin && Configuracion::get('portal_padre_ver_recibos', '1') !== '1') {
            return redirect()->route('portal_padre.dashboard')->with('error', 'La opción de Recibos de Pago no está disponible actualmente.');
        }

        // Verificar que el hijo pertenece al padre (salvo que sea administrador)
        if (!$isAdmin) {
            $padre = Padre::where('user_id', $user->id)->first();
            if (!$padre || $alumno->padre_id !== $padre->id) {
                abort(403, 'Acceso denegado');
            }
        }

        $alumno->load('gradoGrupo');

        // Nomenclatura: matricula + grado + mes + año
        $matricula = $alumno->matricula ? $alumno->matricula : $alumno->id;
        $gradoRaw = strtoupper(trim($alumno->gradoGrupo->grado ?? ''));
        if ($gradoRaw === 'BK') {
            $gradoNum = 'B';
        } elseif (in_array($gradoRaw, ['N', 'K', 'P', 'B'])) {
            $gradoNum = $gradoRaw;
        } else {
            $gradoNum = substr(preg_replace('/[^0-9]/', '', $gradoRaw), 0, 1) ?: '1';
        }
        $mes = date('m');
        $anio = date('Y');

        $referencia = $matricula . $gradoNum . $mes . $anio;

        // Montos de pago (pronto pago vs pago regular)
        $montoPronto = $alumno->colegiatura ? floatval($alumno->colegiatura) : 2750;
        $montoRegular = round($montoPronto * 1.10);

        return view('portal_padre.recibo', compact('alumno', 'referencia', 'montoPronto', 'montoRegular'));
    }
}
