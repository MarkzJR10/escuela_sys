<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'grado'];

    /**
     * Devuelve el nombre de la materia junto a su grado.
     */
    public function getNombreConGradoAttribute(): string
    {
        return $this->grado 
            ? "{$this->nombre} - {$this->grado}"
            : "{$this->nombre} - General";
    }

    /**
     * Indica si la materia es extracurricular y se califica con letras (A, B, C, D).
     */
    public function getIsExtracurricularAttribute(): bool
    {
        return \App\Services\BoletaPdfService::isExtracurricularSubject($this->nombre);
    }
}
