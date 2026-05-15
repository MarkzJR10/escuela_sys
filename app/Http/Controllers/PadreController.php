<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use App\Models\User;
use App\Models\DatosFacturacion;
use App\Models\SatConcepto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class PadreController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $padres = Padre::with(['user', 'alumnos'])
            ->when($search, function($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('curp', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('email', 'like', "%{$search}%");
                  });
            })
            ->paginate(10);

        $satConceptos = SatConcepto::where('active', true)->get();

        return view('padres.index', compact('padres', 'search', 'satConceptos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'genero' => 'required|in:M,F',
            'curp' => 'required|size:18|unique:padres,curp',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'telefono' => 'required|string|max:20',
            'celular' => 'nullable|string|max:20',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Crear el usuario
            $user = User::create([
                'name' => trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno),
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Asignar rol padre
            $role = Role::firstOrCreate(['name' => 'padre', 'guard_name' => 'web']);
            $user->assignRole($role);

            // 3. Preparar datos del padre
            $data = $request->only([
                'nombre', 'apellido_paterno', 'apellido_materno', 'genero', 
                'curp', 'fecha_nacimiento', 'domicilio', 'telefono', 'celular'
            ]);
            $data['user_id'] = $user->id;

            if ($request->hasFile('fotografia')) {
                $path = $request->file('fotografia')->store('padres', 'public');
                $data['fotografia'] = $path;
            }

            // 4. Crear el padre
            Padre::create($data);
        });

        return redirect()->route('padres.index')->with('success', 'Padre y cuenta de usuario creados exitosamente.');
    }

    public function edit(Padre $padre)
    {
        return view('padres.edit', compact('padre'));
    }

    public function update(Request $request, Padre $padre)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $padre->user_id,
            'password' => 'nullable|min:6',
            'genero' => 'required|in:M,F',
            'curp' => 'required|size:18|unique:padres,curp,' . $padre->id,
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'telefono' => 'required|string|max:20',
            'celular' => 'nullable|string|max:20',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request, $padre) {
            $data = $request->only([
                'nombre', 'apellido_paterno', 'apellido_materno', 'genero', 
                'curp', 'fecha_nacimiento', 'domicilio', 'telefono', 'celular'
            ]);

            if ($request->hasFile('fotografia')) {
                if ($padre->fotografia) {
                    Storage::disk('public')->delete($padre->fotografia);
                }
                $path = $request->file('fotografia')->store('padres', 'public');
                $data['fotografia'] = $path;
            }

            $padre->update($data);

            if ($padre->user) {
                $userData = [
                    'name' => trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno),
                    'email' => $request->email,
                ];
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $padre->user->update($userData);
            }
        });

        return redirect()->route('padres.index')->with('success', 'Padre actualizado exitosamente.');
    }

    public function destroy(Padre $padre)
    {
        DB::transaction(function () use ($padre) {
            if ($padre->fotografia) {
                Storage::disk('public')->delete($padre->fotografia);
            }
            if ($padre->user) {
                $padre->user->delete();
            }
            $padre->delete();
        });

        return redirect()->route('padres.index')->with('success', 'Padre y cuenta de usuario eliminados exitosamente.');
    }

    public function updateBilling(Request $request, Padre $padre)
    {
        $data = $request->validate([
            'rfc' => 'nullable|max:13',
            'razon_social' => 'nullable',
            'calle' => 'nullable',
            'numero' => 'nullable',
            'colonia' => 'nullable',
            'ciudad' => 'nullable',
            'codigo_postal' => 'nullable',
            'sep' => 'nullable',
            'sat' => 'nullable',
            'estado' => 'nullable',
        ]);

        $padre->datosFacturacion()->updateOrCreate(
            ['padre_id' => $padre->id],
            $data
        );

        return redirect()->route('padres.index')->with('success', 'Datos de facturación actualizados.');
    }

    public function getChildren(Padre $padre)
    {
        return response()->json($padre->alumnos()->with('gradoGrupo')->get());
    }
}
