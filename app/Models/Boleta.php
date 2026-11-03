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

    /**
     * Indica si la materia de este registro de boleta es extracurricular (A, B, C, D).
     */
    public function getIsExtracurricularAttribute(): bool
    {
        return \App\Services\BoletaPdfService::isExtracurricularSubject($this->materia);
    }

    /**
     * Devuelve la letra (A, B, C, D) para el trimestre indicado (1, 2, 3).
     */
    public function letraTrimestre(int $trimestre): string
    {
        $field = "p{$trimestre}";
        return trim(\App\Services\BoletaPdfService::formatExtraGrade($this->{$field}));
    }
}
