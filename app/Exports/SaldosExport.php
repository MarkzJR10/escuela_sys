<?php

namespace App\Exports;

use App\Models\Alumno;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SaldosExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Alumno::with(['gradoGrupo', 'adeudos' => function($q) {
            $q->whereIn('status', ['pendiente', 'vencido']);
        }])->get()->filter(function($alumno) {
            return $alumno->adeudos->sum('monto_calculado') > 0;
        });
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nombre Alumno',
            'Grado y Grupo',
            'Total Colegiaturas',
            'Total Especiales',
            'Gran Total Deuda'
        ];
    }

    public function map($alumno): array
    {
        $colegiaturas = $alumno->adeudos->where('tipo', 'colegiatura')->sum('monto_calculado');
        $especiales = $alumno->adeudos->where('tipo', 'especial')->sum('monto_calculado');
        $ventas = $alumno->adeudos->where('tipo', 'venta')->sum('monto_calculado');

        return [
            $alumno->matricula,
            $alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombre,
            ($alumno->gradoGrupo->grado ?? '') . ' "' . ($alumno->gradoGrupo->grupo ?? '') . '"',
            number_format($colegiaturas, 2),
            number_format($especiales, 2),
            number_format($colegiaturas + $especiales + $ventas, 2),
        ];
    }
}
