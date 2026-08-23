<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\Configuracion;
use App\Models\BitacoraEliminacionAdeudo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExtracurricularController extends Controller
{
    /**
     * Pantalla principal de Clases Extracurriculares
     */
    public function index(Request $request)
    {
        $cicloActual = Configuracion::get('ciclo_actual', date('Y') . '-' . (date('Y') + 1));
        $cicloSeleccionado = $request->input('ciclo', $cicloActual);

        // Generar opciones de ciclos
        $anioActual = (int) date('Y');
        $opcionesCiclo = [];
        for ($i = -2; $i <= 2; $i++) {
            $a = $anioActual + $i;
            $opcionesCiclo[] = $a . '-' . ($a + 1);
        }

        // Construir periodos pertenecientes al ciclo seleccionado (Sep-Jun)
        $partes = explode('-', $cicloSeleccionado);
        $periodosCiclo = [];
        if (count($partes) === 2) {
            $anio1 = $partes[0];
            $anio2 = $partes[1];
            for ($m = 9; $m <= 12; $m++) {
                $periodosCiclo[] = $anio1 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            }
            for ($m = 1; $m <= 6; $m++) {
                $periodosCiclo[] = $anio2 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            }
        }

        // Obtener adeudos exclusivamente del concepto CLASES EXTRACURRICULARES pertenecientes al ciclo seleccionado
        $query = Adeudo::where('concepto', 'LIKE', 'CLASES EXTRACURRICULARES%');
        if (!empty($periodosCiclo)) {
            $query->whereIn('periodo', $periodosCiclo);
        }

        $adeudos = $query->with(['alumno.gradoGrupo'])
            ->orderBy('id', 'desc')
            ->get();

        // Obtener lista completa de alumnos para configuración de costos y filtros
        $alumnos = Alumno::with('gradoGrupo')
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get();

        $totalGenerado = $adeudos->count();
        $totalPagados = $adeudos->where('status', 'pagado')->count();
        $totalPendientes = $adeudos->whereIn('status', ['pendiente', 'vencido', 'programado'])->count();
        $montoTotalPendiente = $adeudos->whereIn('status', ['pendiente', 'vencido', 'programado'])->sum('monto_actual');

        return view('extracurriculares.index', compact(
            'adeudos',
            'alumnos',
            'cicloSeleccionado',
            'opcionesCiclo',
            'cicloActual',
            'totalGenerado',
            'totalPagados',
            'totalPendientes',
            'montoTotalPendiente'
        ));
    }

    /**
     * Generar adeudos masivos de Clases Extracurriculares para el ciclo (Regla 1 y Regla 4)
     */
    public function generarAdeudos(Request $request)
    {
        $request->validate([
            'ciclo' => 'required|string|max:20',
            'monto_default' => 'nullable|numeric|min:0',
        ]);

        $ciclo = $request->input('ciclo');
        $montoDefault = (float) $request->input('monto_default', 0);

        $partes = explode('-', $ciclo);
        if (count($partes) !== 2) {
            return redirect()->back()->with('error', 'Formato de ciclo escolar inválido.');
        }

        $anio1 = (int) $partes[0];
        $anio2 = (int) $partes[1];

        // Regla 1: Todos los alumnos sin excepción que estén activos
        $alumnos = Alumno::where('activo', true)->get();

        if ($alumnos->isEmpty()) {
            return redirect()->back()->with('warning', 'No hay alumnos activos para generar adeudos.');
        }

        // Construir los 10 meses del ciclo (Sep-Jun)
        $meses = [];
        for ($m = 9; $m <= 12; $m++) {
            $meses[] = ['periodo' => $anio1 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT), 'mes' => $m, 'anio' => $anio1];
        }
        for ($m = 1; $m <= 6; $m++) {
            $meses[] = ['periodo' => $anio2 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT), 'mes' => $m, 'anio' => $anio2];
        }

        $insertados = 0;
        $omitidos = 0;

        DB::beginTransaction();
        try {
            foreach ($alumnos as $alumno) {
                // Determinar el monto de clase extracurricular para el alumno
                $montoAlumno = $alumno->monto_extracurricular > 0 ? $alumno->monto_extracurricular : $montoDefault;

                if ($montoAlumno <= 0) {
                    // Si no tiene monto asignado ni default, omitir
                    continue;
                }

                foreach ($meses as $item) {
                    $periodo = $item['periodo'];
                    $concepto = "CLASES EXTRACURRICULARES ($periodo)";

                    // Regla 4: Solo considerar aquellos que están pendientes / aún no existen
                    $existe = Adeudo::where('alumno_id', $alumno->id)
                        ->where('concepto', 'LIKE', "CLASES EXTRACURRICULARES ($periodo)%")
                        ->exists();

                    if (!$existe) {
                        Adeudo::create([
                            'alumno_id' => $alumno->id,
                            'tipo' => 'especial', // Sin recargos automáticos el día 11
                            'concepto' => $concepto,
                            'monto_base' => $montoAlumno,
                            'monto_actual' => $montoAlumno,
                            'periodo' => $periodo,
                            'status' => 'pendiente',
                        ]);
                        $insertados++;
                    } else {
                        $omitidos++;
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al generar adeudos de Clases Extracurriculares: ' . $e->getMessage());
        }

        return redirect()->route('extracurriculares.index', ['ciclo' => $ciclo])
            ->with('success', "Proceso completado. Se generaron {$insertados} adeudos de Clases Extracurriculares. ({$omitidos} ya existían y se mantuvieron intactos).");
    }

    /**
     * Actualizar la tarifa de clases extracurriculares por alumno (Regla 3),
     * realizar barrido de adeudos pendientes y actualizar montos de hoy hacia adelante.
     */
    public function updateMontoAlumno(Request $request, Alumno $alumno)
    {
        $request->validate([
            'monto_extracurricular' => 'required|numeric|min:0',
            'ciclo' => 'nullable|string|max:20',
        ]);

        $ciclo = $request->input('ciclo', Configuracion::get('ciclo_actual', '2026-2027'));
        $montoAnterior = (float) $alumno->monto_extracurricular;
        $montoNuevo = (float) $request->input('monto_extracurricular');

        // 1. Actualizar la tarifa asignada al alumno
        $alumno->update(['monto_extracurricular' => $montoNuevo]);

        // Construir los 10 meses del ciclo escolar (Sep-Jun)
        $partes = explode('-', $ciclo);
        $periodosCiclo = [];
        if (count($partes) === 2) {
            $anio1 = (int) $partes[0];
            $anio2 = (int) $partes[1];
            for ($m = 9; $m <= 12; $m++) {
                $periodosCiclo[] = $anio1 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            }
            for ($m = 1; $m <= 6; $m++) {
                $periodosCiclo[] = $anio2 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            }
        }

        $mesActual = now()->format('Y-m');
        $mesesRecreados = [];
        $mesesActualizados = [];

        DB::transaction(function () use ($alumno, $montoNuevo, $periodosCiclo, $mesActual, &$mesesRecreados, &$mesesActualizados) {
            foreach ($periodosCiclo as $periodo) {
                $concepto = "CLASES EXTRACURRICULARES ($periodo)";

                $adeudoExistente = Adeudo::where('alumno_id', $alumno->id)
                    ->where('concepto', 'LIKE', "CLASES EXTRACURRICULARES ($periodo)%")
                    ->first();

                if (!$adeudoExistente) {
                    // Barrido: Si fue borrado previamente y el monto nuevo es > 0, se vuelve a generar
                    if ($montoNuevo > 0) {
                        Adeudo::create([
                            'alumno_id' => $alumno->id,
                            'tipo' => 'especial',
                            'concepto' => $concepto,
                            'monto_base' => $montoNuevo,
                            'monto_actual' => $montoNuevo,
                            'periodo' => $periodo,
                            'status' => 'pendiente',
                        ]);
                        $mesesRecreados[] = $periodo;
                    }
                } else {
                    // Modificar de hoy hacia adelante (periodo >= mesActual) en adeudos no pagados
                    if ($adeudoExistente->status !== 'pagado' && $periodo >= $mesActual) {
                        $adeudoExistente->update([
                            'monto_base' => $montoNuevo,
                            'monto_actual' => $montoNuevo,
                        ]);
                        $mesesActualizados[] = $periodo;
                    }
                }
            }
        });

        // 2. Auditoría en Bitácora (Quién lo hizo y qué hizo)
        $mesesAfectadosTotales = array_unique(array_merge($mesesRecreados, $mesesActualizados));
        sort($mesesAfectadosTotales);
        $mesesAfectadosTexto = !empty($mesesAfectadosTotales) ? implode(', ', $mesesAfectadosTotales) : 'General';

        BitacoraEliminacionAdeudo::create([
            'user_id' => Auth::id(),
            'alumno_id' => $alumno->id,
            'matricula' => $alumno->matricula,
            'nombre_alumno' => trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}"),
            'ciclo' => $ciclo,
            'accion' => 'Ajuste de Tarifa y Recálculo Extracurricular',
            'monto_anterior' => $montoAnterior,
            'monto_eliminado' => 0,
            'monto_nuevo' => $montoNuevo,
            'meses_afectados' => $mesesAfectadosTexto,
            'total_registros_eliminados' => count($mesesAfectadosTotales),
        ]);

        return redirect()->route('extracurriculares.index', ['ciclo' => $ciclo])
            ->with('success', "Costo actualizado a $" . number_format($montoNuevo, 2) . " para {$alumno->nombre} {$alumno->apellido_paterno}. Se realizó el barrido de pendientes y actualización de adeudos de hoy en adelante (Meses: {$mesesAfectadosTexto}).");
    }

    /**
     * Asignación masiva de tarifa de clases extracurriculares a alumnos
     */
    public function actualizarMontosMasivo(Request $request)
    {
        $request->validate([
            'monto_general' => 'required|numeric|min:0',
            'ciclo' => 'nullable|string|max:20',
        ]);

        $ciclo = $request->input('ciclo', Configuracion::get('ciclo_actual', '2026-2027'));
        $montoGeneral = (float) $request->input('monto_general');

        $alumnos = Alumno::where('activo', true)->get();
        $afectados = 0;

        foreach ($alumnos as $alumno) {
            $req = new Request([
                'monto_extracurricular' => $montoGeneral,
                'ciclo' => $ciclo
            ]);
            $this->updateMontoAlumno($req, $alumno);
            $afectados++;
        }

        return redirect()->route('extracurriculares.index', ['ciclo' => $ciclo])
            ->with('success', "Se asignó la tarifa de $" . number_format($montoGeneral, 2) . " a {$afectados} alumnos realizando el barrido de adeudos.");
    }

    /**
     * Cancelar / Quitar un adeudo individual
     */
    public function cancelarIndividual(Adeudo $adeudo)
    {
        if ($adeudo->status === 'pagado') {
            return redirect()->back()->with('error', 'No se puede cancelar un adeudo que ya fue pagado.');
        }

        $nombreAlumno = optional($adeudo->alumno)->nombre . ' ' . optional($adeudo->alumno)->apellido_paterno;
        $monto = $adeudo->monto_actual;
        $concepto = $adeudo->concepto;

        // Registrar auditoría
        BitacoraEliminacionAdeudo::create([
            'user_id' => Auth::id(),
            'alumno_id' => $adeudo->alumno_id,
            'matricula' => optional($adeudo->alumno)->matricula ?? 'N/A',
            'nombre_alumno' => $nombreAlumno ?: 'N/A',
            'ciclo' => $adeudo->periodo ?: 'Extracurricular',
            'monto_anterior' => $monto,
            'monto_eliminado' => $monto,
            'monto_nuevo' => 0,
            'meses_afectados' => $concepto,
            'total_registros_eliminados' => 1,
        ]);

        $adeudo->delete();

        return redirect()->back()->with('success', "Adeudo '{$concepto}' eliminado exitosamente para {$nombreAlumno}.");
    }

    /**
     * Refrescar / Recalcular el monto de un adeudo individual no pagado con el costo actual asignado al alumno
     */
    public function refrescarIndividual(Adeudo $adeudo)
    {
        if ($adeudo->status === 'pagado') {
            return redirect()->back()->with('error', 'No se puede refrescar un adeudo que ya fue pagado.');
        }

        $alumno = $adeudo->alumno;
        if (!$alumno) {
            return redirect()->back()->with('error', 'Alumno no encontrado.');
        }

        $nuevoMonto = $alumno->monto_extracurricular;

        $adeudo->update([
            'monto_base' => $nuevoMonto,
            'monto_actual' => $nuevoMonto,
        ]);

        return redirect()->back()->with('success', "Adeudo de {$alumno->nombre} {$alumno->apellido_paterno} actualizado al monto vigente de $" . number_format($nuevoMonto, 2) . ".");
    }

    /**
     * Quitar adeudos masivamente o por alumno (Regla 2)
     */
    public function eliminarMasivo(Request $request)
    {
        $request->validate([
            'ciclo' => 'required|string|max:20',
            'alumno_id' => 'nullable|exists:alumnos,id',
        ]);

        $ciclo = $request->input('ciclo');
        $alumnoId = $request->input('alumno_id');

        $query = Adeudo::where('concepto', 'LIKE', 'CLASES EXTRACURRICULARES%')
            ->whereIn('status', ['pendiente', 'vencido', 'programado'])
            ->doesntHave('pagosDetalles');

        if (!empty($alumnoId)) {
            $query->where('alumno_id', $alumnoId);
        }

        $adeudosEliminar = $query->with('alumno')->get();
        $eliminados = 0;

        if ($adeudosEliminar->isNotEmpty()) {
            DB::transaction(function () use ($adeudosEliminar, $ciclo, &$eliminados) {
                $porAlumno = $adeudosEliminar->groupBy('alumno_id');

                foreach ($porAlumno as $idAl => $adeudosGrupo) {
                    $alumnoObj = $adeudosGrupo->first()->alumno ?: Alumno::find($idAl);
                    $montoEliminado = (float) $adeudosGrupo->sum('monto_actual');
                    $mesesAfectados = $adeudosGrupo->pluck('periodo')->filter()->unique()->values()->implode(', ');

                    $montoAnteriorAlumno = (float) Adeudo::where('alumno_id', $idAl)->whereIn('status', ['pendiente', 'vencido', 'programado'])->sum('monto_actual');
                    $montoNuevoAlumno = max(0, $montoAnteriorAlumno - $montoEliminado);

                    BitacoraEliminacionAdeudo::create([
                        'user_id' => Auth::id(),
                        'alumno_id' => $idAl,
                        'matricula' => $alumnoObj ? $alumnoObj->matricula : 'N/A',
                        'nombre_alumno' => $alumnoObj ? trim("{$alumnoObj->apellido_paterno} {$alumnoObj->apellido_materno} {$alumnoObj->nombre}") : 'N/A',
                        'ciclo' => $ciclo,
                        'monto_anterior' => $montoAnteriorAlumno,
                        'monto_eliminado' => $montoEliminado,
                        'monto_nuevo' => $montoNuevoAlumno,
                        'meses_afectados' => $mesesAfectados ?: 'CLASES EXTRACURRICULARES',
                        'total_registros_eliminados' => $adeudosGrupo->count(),
                    ]);
                }

                $eliminados = Adeudo::whereIn('id', $adeudosEliminar->pluck('id'))->delete();
            });
        }

        return redirect()->back()->with('success', "Se eliminaron {$eliminados} adeudos pendientes de Clases Extracurriculares.");
    }

    /**
     * Simulación de prueba para un alumno de prueba
     */
    public function simularPrueba(Request $request)
    {
        $alumno = Alumno::first();
        if (!$alumno) {
            return redirect()->back()->with('error', 'No hay ningún alumno registrado en la base de datos para simular.');
        }

        // Asignar tarifa de prueba $450.00
        $montoPrueba = 450.00;
        $alumno->update(['monto_extracurricular' => $montoPrueba]);

        $periodoPrueba = now()->format('Y-m');
        $conceptoPrueba = "CLASES EXTRACURRICULARES ($periodoPrueba)";

        $adeudo = Adeudo::create([
            'alumno_id' => $alumno->id,
            'tipo' => 'especial',
            'concepto' => $conceptoPrueba,
            'monto_base' => $montoPrueba,
            'monto_actual' => $montoPrueba,
            'periodo' => $periodoPrueba,
            'status' => 'pendiente',
        ]);

        return redirect()->route('extracurriculares.index')
            ->with('success', "SIMULACIÓN EXITOSA: Se asignó tarifa de $" . number_format($montoPrueba, 2) . " y se creó un adeudo de prueba '{$conceptoPrueba}' para el alumno {$alumno->nombre} {$alumno->apellido_paterno} (Matrícula: {$alumno->matricula}).");
    }
}
