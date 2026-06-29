<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\ReporteConducta;
use App\Exports\SaldosExport;
use App\Imports\PagosImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $alumno = Alumno::with(['gradoGrupo', 'boletas'])->findOrFail($alumnoId);
        
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
        return view('complementos.importar_pagos');
    }

    /**
     * Procesar la importación
     */
    public function procesarImportarPagos(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            Excel::import(new PagosImport, $request->file('archivo_excel'));
            return redirect()->back()->with('success', 'Pagos importados y procesados exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al procesar archivo: ' . $e->getMessage());
        }
    }
}

