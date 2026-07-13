<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\Alumno;
use App\Models\GradoGrupo;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteCobranzaController extends Controller
{
    /**
     * Reporte de cobranza general — muestra el saldo total de cada alumno.
     */
    public function index()
    {
        // Totales generales
        $totalColegiaturas = Adeudo::where('tipo', 'colegiatura')
            ->whereIn('status', ['pendiente', 'vencido'])
            ->get()
            ->sum('monto_calculado');

        $totalEspeciales = Adeudo::where('tipo', 'especial')
            ->whereIn('status', ['pendiente', 'vencido'])
            ->get()
            ->sum('monto_calculado');

        $totalVentasCredito = Adeudo::where('tipo', 'venta')
            ->whereIn('status', ['pendiente', 'vencido'])
            ->get()
            ->sum('monto_calculado');

        $totalGeneral = $totalColegiaturas + $totalEspeciales + $totalVentasCredito;

        // Detalle por alumno
        $alumnos = Alumno::with(['gradoGrupo', 'adeudos'])
            ->where('activo', true)
            ->orderBy('apellido_paterno')
            ->get()
            ->map(function ($alumno) {
                $alumno->colegiaturas_pendientes = $alumno->adeudos
                    ->where('tipo', 'colegiatura')
                    ->whereIn('status', ['pendiente', 'vencido'])
                    ->sum('monto_calculado');
                
                $alumno->adeudos_especiales = $alumno->adeudos
                    ->where('tipo', 'especial')
                    ->whereIn('status', ['pendiente', 'vencido'])
                    ->sum('monto_calculado');

                $alumno->creditos = $alumno->adeudos
                    ->where('tipo', 'venta')
                    ->whereIn('status', ['pendiente', 'vencido'])
                    ->sum('monto_calculado');

                $alumno->saldo_total = $alumno->colegiaturas_pendientes 
                    + $alumno->adeudos_especiales 
                    + $alumno->creditos;

                return $alumno;
            });

        return view('reportes.cobranza', compact(
            'totalColegiaturas', 'totalEspeciales', 'totalVentasCredito', 
            'totalGeneral', 'alumnos'
        ));
    }

    /**
     * Pendientes por cobrar por mes.
     */
    public function pendientesPorMes(Request $request)
    {
        $mes = $request->input('mes', now()->format('Y-m'));

        $adeudos = Adeudo::with('alumno.gradoGrupo')
            ->whereIn('status', ['pendiente', 'vencido'])
            ->where('periodo', $mes)
            ->orderBy('alumno_id')
            ->get();

        $total = $adeudos->sum('monto_calculado');

        return view('reportes.pendientes_mes', compact('adeudos', 'mes', 'total'));
    }

    /**
     * Estados de cuenta por padre.
     */
    public function estadoCuenta(Request $request)
    {
        $alumnos = collect();
        $busqueda = $request->input('busqueda', '');

        if ($request->filled('busqueda')) {
            $alumnos = Alumno::with(['gradoGrupo', 'padre', 'adeudos'])
                ->where('activo', true)
                ->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                      ->orWhere('matricula', 'like', "%{$busqueda}%");
                })
                ->get();
        }

        return view('reportes.estado_cuenta', compact('alumnos', 'busqueda'));
    }

    /**
     * Detalle de adeudos de un alumno específico.
     */
    public function detalleAlumno($alumnoId)
    {
        $alumno = Alumno::with(['gradoGrupo', 'padre'])->findOrFail($alumnoId);

        $adeudos = $alumno->adeudos()
            ->whereIn('status', ['pendiente', 'vencido'])
            ->orderBy('periodo', 'asc')
            ->get();

        $colegiaturas = $adeudos->where('tipo', 'colegiatura');
        $especiales = $adeudos->where('tipo', 'especial');
        $ventas = $adeudos->where('tipo', 'venta');

        $totalAdeudo = $adeudos->sum('monto_actual');

        $pagosRecientes = Pago::where('alumno_id', $alumnoId)
            ->with('detalles.adeudo')
            ->orderBy('fecha_pago', 'desc')
            ->take(20)
            ->get();

        return view('reportes.detalle_alumno', compact(
            'alumno', 'colegiaturas', 'especiales', 'ventas', 
            'totalAdeudo', 'pagosRecientes'
        ));
    }

    /**
     * Historial de colegiaturas.
     */
    public function historialColegiaturas(Request $request)
    {
        $gradoGrupos = GradoGrupo::all();
        $adeudos = collect();

        if ($request->filled('grado_grupo_id')) {
            $alumnoIds = Alumno::where('grado_grupo_id', $request->grado_grupo_id)->pluck('id');
            $adeudos = Adeudo::with('alumno')
                ->whereIn('alumno_id', $alumnoIds)
                ->where('tipo', 'colegiatura')
                ->orderBy('periodo', 'desc')
                ->get();
        }

        return view('reportes.historial_colegiaturas', compact('gradoGrupos', 'adeudos'));
    }
}
