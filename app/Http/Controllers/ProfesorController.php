<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class ProfesorController extends Controller
{
    public function index()
    {
        // Sincronizar cualquier usuario que tenga rol profesor para garantizar consistencia
        Profesor::syncAllProfesorUsers();

        $profesores = Profesor::with('user')->get()->sortBy('nombre_completo', SORT_NATURAL | SORT_FLAG_CASE)->values();
        return view('profesores.index', compact('profesores'));
    }

    public function create()
    {
        return view('profesores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $existingUser = User::where('email', $value)->first();
                    if ($existingUser && Profesor::where('user_id', $existingUser->id)->exists()) {
                        $fail('Ya existe un profesor registrado con este correo electrónico.');
                    }
                },
            ],
            'genero' => 'required|in:M,F',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $fullName = trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno);
            
            // 1. Obtener o crear el usuario
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $fullName,
                    'email' => $request->email,
                    'password' => Hash::make('password'), // Contraseña por defecto
                ]);
            } else {
                $user->update(['name' => $fullName]);
            }

            // 2. Asegurar que el rol profesor existe y asignarlo
            $role = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
            if (!$user->hasRole('profesor')) {
                $user->assignRole($role);
            }

            // 3. Preparar datos del profesor
            $data = $request->only([
                'nombre', 'apellido_paterno', 'apellido_materno', 'genero', 
                'curp', 'fecha_nacimiento', 'domicilio', 'telefono', 'celular'
            ]);
            $data['user_id'] = $user->id;

            if ($request->hasFile('fotografia')) {
                $path = $request->file('fotografia')->store('profesores', 'public');
                $data['fotografia'] = $path;
            }

            // 4. Crear el profesor vinculado al usuario
            Profesor::create($data);
        });

        return redirect()->route('profesores.index')->with('success', 'Profesor y cuenta de usuario creados exitosamente.');
    }

    public function show(Profesor $profesore)
    {
        return view('profesores.show', compact('profesore'));
    }

    public function edit(Profesor $profesore)
    {
        return view('profesores.edit', compact('profesore'));
    }

    public function update(Request $request, Profesor $profesore)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($profesore->user_id ?? 0),
            'genero' => 'required|in:M,F',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request, $profesore) {
            $fullName = trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno);
            $data = $request->only([
                'nombre', 'apellido_paterno', 'apellido_materno', 'genero', 
                'curp', 'fecha_nacimiento', 'domicilio', 'telefono', 'celular'
            ]);

            if ($request->hasFile('fotografia')) {
                if ($profesore->fotografia) {
                    Storage::disk('public')->delete($profesore->fotografia);
                }
                $path = $request->file('fotografia')->store('profesores', 'public');
                $data['fotografia'] = $path;
            }

            $role = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);

            // Actualizar usuario vinculado o crearlo/asociarlo si no existía
            if ($profesore->user) {
                $profesore->user->update([
                    'name' => $fullName,
                    'email' => $request->email,
                ]);
                if (!$profesore->user->hasRole('profesor')) {
                    $profesore->user->assignRole($role);
                }
            } else {
                $user = User::where('email', $request->email)->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $fullName,
                        'email' => $request->email,
                        'password' => Hash::make('password'),
                    ]);
                } else {
                    $user->update(['name' => $fullName]);
                }
                if (!$user->hasRole('profesor')) {
                    $user->assignRole($role);
                }
                $data['user_id'] = $user->id;
            }

            // Actualizar profesor
            $profesore->update($data);
        });

        return redirect()->route('profesores.index')->with('success', 'Profesor actualizado exitosamente.');
    }

    public function destroy(Profesor $profesore)
    {
        DB::transaction(function () use ($profesore) {
            if ($profesore->fotografia) {
                Storage::disk('public')->delete($profesore->fotografia);
            }
            $user = $profesore->user;
            $profesore->delete();

            if ($user) {
                if ($user->roles->count() <= 1 && $user->hasRole('profesor')) {
                    $user->delete();
                } else {
                    $user->removeRole('profesor');
                }
            }
        });

        return redirect()->route('profesores.index')->with('success', 'Profesor eliminado exitosamente.');
    }
}
