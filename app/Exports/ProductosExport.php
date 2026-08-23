<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductosExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Producto::orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Descripción',
            'Precio',
            'Clave SAT',
            'Stock',
            'Estado'
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->id,
            $producto->nombre,
            $producto->descripcion ?? 'N/A',
            number_format($producto->precio, 2),
            $producto->clave_sat ?? 'N/A',
            $producto->stock,
            $producto->activo ? 'Activo' : 'Inactivo'
        ];
    }
}
