<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\PagoInsuficiente;
use App\Models\ReporteConducta;
use App\Exports\SaldosExport;
use App\Imports\PagosImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ComplementosController extends Controller
{
    /**
     * Exportar la tabla de deudores a Excel
     */
    public function exportarSaldosExcel()
    {
        return Excel::download(new SaldosExport, 'saldos_alumnos_'.now()->format('Y_m_d').'.xlsx');
    }

    /**
     * Conducta Destacada (Alumnos sin reportes en el mes actual)
     */
    public function conductaDestacada(Request $request)
    {
        $mes = $request->input('mes', now()->format('Y-m'));

        // Obtener IDs de alumnos que SÍ tienen reportes este mes
        $alumnosConReportes = ReporteConducta::whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$mes])
            ->pluck('alumno_id')->unique();

        // Alumnos destacados son aquellos activos que NO están en la lista anterior
        $destacados = Alumno::where('activo', true)
            ->whereNotIn('id', $alumnosConReportes)
            ->with('gradoGrupo')
            ->orderBy('apellido_paterno')
            ->get();

        return view('complementos.conducta_destacada', compact('destacados', 'mes'));
    }

    /**
     * Generar PDF Boleta
     */
    public function generarBoletaPdf($alumnoId)
    {
        $alumno = Alumno::with(['gradoGrupo.maestro', 'boletas'])->findOrFail($alumnoId);
        
        $pdf = Pdf::loadView('boletas.pdf', compact('alumno'));
        return $pdf->download('Boleta_'.$alumno->matricula.'.pdf');
    }

    /**
     * Lista Asistencia Imprimible PDF
     */
    public function imprimirListaAsistencia(Request $request)
    {
        $grado_grupo_id = $request->input('grado_grupo_id');
        if(!$grado_grupo_id) {
            return redirect()->back()->with('error', 'Seleccione un grado.');
        }

        $alumnos = Alumno::where('grado_grupo_id', $grado_grupo_id)->where('activo', true)->orderBy('apellido_paterno')->get();
        $grupo = \App\Models\GradoGrupo::find($grado_grupo_id);

        $pdf = Pdf::loadView('asistencias.lista_pdf', compact('alumnos', 'grupo'));
        // Formato horizontal
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Lista_Asistencia_'.$grupo->grado.'_'.$grupo->grupo.'.pdf');
    }

    /**
     * Mostrar vista para cargar pagos en Excel
     */
    public function showImportarPagos()
    {
        $preview = session('import_pagos_preview', null);
        return view('complementos.importar_pagos', compact('preview'));
    }

    /**
     * Descargar archivo de ejemplo/plantilla para la importación de pagos (CSV)
     */
    public function descargarEjemploPagos()
    {
        $csvHeader = "\xEF\xBB\xBF" . "Fecha de pago,Referencia,Referencia Leyenda,Abono\n"
            . now()->format('Y-m-d') . ",202411092026,PAGO COLEG SEPTIEMBRE,500.00\n"
            . now()->format('Y-m-d') . ",202422092026,PAGO COLEG SEPTIEMBRE,1250.00\n"
            . now()->format('Y-m-d') . ",202431092026,PAGO INSUFICIENTE COLEG,250.00\n";
        
        return response($csvHeader, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_ejemplo_importar_pagos.csv"',
        ]);
    }

    /**
     * Procesar el archivo subido y generar vista previa del resumen (Paso 1)
     */
    public function procesarImportarPagos(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        try {
            $rows = Excel::toArray([], $request->file('archivo_excel'));
            
            if (empty($rows) || empty($rows[0])) {
                return redirect()->back()->with('error', 'El archivo no contiene filas procesables.');
            }

            $sheet = $rows[0];
            $header = array_map(function($h) {
                return strtolower(trim(str_replace([' ', '_', '-'], '', $h)));
            }, $sheet[0]);

            // Mapear posiciones de encabezados
            $colFecha = $this->findHeaderIndex($header, ['fechadepago', 'fechapago', 'fecha']);
            $colRef = $this->findHeaderIndex($header, ['referencia', 'ref']);
            $colLey = $this->findHeaderIndex($header, ['referencialeyenda', 'referenciadeleyenda', 'leyenda']);
            $colAbono = $this->findHeaderIndex($header, ['abono', 'monto', 'cantidad']);

            $completos = [];
            $insuficientes = [];
            $errores = [];

            for ($i = 1; $i < count($sheet); $i++) {
                $row = $sheet[$i];
                if (empty(array_filter($row))) continue; // Omitir filas vacías

                $valFecha = $colFecha !== null && isset($row[$colFecha]) ? $row[$colFecha] : null;
                $valRef = $colRef !== null && isset($row[$colRef]) ? trim((string)$row[$colRef]) : '';
                $valLey = $colLey !== null && isset($row[$colLey]) ? trim((string)$row[$colLey]) : '';
                $valAbono = $colAbono !== null && isset($row[$colAbono]) ? $row[$colAbono] : 0;

                $fechaPago = $this->parseFecha($valFecha);
                $rawAbono = preg_replace('/[^\d.]/', '', str_replace(',', '', (string)$valAbono));
                $abono = (float) $rawAbono;

                // Buscar nomenclatura de 12 caracteres
                $nomenclatura = $this->extraerNomenclatura12($valRef, $valLey);

                if (!$nomenclatura) {
                    $errores[] = [
                        'fila' => $i + 1,
                        'referencia' => $valRef,
                        'referencia_leyenda' => $valLey,
                        'abono' => $abono,
                        'fecha_pago' => $fechaPago,
                        'motivo' => 'No se encontró nomenclatura válida de 12 caracteres'
                    ];
                    continue;
                }

                $mat = $nomenclatura['matricula'];
                $gra = $nomenclatura['grado'];
                $periodo = $nomenclatura['periodo'];

                // Buscar Alumno por matrícula
                $alumno = Alumno::with('gradoGrupo')->where('matricula', $mat)->first();
                if (!$alumno) {
                    $alumno = Alumno::with('gradoGrupo')->where('matricula', 'LIKE', "%{$mat}%")->first();
                }

                if (!$alumno) {
                    $errores[] = [
                        'fila' => $i + 1,
                        'nomenclatura' => $nomenclatura['code'],
                        'matricula' => $mat,
                        'referencia' => $valRef,
                        'referencia_leyenda' => $valLey,
                        'abono' => $abono,
                        'fecha_pago' => $fechaPago,
                        'motivo' => "Alumno no encontrado con matrícula {$mat}"
                    ];
                    continue;
                }

                // Buscar Adeudo por alumno y periodo
                $adeudo = Adeudo::where('alumno_id', $alumno->id)
                    ->where('periodo', $periodo)
                    ->whereIn('status', ['pendiente', 'vencido', 'programado'])
                    ->first();

                if (!$adeudo) {
                    // Si no coincide exacto el periodo, buscar el adeudo pendiente más antiguo del alumno
                    $adeudo = Adeudo::where('alumno_id', $alumno->id)
                        ->whereIn('status', ['pendiente', 'vencido'])
                        ->orderBy('periodo', 'asc')
                        ->first();
                }

                if (!$adeudo) {
                    $errores[] = [
                        'fila' => $i + 1,
                        'nomenclatura' => $nomenclatura['code'],
                        'matricula' => $alumno->matricula,
                        'alumno_nombre' => $alumno->nombre_completo,
                        'referencia' => $valRef,
                        'referencia_leyenda' => $valLey,
                        'abono' => $abono,
                        'fecha_pago' => $fechaPago,
                        'motivo' => "El alumno no tiene adeudos pendientes para el periodo {$periodo}"
                    ];
                    continue;
                }

                $montoDebido = $this->calcularMontoAdeudoAFecha($adeudo, $fechaPago);
                $diferencia = $montoDebido - $abono;

                $registro = [
                    'alumno_id' => $alumno->id,
                    'adeudo_id' => $adeudo->id,
                    'matricula' => $alumno->matricula,
                    'alumno_nombre' => $alumno->nombre_completo,
                    'grado' => $alumno->gradoGrupo->grado ?? $gra,
                    'periodo' => $adeudo->periodo ?? $periodo,
                    'fecha_pago' => $fechaPago,
                    'referencia' => $valRef,
                    'referencia_leyenda' => $valLey,
                    'monto_abonado' => $abono,
                    'monto_debido' => $montoDebido,
                    'diferencia' => $diferencia > 0 ? $diferencia : 0,
                    'concepto' => $adeudo->concepto,
                ];

                if ($abono >= $montoDebido) {
                    $completos[] = $registro;
                } else {
                    $insuficientes[] = $registro;
                }
            }

            $previewData = [
                'completos' => $completos,
                'insuficientes' => $insuficientes,
                'errores' => $errores,
                'total_filas' => count($sheet) - 1,
            ];

            session(['import_pagos_preview' => $previewData]);

            return redirect()->route('complementos.importar_pagos')->with('info', 'Vista previa procesada. Por favor revisa el resumen antes de confirmar.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar y ejecutar la importación en Base de Datos (Paso 2)
     */
    public function confirmarImportarPagos(Request $request)
    {
        $preview = session('import_pagos_preview', null);

        if (!$preview) {
            return redirect()->route('complementos.importar_pagos')->with('error', 'No hay ninguna vista previa activa para confirmar.');
        }

        $completos = $preview['completos'] ?? [];
        $insuficientes = $preview['insuficientes'] ?? [];

        try {
            DB::transaction(function () use ($completos, $insuficientes) {
                // 1. Aplicar pagos completos
                foreach ($completos as $item) {
                    $pago = Pago::create([
                        'alumno_id' => $item['alumno_id'],
                        'user_id' => Auth::id() ?? 1,
                        'total' => $item['monto_abonado'],
                        'metodo_pago' => 'transferencia',
                        'referencia_ticket' => !empty($item['referencia']) ? $item['referencia'] : ('EXCEL-' . strtoupper(uniqid())),
                        'fecha_pago' => $item['fecha_pago'],
                        'status' => 'completado'
                    ]);

                    PagoDetalle::create([
                        'pago_id' => $pago->id,
                        'adeudo_id' => $item['adeudo_id'],
                        'concepto' => $item['concepto'] ?? 'Colegiatura Masiva Excel',
                        'monto_pagado' => $item['monto_abonado'],
                    ]);

                    $adeudo = Adeudo::find($item['adeudo_id']);
                    if ($adeudo) {
                        $adeudo->update([
                            'status' => 'pagado',
                            'fecha_pago' => $item['fecha_pago'],
                        ]);
                    }
                }

                // 2. Registrar pagos insuficientes en el reporte
                foreach ($insuficientes as $item) {
                    PagoInsuficiente::create([
                        'alumno_id' => $item['alumno_id'],
                        'matricula' => $item['matricula'],
                        'alumno_nombre' => $item['alumno_nombre'],
                        'grado' => $item['grado'],
                        'periodo' => $item['periodo'],
                        'fecha_pago' => $item['fecha_pago'],
                        'referencia' => $item['referencia'],
                        'referencia_leyenda' => $item['referencia_leyenda'],
                        'monto_abonado' => $item['monto_abonado'],
                        'monto_debido' => $item['monto_debido'],
                        'diferencia' => $item['diferencia'],
                        'user_id' => Auth::id() ?? 1,
                    ]);
                }
            });

            session()->forget('import_pagos_preview');

            $countComp = count($completos);
            $countInsuf = count($insuficientes);

            return redirect()->route('complementos.importar_pagos')->with(
                'success', 
                "Operación exitosa: Se registraron {$countComp} pagos completos y {$countInsuf} registros fueron enviados al Reporte de Saldo Insuficiente."
            );

        } catch (\Exception $e) {
            return redirect()->route('complementos.importar_pagos')->with('error', 'Error durante la aplicación de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Auxiliar para extraer nomenclatura de 12 caracteres
     * Estructura: 5 chars matricula, 1 char grado, 2 chars mes, 4 chars año
     */
    private function extraerNomenclatura12($ref, $ley)
    {
        $text = $this->formatCellString($ref) . ' ' . $this->formatCellString($ley);
        
        // 1. Buscar coincidencia de 12 caracteres directamente en el texto
        preg_match_all('/[A-Za-z0-9]{12}/', $text, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $code) {
                $parsed = $this->validarYConstruirNomenclatura($code);
                if ($parsed) return $parsed;
            }
        }

        // 2. Buscar en el texto sin caracteres especiales
        $cleanText = preg_replace('/[^A-Za-z0-9]/', '', $text);
        if (strlen($cleanText) >= 12) {
            preg_match_all('/[A-Za-z0-9]{12}/', $cleanText, $cleanMatches);
            if (!empty($cleanMatches[0])) {
                foreach ($cleanMatches[0] as $code) {
                    $parsed = $this->validarYConstruirNomenclatura($code);
                    if ($parsed) return $parsed;
                }
            }
        }

        return null;
    }

    private function validarYConstruirNomenclatura($code)
    {
        if (strlen($code) !== 12) return null;

        $mat = substr($code, 0, 5);
        $gra = substr($code, 5, 1);
        $mes = substr($code, 6, 2);
        $anio = substr($code, 8, 4);

        if (is_numeric($mes) && (int)$mes >= 1 && (int)$mes <= 12 && is_numeric($anio) && (int)$anio >= 2000 && (int)$anio <= 2100) {
            $mesPad = str_pad($mes, 2, '0', STR_PAD_LEFT);
            return [
                'code' => $code,
                'matricula' => $mat,
                'grado' => $gra,
                'mes' => $mesPad,
                'anio' => $anio,
                'periodo' => "{$anio}-{$mesPad}"
            ];
        }

        return null;
    }

    private function formatCellString($val)
    {
        if (is_null($val)) return '';
        if (is_numeric($val)) {
            return sprintf('%.0f', $val);
        }
        return trim((string)$val);
    }

    /**
     * Calcula el monto del adeudo aplicando reglas de recargo a la fecha de pago especificada
     */
    private function calcularMontoAdeudoAFecha($adeudo, $fechaPago)
    {
        if (!$adeudo) return 0;
        if ($adeudo->tipo !== 'colegiatura') {
            return (float) $adeudo->monto_actual;
        }

        try {
            $fecha = Carbon::parse($fechaPago);
        } catch (\Exception $e) {
            $fecha = Carbon::now();
        }

        $periodoPago = $fecha->format('Y-m');
        $periodoAdeudo = $adeudo->periodo;

        // Si el adeudo corresponde al mismo mes/año que la fecha de pago
        if ($periodoAdeudo === $periodoPago) {
            if ($fecha->day <= 10) {
                return (float) $adeudo->monto_base;
            } else {
                // Día 11 en adelante: recargo del 10%
                return (float) ($adeudo->monto_base * 1.10);
            }
        }

        // Si el periodo del adeudo ya venció con respecto a la fecha de pago
        if ($periodoAdeudo < $periodoPago) {
            $fechaAdeudo = Carbon::parse($periodoAdeudo . '-01');
            $fechaCorte = Carbon::parse($periodoPago . '-01');
            $mesesTranscurridos = (int) $fechaAdeudo->diffInMonths($fechaCorte);

            $recargos = 1 + $mesesTranscurridos;
            return (float) ($adeudo->monto_base + ($adeudo->monto_base * 0.10 * $recargos));
        }

        return (float) $adeudo->monto_base;
    }

    private function findHeaderIndex(array $headers, array $candidates)
    {
        foreach ($candidates as $cand) {
            $idx = array_search($cand, $headers);
            if ($idx !== false) return $idx;
        }
        return null;
    }

    private function parseFecha($val)
    {
        if (empty($val)) return now()->toDateString();

        if (is_numeric($val)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
            } catch (\Exception $e) {
                return now()->toDateString();
            }
        }

        $str = trim((string)$val);

        // Soporte para DD/MM/YYYY o DD-MM-YYYY (ej: 10/10/2026 o 25-09-2026)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $m)) {
            $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $anio = $m[3];
            return "{$anio}-{$mes}-{$dia}";
        }

        // Soporte para YYYY-MM-DD o YYYY/MM/DD (ej: 2026-10-10)
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $str, $m)) {
            $anio = $m[1];
            $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $dia = str_pad($m[3], 2, '0', STR_PAD_LEFT);
            return "{$anio}-{$mes}-{$dia}";
        }

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->toDateString();
        }
    }
}
