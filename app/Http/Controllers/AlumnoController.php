<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\GradoGrupo;
use App\Models\Padre;
use App\Models\Adeudo;
use App\Models\Configuracion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alumnos = Alumno::with('gradoGrupo')->get();
        return view('alumnos.index', compact('alumnos'));
    }

    public function create()
    {
        $gradoGrupos = GradoGrupo::all();
        $padres = Padre::with('user')->get();
        return view('alumnos.create', compact('gradoGrupos', 'padres'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula' => 'required|string|unique:alumnos,matricula',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'genero' => 'required|in:M,F',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'alergias' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'grado_grupo_id' => 'required|exists:grado_grupos,id',
            'colegiatura' => 'nullable|numeric|between:0,999999.99',
            'padre_id' => 'nullable|exists:padres,id',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        return DB::transaction(function () use ($request) {
            $data = $request->all();

            if ($request->hasFile('fotografia')) {
                $path = $request->file('fotografia')->store('alumnos', 'public');
                $data['fotografia'] = $path;
            }

            $alumno = Alumno::create($data);
            
            // 1. Generar adeudo de inscripción automático
            $costoInscripcion = Configuracion::get('costo_inscripcion', 0);
            $cicloActual = Configuracion::get('ciclo_actual', '2025-2026');

            if ($costoInscripcion > 0) {
                Adeudo::create([
                    'alumno_id' => $alumno->id,
                    'tipo' => 'especial',
                    'concepto' => "Inscripción $cicloActual",
                    'monto_base' => $costoInscripcion,
                    'monto_actual' => $costoInscripcion,
                    'status' => 'pendiente',
                ]);
            }

            // 2. Generar Colegiaturas del Ciclo (Agosto - Mayo)
            if ($alumno->colegiatura > 0) {
                $anios = explode('-', $cicloActual); // [2025, 2026]
                $anioInicio = $anios[0];
                $anioFin = $anios[1] ?? ($anioInicio + 1);

                $meses = [
                    ['mes' => 8, 'anio' => $anioInicio],
                    ['mes' => 9, 'anio' => $anioInicio],
                    ['mes' => 10, 'anio' => $anioInicio],
                    ['mes' => 11, 'anio' => $anioInicio],
                    ['mes' => 12, 'anio' => $anioInicio],
                    ['mes' => 1, 'anio' => $anioFin],
                    ['mes' => 2, 'anio' => $anioFin],
                    ['mes' => 3, 'anio' => $anioFin],
                    ['mes' => 4, 'anio' => $anioFin],
                    ['mes' => 5, 'anio' => $anioFin],
                ];

                $hoy = Carbon::now();

                foreach ($meses as $m) {
                    $periodo = sprintf("%04d-%02d", $m['anio'], $m['mes']);
                    $fechaPeriodo = Carbon::parse($periodo . '-01');
                    
                    // REGLA: Solo generar adeudos desde el mes de inscripción en adelante
                    if ($fechaPeriodo->format('Y-m') < $hoy->format('Y-m')) {
                        continue;
                    }

                    $status = 'programado';
                    $montoActual = $alumno->colegiatura;

                    if ($fechaPeriodo->isPast() && $periodo !== $hoy->format('Y-m')) {
                        // Meses pasados: Status Vencido y calcular recargos acumulados
                        $status = 'vencido';
                        // Calculamos cuántos meses han pasado desde el vencimiento (día 11 del mes del periodo)
                        $fechaVencimiento = Carbon::parse($periodo . '-11');
                        $mesesVencidos = (int) $fechaVencimiento->diffInMonths($hoy);
                        
                        for ($i = 0; $i <= $mesesVencidos; $i++) {
                            $montoActual += $alumno->colegiatura * 0.10;
                        }
                    } elseif ($periodo === $hoy->format('Y-m')) {
                        // Mes actual
                        if ($hoy->day >= 11) {
                            $status = 'vencido';
                            $montoActual += $alumno->colegiatura * 0.10;
                        } else {
                            $status = 'pendiente';
                        }
                    }

                    Adeudo::create([
                        'alumno_id' => $alumno->id,
                        'tipo' => 'colegiatura',
                        'concepto' => 'Colegiatura Mensual',
                        'monto_base' => $alumno->colegiatura,
                        'monto_actual' => $montoActual,
                        'periodo' => $periodo,
                        'status' => $status,
                    ]);
                }
            }

            return redirect()->route('alumnos.index')->with('success', 'Alumno creado exitosamente.');
        });
    }

    public function show(Alumno $alumno)
    {
        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno)
    {
        $gradoGrupos = GradoGrupo::all();
        $padres = Padre::with('user')->get();
        return view('alumnos.edit', compact('alumno', 'gradoGrupos', 'padres'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $request->validate([
            'matricula' => 'required|string|unique:alumnos,matricula,' . $alumno->id,
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'genero' => 'required|in:M,F',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'alergias' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'grado_grupo_id' => 'required|exists:grado_grupos,id',
            'colegiatura' => 'nullable|numeric|between:0,999999.99',
            'padre_id' => 'nullable|exists:padres,id',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('fotografia')) {
            // Delete old photo if exists
            if ($alumno->fotografia) {
                Storage::disk('public')->delete($alumno->fotografia);
            }
            $path = $request->file('fotografia')->store('alumnos', 'public');
            $data['fotografia'] = $path;
        }

        $alumno->update($data);
        return redirect()->route('alumnos.index')->with('success', 'Alumno actualizado exitosamente.');
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->delete();
        return redirect()->route('alumnos.index')->with('success', 'Alumno eliminado exitosamente.');
    }
}
