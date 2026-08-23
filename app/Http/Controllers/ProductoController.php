<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Exports\ProductosExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('bitacorasStock.usuario')->get();
        $bitacoras = \App\Models\BitacoraStock::with(['producto', 'usuario'])->orderBy('id', 'desc')->get();
        return view('productos.index', compact('productos', 'bitacoras'));
    }

    public function exportarExcel()
    {
        return Excel::download(new ProductosExport, 'catalogo_productos_' . now()->format('Y_m_d') . '.xlsx');
    }

    public function agregarStock(Request $request, Producto $producto)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $producto) {
            $stockAnterior = $producto->stock;
            $cantidad = (int) $request->cantidad;
            $stockNuevo = $stockAnterior + $cantidad;

            $producto->update(['stock' => $stockNuevo]);

            \App\Models\BitacoraStock::create([
                'producto_id' => $producto->id,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'cantidad_agregada' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => $request->motivo ?: 'Reabastecimiento de stock',
            ]);
        });

        return redirect()->route('productos.index')
            ->with('success', "Se agregaron {$request->cantidad} unidades al stock de {$producto->nombre}. Stock actualizado: {$producto->fresh()->stock}.");
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'clave_sat' => 'nullable|string|max:50',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean'
        ]);

        Producto::create(array_merge($request->all(), ['activo' => $request->has('activo')]));

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'clave_sat' => 'nullable|string|max:50',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean'
        ]);

        $producto->update(array_merge($request->all(), ['activo' => $request->has('activo')]));

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
