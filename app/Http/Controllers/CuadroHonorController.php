<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\PeriodoControl;
use App\Models\Configuracion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CuadroHonorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('administrador');

        // Determinar trimestres disponibles según el rol
        if ($isAdmin) {
            $trimestres = PeriodoControl::orderBy('trimestre')->get();
        } else {
            $trimestres = PeriodoControl::where('activo', true)->orderBy('trimestre')->get();
        }

        // Determinar trimestre seleccionado
        $selectedTrimestreId = $request->input('trimestre');
        if (!$selectedTrimestreId) {
            $primerDisponible = $trimestres->first();
            $selectedTrimestreId = $primerDisponible ? $primerDisponible->trimestre : null;
        }

        $cuadroDeHonor = [];

        if ($selectedTrimestreId) {
            // Cargar alumnos con sus calificaciones de ese trimestre y su grupo
            $alumnos = Alumno::with(['gradoGrupo', 'calificaciones' => function ($q) use ($selectedTrimestreId) {
                $q->where('trimestre', $selectedTrimestreId)->with('materia');
            }])
            ->whereHas('calificaciones', function ($q) use ($selectedTrimestreId) {
                $q->where('trimestre', $selectedTrimestreId);
            })
            ->get();

            // Calcular promedios y agrupar
            $agrupados = $alumnos->groupBy(function ($alumno) {
                return $alumno->gradoGrupo ? "Grado {$alumno->gradoGrupo->grado} - Grupo {$alumno->gradoGrupo->grupo}" : "Sin Grupo Asignado";
            });

            foreach ($agrupados as $nombreGrupo => $alumnosGrupo) {
                $procesados = $alumnosGrupo->map(function ($alumno) {
                    $calificaciones = $alumno->calificaciones;
                    $sumPromedio = 0;
                    $countPromedio = 0;
                    $conductaScore = 0.0;

                    foreach ($calificaciones as $calif) {
                        $materiaNombre = strtolower(trim($calif->materia->nombre ?? ''));
                        if (str_contains($materiaNombre, 'conducta') || str_contains($materiaNombre, 'comportamiento')) {
                            $conductaScore = (float)$calif->puntaje;
                        } else {
                            $sumPromedio += (float)$calif->puntaje;
                            $countPromedio++;
                        }
                    }

                    $promedio = $countPromedio > 0 ? ($sumPromedio / $countPromedio) : 0;

                    $alumno->promedio_calculado = round($promedio, 2);
                    $alumno->conducta_calculada = round($conductaScore, 2);
                    
                    return $alumno;
                });

                // Ordenar por promedio descendente, y en caso de empate por conducta descendente
                $sorted = $procesados->sortByDesc(function ($alumno) {
                    return sprintf('%06.2f|%06.2f', $alumno->promedio_calculado, $alumno->conducta_calculada);
                })->take(5)->values();

                $cuadroDeHonor[$nombreGrupo] = $sorted;
            }
            
            // Ordenar los grupos alfabéticamente
            ksort($cuadroDeHonor);
        }

        return view('cuadro_honor.index', compact('trimestres', 'selectedTrimestreId', 'cuadroDeHonor', 'isAdmin'));
    }

    /**
     * Genera un diploma PDF en formato landscape (horizontal) para el alumno.
     */
    public function generarDiploma(Request $request, Alumno $alumno)
    {
        $trimestre = (int) $request->input('trimestre');
        $lugar = (int) $request->input('lugar');

        // Validar parámetros básicos
        if (!$trimestre || $trimestre < 1 || $trimestre > 3) {
            abort(404, 'Trimestre inválido.');
        }

        if (!$lugar || $lugar < 1 || $lugar > 5) {
            abort(404, 'Lugar inválido.');
        }

        // Cargar calificaciones del trimestre seleccionado
        $alumno->load(['gradoGrupo', 'calificaciones' => function ($q) use ($trimestre) {
            $q->where('trimestre', $trimestre)->with('materia');
        }]);

        // Re-calcular promedio y conducta para este alumno en este trimestre
        $calificaciones = $alumno->calificaciones;
        $sumPromedio = 0;
        $countPromedio = 0;

        foreach ($calificaciones as $calif) {
            $materiaNombre = strtolower(trim($calif->materia->nombre ?? ''));
            // Excluimos conducta de promedio general
            if (!str_contains($materiaNombre, 'conducta') && !str_contains($materiaNombre, 'comportamiento')) {
                $sumPromedio += (float)$calif->puntaje;
                $countPromedio++;
            }
        }

        $promedio = $countPromedio > 0 ? ($sumPromedio / $countPromedio) : 0;
        $promedioFormateado = number_format($promedio, 2);

        // Traducir número de lugar a texto ordinal en español
        $lugaresTexto = [
            1 => 'PRIMER LUGAR',
            2 => 'SEGUNDO LUGAR',
            3 => 'TERCER LUGAR',
            4 => 'CUARTO LUGAR',
            5 => 'QUINTO LUGAR',
        ];
        $lugarTexto = $lugaresTexto[$lugar] ?? '';

        // Obtener ciclo escolar actual
        $cicloEscolar = Configuracion::get('ciclo_actual', date('Y') . '-' . (date('Y') + 1));

        // Grado y grupo
        $grado = $alumno->gradoGrupo->grado ?? '';
        $grupo = $alumno->gradoGrupo->grupo ?? '';

        // Cargar la vista y generar el PDF
        $pdf = Pdf::loadView('cuadro_honor.diploma_pdf', compact(
            'alumno',
            'trimestre',
            'lugarTexto',
            'promedioFormateado',
            'cicloEscolar',
            'grado',
            'grupo'
        ));

        // Configurar orientación horizontal y tamaño de papel
        $pdf->setPaper('a4', 'landscape');

        // Retornar en modo stream para abrir en una pestaña nueva
        $nombreArchivo = 'Diploma_' . str_replace(' ', '_', $alumno->nombre) . '_' . $alumno->matricula . '.pdf';
        return $pdf->stream($nombreArchivo);
    }
}
