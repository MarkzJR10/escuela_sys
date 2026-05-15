<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::all();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:255',
            'url'  => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);
        Menu::create($request->all());
        return redirect()->route('menus.index')->with('success', 'Menú creado exitosamente.');
    }

    public function show(Menu $menu)
    {
        return view('menus.show', compact('menu'));
    }

    public function edit(Menu $menu)
    {
        return view('menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'text' => 'required|string|max:255',
            'url'  => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);
        $menu->update($request->all());
        return redirect()->route('menus.index')->with('success', 'Menú actualizado exitosamente.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menú eliminado exitosamente.');
    }
}
