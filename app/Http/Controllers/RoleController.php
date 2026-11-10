<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    private function ensureSpecialPermissions(): void
    {
        Permission::findOrCreate('editar_numero_lista', 'web');
    }

    public function index()
    {
        $this->ensureSpecialPermissions();
        $roles = Role::with(['menus', 'permissions'])->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $this->ensureSpecialPermissions();
        $menus = Menu::all();
        return view('roles.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $this->ensureSpecialPermissions();
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role = Role::create([
            'name' => strtolower($request->name),
            'guard_name' => 'web' // Default guard for Spatie
        ]);

        if ($request->has('menus')) {
            $role->menus()->sync($request->menus);
        }

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        $this->ensureSpecialPermissions();
        $menus = Menu::all();
        $roleMenus = $role->menus->pluck('id')->toArray();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'menus', 'roleMenus', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->ensureSpecialPermissions();
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id . '|max:255',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'name' => strtolower($request->name)
        ]);
        
        $role->menus()->sync($request->input('menus', []));
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
