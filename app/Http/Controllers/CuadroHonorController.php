<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\PeriodoControl;
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
}
