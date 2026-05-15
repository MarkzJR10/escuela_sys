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
        $profesores = Profesor::with('user')->get();
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
            'email' => 'required|email|unique:users,email',
            'genero' => 'required|in:M,F',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Crear el usuario
            $user = User::create([
                'name' => trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno),
                'email' => $request->email,
                'password' => Hash::make('password'), // Contraseña por defecto
            ]);

            // 2. Asegurar que el rol profesor existe y asignarlo
            $role = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
            $user->assignRole($role);

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
            'email' => 'required|email|unique:users,email,' . $profesore->user_id,
            'genero' => 'required|in:M,F',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request, $profesore) {
            $data = $request->only([
                'nombre', 'apellido_paterno', 'apellido_materno', 'genero', 
                'curp', 'fecha_nacimiento', 'domicilio', 'telefono', 'celular'
            ]);

            if ($request->hasFile('fotografia')) {
                // Eliminar foto anterior si existe
                if ($profesore->fotografia) {
                    Storage::disk('public')->delete($profesore->fotografia);
                }
                $path = $request->file('fotografia')->store('profesores', 'public');
                $data['fotografia'] = $path;
            }

            // Actualizar profesor
            $profesore->update($data);

            // Actualizar usuario vinculado
            if ($profesore->user) {
                $profesore->user->update([
                    'name' => trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno),
                    'email' => $request->email,
                ]);
            }
        });

        return redirect()->route('profesores.index')->with('success', 'Profesor actualizado exitosamente.');
    }

    public function destroy(Profesor $profesore)
    {
        $profesore->delete();
        return redirect()->route('profesores.index')->with('success', 'Profesor eliminado exitosamente.');
    }
}
