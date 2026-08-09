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
            $adeudos = Adeudo::where(function ($q) use ($cicloSeleccionado) {
                    $q->where(function ($subQ) use ($cicloSeleccionado) {
                        $subQ->where('tipo', 'colegiatura')
                             ->where(function ($monthQ) use ($cicloSeleccionado) {
                                 $partes = explode('-', $cicloSeleccionado);
                                 if (count($partes) === 2) {
                                     $anio1 = $partes[0]; // ej. "2025"
                                     $anio2 = $partes[1]; // ej. "2026"

                                     $monthQ->where(function ($sub) use ($anio1) {
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
                             });
                    })
                    ->orWhere(function ($subQ) use ($cicloSeleccionado) {
                        $subQ->where('tipo', 'especial')
                             ->where('concepto', 'Reinscripción ' . $cicloSeleccionado);
                    });
                })
                ->with('alumno.gradoGrupo')
                ->orderBy('alumno_id')
                ->orderByRaw("FIELD(tipo, 'especial', 'colegiatura')")
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

        // Lista de todos los alumnos para los filtros
        $alumnosLista = Alumno::orderBy('apellido_paterno')->orderBy('nombre')->get();

        return view('ciclos.index', compact('cicloSeleccionado', 'adeudos', 'opciones', 'cicloActual', 'alumnosLista'));
    }

    /**
     * Registrar adeudos masivos de colegiatura para alumnos activos con estatus regular.
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

        // NO generar adeudos a alumnos en baja ni egresados
        $alumnos = Alumno::where('activo', true)
            ->where('estatus', 'regular')
            ->whereNotNull('colegiatura')
            ->where('colegiatura', '>', 0)
            ->get();

        if ($alumnos->isEmpty()) {
            return redirect()->back()->with('warning', 'No hay alumnos activos regulares con colegiatura definida.');
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

    /**
     * Registrar adeudos masivos de reinscripción para alumnos activos con estatus regular.
     */
    public function registrarReinscripcionMasivo(Request $request)
    {
        $request->validate([
            'ciclo' => 'required|string|max:20',
        ]);

        $ciclo = $request->input('ciclo');
        $costoReinscripcion = (float) Configuracion::get('costo_reinscripcion', 0);

        if ($costoReinscripcion <= 0) {
            return redirect()->back()->with('error', 'El costo de reinscripción no está configurado o es menor/igual a cero. Configúrelo en la sección de Configuración General.');
        }

        // NO generar a alumnos baja o egresados
        $alumnos = Alumno::where('activo', true)
            ->where('estatus', 'regular')
            ->get();

        if ($alumnos->isEmpty()) {
            return redirect()->back()->with('warning', 'No hay alumnos activos regulares para registrar reinscripción.');
        }

        $insertados = 0;
        $omitidos = 0;
        $concepto = "Reinscripción $ciclo";

        DB::beginTransaction();
        try {
            foreach ($alumnos as $alumno) {
                // Verificar que no exista ya para este alumno y este ciclo
                $existe = Adeudo::where('alumno_id', $alumno->id)
                    ->where('tipo', 'especial')
                    ->where('concepto', $concepto)
                    ->exists();

                if (!$existe) {
                    Adeudo::create([
                        'alumno_id'   => $alumno->id,
                        'tipo'        => 'especial',
                        'concepto'    => $concepto,
                        'monto_base'  => $costoReinscripcion,
                        'monto_actual' => $costoReinscripcion,
                        'status'      => 'pendiente',
                    ]);
                    $insertados++;
                } else {
                    $omitidos++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar reinscripciones: ' . $e->getMessage());
        }

        return redirect()->route('ciclos.index', ['ciclo' => $ciclo])
            ->with('success', "Adeudos de reinscripción registrados exitosamente. Se crearon {$insertados} adeudos. ({$omitidos} ya existían y se omitieron).");
    }

    /**
     * Eliminar adeudos masivamente de un ciclo con opciones de filtrado.
     */
    public function eliminarMasivo(Request $request)
    {
        $request->validate([
            'ciclo'          => 'required|string|max:20',
            'estatus_alumno' => 'nullable|string|in:todos,regular,baja,egresado',
            'alumno_id'      => 'nullable|exists:alumnos,id',
            'tipo_adeudo'    => 'nullable|string|in:todos,colegiatura,especial',
        ]);

        $ciclo = $request->input('ciclo');
        $estatusAlumno = $request->input('estatus_alumno', 'todos');
        $alumnoId = $request->input('alumno_id');
        $tipoAdeudo = $request->input('tipo_adeudo', 'todos');

        $query = Adeudo::whereIn('status', ['pendiente', 'vencido', 'programado'])
            ->where(function ($q) use ($ciclo, $tipoAdeudo) {
                if ($tipoAdeudo === 'todos' || $tipoAdeudo === 'colegiatura') {
                    $q->where(function ($subQ) use ($ciclo) {
                        $subQ->where('tipo', 'colegiatura')
                             ->where(function ($monthQ) use ($ciclo) {
                                 $partes = explode('-', $ciclo);
                                 if (count($partes) === 2) {
                                     $anio1 = $partes[0];
                                     $anio2 = $partes[1];
                                     $monthQ->where(function ($sub) use ($anio1) {
                                         for ($m = 9; $m <= 12; $m++) {
                                             $sub->orWhere('periodo', $anio1 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT));
                                         }
                                     })->orWhere(function ($sub) use ($anio2) {
                                         for ($m = 1; $m <= 6; $m++) {
                                             $sub->orWhere('periodo', $anio2 . '-' . str_pad($m, 2, '0', STR_PAD_LEFT));
                                         }
                                     });
                                 }
                             });
                    });
                }

                if ($tipoAdeudo === 'todos' || $tipoAdeudo === 'especial') {
                    $orCondition = ($tipoAdeudo === 'todos') ? 'orWhere' : 'where';
                    $q->$orCondition(function ($subQ) use ($ciclo) {
                        $subQ->where('tipo', 'especial')
                             ->where('concepto', 'Reinscripción ' . $ciclo);
                    });
                }
            });

        // Filtrar por alumno específico si fue seleccionado
        if (!empty($alumnoId)) {
            $query->where('alumno_id', $alumnoId);
        }

        // Filtrar por estatus del alumno (regular, baja, egresado)
        if (!empty($estatusAlumno) && $estatusAlumno !== 'todos') {
            $query->whereHas('alumno', function ($q) use ($estatusAlumno) {
                $q->where('estatus', $estatusAlumno);
            });
        }

        $eliminados = $query->delete();

        return redirect()->route('ciclos.index', ['ciclo' => $ciclo])
            ->with('success', "Se eliminaron {$eliminados} adeudos no pagados del ciclo {$ciclo} según los filtros especificados.");
    }
}
