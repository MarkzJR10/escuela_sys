<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\GradoGrupo;
use Illuminate\Http\Request;

class AdeudoController extends Controller
{
    /**
     * Muestra el formulario para generar adeudos especiales.
     */
    public function createEspecial()
    {
        $grados = GradoGrupo::select('grado')->distinct()->pluck('grado');
        $grupos = GradoGrupo::select('grupo')->distinct()->pluck('grupo');
        // No cargamos todos los alumnos para evitar problemas de rendimiento
        return view('adeudos.create_especial', compact('grados', 'grupos'));
    }

    /**
     * Endpoint para búsqueda AJAX de alumnos (Select2).
     */
    public function buscarAlumnosAjax(Request $request)
    {
        $term = $request->get('q');
        
        $alumnos = Alumno::where('nombre', 'like', "%{$term}%")
            ->orWhere('apellido_paterno', 'like', "%{$term}%")
            ->orWhere('apellido_materno', 'like', "%{$term}%")
            ->orWhere('matricula', 'like', "%{$term}%")
            ->limit(20)
            ->get();

        $results = $alumnos->map(function ($alumno) {
            return [
                'id' => $alumno->id,
                'text' => "[{$alumno->matricula}] {$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Procesa la generación masiva o individual de adeudos especiales.
     */
    public function storeEspecial(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'tipo_destino' => 'required|in:individual,masivo',
            // Si es individual:
            'alumno_ids' => 'required_if:tipo_destino,individual|array',
            'alumno_ids.*' => 'exists:alumnos,id',
            // Si es masivo:
            'grado' => 'nullable|string',
            'grupo' => 'nullable|string',
            'genero' => 'nullable|in:M,F,todos',
        ]);

        $concepto = $request->concepto;
        $monto = $request->monto;

        if ($request->tipo_destino === 'individual') {
            foreach ($request->alumno_ids as $alumno_id) {
                $this->crearAdeudo($alumno_id, $concepto, $monto);
            }
            return redirect()->back()->with('success', 'Adeudo especial generado para ' . count($request->alumno_ids) . ' alumno(s).');
        }

        // Generación masiva
        $query = Alumno::query();

        if ($request->grado || $request->grupo) {
            $query->whereHas('gradoGrupo', function($q) use ($request) {
                if ($request->grado) $q->where('grado', $request->grado);
                if ($request->grupo) $q->where('grupo', $request->grupo);
            });
        }

        if ($request->genero && $request->genero !== 'todos') {
            $query->where('genero', $request->genero);
        }

        $alumnos = $query->get();

        if ($alumnos->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron alumnos con los filtros seleccionados.');
        }

        foreach ($alumnos as $alumno) {
            $this->crearAdeudo($alumno->id, $concepto, $monto);
        }

        return redirect()->back()->with('success', "Adeudo generado exitosamente para " . $alumnos->count() . " alumnos.");
    }

    /**
     * Helper para crear el registro de adeudo.
     */
    private function crearAdeudo($alumno_id, $concepto, $monto)
    {
        Adeudo::create([
            'alumno_id' => $alumno_id,
            'tipo' => 'especial',
            'concepto' => $concepto,
            'monto_base' => $monto,
            'monto_actual' => $monto,
            'status' => 'pendiente',
        ]);
    }
}
