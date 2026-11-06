<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\GradoGrupo;
use Illuminate\Http\Request;

class MigrarGradoController extends Controller
{
    /**
     * Mostrar formulario para seleccionar grado a migrar.
     */
    public function index(Request $request)
    {
        $gradoGrupos = GradoGrupo::orderBy('grado')->orderBy('grupo')->get();
        $alumnos = collect();

        if ($request->filled('grado_grupo_id')) {
            $alumnos = Alumno::where('grado_grupo_id', $request->grado_grupo_id)
                ->where('activo', true)
                ->orderBy('apellido_paterno')
                ->get();
        }

        // Obtener grados destino
        $gradosDestino = GradoGrupo::orderBy('grado')->orderBy('grupo')->get();

        return view('migrar_grados.index', compact('gradoGrupos', 'alumnos', 'gradosDestino'));
    }

    /**
     * Migrar un alumno individual a un nuevo grado.
     */
    public function migrarAlumno(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'nuevo_grado_grupo_id' => 'required|exists:grado_grupos,id',
        ]);

        $alumno = Alumno::findOrFail($request->alumno_id);
        $nuevoNumeroLista = ((int) $alumno->grado_grupo_id !== (int) $request->nuevo_grado_grupo_id)
            ? Alumno::siguienteNumeroLista($request->nuevo_grado_grupo_id)
            : $alumno->numero_lista;

        $alumno->update([
            'grado_grupo_id' => $request->nuevo_grado_grupo_id,
            'numero_lista' => $nuevoNumeroLista,
        ]);

        return redirect()->back()->with('success', "Alumno {$alumno->nombre} migrado exitosamente.");
    }

    /**
     * Migrar todos los alumnos de un grado a otro masivamente.
     */
    public function migrarMasivo(Request $request)
    {
        $request->validate([
            'grado_grupo_origen_id' => 'required|exists:grado_grupos,id',
            'grado_grupo_destino_id' => 'required|exists:grado_grupos,id',
        ]);

        $count = Alumno::where('grado_grupo_id', $request->grado_grupo_origen_id)
            ->where('activo', true)
            ->update(['grado_grupo_id' => $request->grado_grupo_destino_id]);

        // Reordenar números de lista en el grupo destino por apellidos
        $alumnosDestino = Alumno::where('grado_grupo_id', $request->grado_grupo_destino_id)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
        $num = 1;
        foreach ($alumnosDestino as $al) {
            $al->update(['numero_lista' => $num++]);
        }

        $origen = GradoGrupo::find($request->grado_grupo_origen_id);
        $destino = GradoGrupo::find($request->grado_grupo_destino_id);

        return redirect()->route('migrar_grados.index')
            ->with('success', "Se migraron {$count} alumnos de {$origen->grado}{$origen->grupo} a {$destino->grado}{$destino->grupo}.");
    }

    /**
     * Dar de baja un alumno (marcar como inactivo).
     */
    public function darBaja(Request $request)
    {
        $request->validate(['alumno_id' => 'required|exists:alumnos,id']);
        
        $alumno = Alumno::findOrFail($request->alumno_id);
        $alumno->update([
            'activo' => false,
            'estatus' => 'baja'
        ]);

        return redirect()->back()->with('success', "Alumno {$alumno->nombre} marcado como baja.");
    }

    /**
     * Reactivar un alumno inactivo.
     */
    public function reactivar(Request $request)
    {
        $request->validate(['alumno_id' => 'required|exists:alumnos,id']);
        
        $alumno = Alumno::findOrFail($request->alumno_id);
        $alumno->update([
            'activo' => true,
            'estatus' => 'regular'
        ]);

        return redirect()->back()->with('success', "Alumno {$alumno->nombre} reactivado como regular exitosamente.");
    }

    /**
     * Ver listado de alumnos inactivos.
     */
    public function inactivos()
    {
        $alumnos = Alumno::with('gradoGrupo')
            ->where('activo', false)
            ->orderBy('apellido_paterno')
            ->get();

        return view('migrar_grados.inactivos', compact('alumnos'));
    }
}
