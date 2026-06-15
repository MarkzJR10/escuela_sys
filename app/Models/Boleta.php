<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula', 'nombre', 'grado', 'grupo', 'materia',
        'p1', 'p2', 'p3', 'p_final', 'ciclo'
    ];

    /**
     * Busca el alumno por matrícula.
     */
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'matricula', 'matricula');
    }
}
