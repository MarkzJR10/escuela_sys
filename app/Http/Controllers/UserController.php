<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profesor;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole($request->role);

            // Si el rol es profesor, sincronizarlo automáticamente a la tabla profesores
            if (strtolower($request->role) === 'profesor') {
                Profesor::syncFromUser($user);
            }
        });

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|exists:roles,name',
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }
            $user->syncRoles([$request->role]);

            if (strtolower($request->role) === 'profesor') {
                Profesor::syncFromUser($user);
            } else {
                // Si ya no tiene el rol profesor y no tiene asignaciones activas, retirar de profesores
                if ($user->profesor) {
                    $tieneAsignaciones = DB::table('maestro_materia')->where('profesor_id', $user->profesor->id)->exists()
                        || DB::table('grado_grupos')->where('maestro_id', $user->profesor->id)->exists();
                    if (!$tieneAsignaciones) {
                        if ($user->profesor->fotografia) {
                            Storage::disk('public')->delete($user->profesor->fotografia);
                        }
                        $user->profesor->delete();
                    }
                }
            }
        });

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            if ($user->profesor) {
                if ($user->profesor->fotografia) {
                    Storage::disk('public')->delete($user->profesor->fotografia);
                }
                $user->profesor->delete();
            }
            $user->delete();
        });

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
