<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CicloController extends Controller
{
    /**
     * Vista principal — seleccionar ciclo y ver adeudos registrados.
     */
    public function index(Request $request)
    {
        $cicloActual = Configuracion::get('ciclo_actual', date('Y') . '-' . (date('Y') + 1));
        $cicloSeleccionado = $request->input('ciclo', '');

        $adeudos = collect();
        if ($cicloSeleccionado) {
            $adeudos = Adeudo::where('tipo', 'colegiatura')
                ->whereHas('alumno', function ($q) {
                    $q->where('activo', true);
                })
                ->where(function ($q) use ($cicloSeleccionado) {
                    // Filtrar adeudos que corresponden a los meses del ciclo seleccionado
                    $partes = explode('-', $cicloSeleccionado);
                    if (count($partes) === 2) {
                        $anio1 = $partes[0]; // ej. "2025"
                        $anio2 = $partes[1]; // ej. "2026"

                        $q->where(function ($sub) use ($anio1) {
                            // Sep-Dic del primer año
                            for ($m = 9; $m <= 12; $m++) {
                                $sub->orWhere('periodo', $anio1 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT));
                            }
                        })->orWhere(function ($sub) use ($anio2) {
                            // Ene-Jun del segundo año
                            for ($m = 1; $m <= 6; $m++) {
                                $sub->orWhere('periodo', $anio2 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT));
                            }
                        });
                    }
                })
                ->with('alumno.gradoGrupo')
                ->orderBy('alumno_id')
                ->orderBy('periodo')
                ->get();
        }

        // Generar opciones de ciclo dinámicamente
        $anioActual = (int) date('Y');
        $opciones = [];
        for ($i = -2; $i <= 2; $i++) {
            $a = $anioActual + $i;
            $opciones[] = $a . '-' . ($a + 1);
        }

        return view('ciclos.index', compact('cicloSeleccionado', 'adeudos', 'opciones', 'cicloActual'));
    }

    /**
     * Registrar adeudos masivos de colegiatura para todos los alumnos activos.
     *
     * Crea un adeudo por mes del ciclo escolar (Sep-Dic, Ene-Jun) para cada
     * alumno activo que tenga un monto de colegiatura definido,
     * solo si el adeudo no existe ya.
     */
    public function registrarMasivo(Request $request)
    {
        $request->validate([
            'ciclo' => 'required|string|max:20',
        ]);

        $ciclo = $request->input('ciclo');
        $partes = explode('-', $ciclo);

        if (count($partes) !== 2) {
            return redirect()->back()->with('error', 'Formato de ciclo inválido.');
        }

        $anio1 = (int) $partes[0]; // ej. 2025
        $anio2 = (int) $partes[1]; // ej. 2026

        // Obtener alumnos activos que tengan colegiatura definida
        $alumnos = Alumno::where('activo', true)
            ->whereNotNull('colegiatura')
            ->where('colegiatura', '>', 0)
            ->get();

        if ($alumnos->isEmpty()) {
            return redirect()->back()->with('warning', 'No hay alumnos activos con colegiatura definida.');
        }

        // Construir los meses del ciclo: Sep-Dic del año 1, Ene-Jun del año 2
        $meses = [];
        for ($m = 9; $m <= 12; $m++) {
            $meses[] = $anio1 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
        }
        for ($m = 1; $m <= 6; $m++) {
            $meses[] = $anio2 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
        }

        $insertados = 0;
        $omitidos = 0;

        DB::beginTransaction();
        try {
            foreach ($alumnos as $alumno) {
                foreach ($meses as $periodo) {
                    // Verificar que no exista ya
                    $existe = Adeudo::where('alumno_id', $alumno->id)
                        ->where('tipo', 'colegiatura')
                        ->where('periodo', $periodo)
                        ->exists();

                    if (!$existe) {
                        Adeudo::create([
                            'alumno_id'   => $alumno->id,
                            'tipo'        => 'colegiatura',
                            'concepto'    => null, // se genera dinámicamente con el accessor
                            'monto_base'  => $alumno->colegiatura,
                            'monto_actual' => $alumno->colegiatura,
                            'periodo'     => $periodo,
                            'status'      => 'pendiente',
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
            return redirect()->back()->with('error', 'Error al registrar ciclo: ' . $e->getMessage());
        }

        return redirect()->route('ciclos.index', ['ciclo' => $ciclo])
            ->with('success', "Ciclo registrado exitosamente. Se crearon {$insertados} adeudos. ({$omitidos} ya existían y se omitieron).");
    }
}
