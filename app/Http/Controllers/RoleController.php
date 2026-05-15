<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $menus = Menu::all();
        return view('roles.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id'
        ]);

        $role = Role::create([
            'name' => strtolower($request->name),
            'guard_name' => 'web' // Default guard for Spatie
        ]);

        if ($request->has('menus')) {
            $role->menus()->sync($request->menus);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        $menus = Menu::all();
        $roleMenus = $role->menus->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'menus', 'roleMenus'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id . '|max:255',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id'
        ]);

        $role->update([
            'name' => strtolower($request->name)
        ]);
        
        $role->menus()->sync($request->input('menus', []));

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
