<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';
    protected $fillable = ['alumno_id', 'materia_id', 'trimestre', 'puntaje'];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Devuelve la letra (A, B, C, D) correspondiente al puntaje para materias extracurriculares.
     */
    public function getLetraExtracurricularAttribute(): string
    {
        return trim(\App\Services\BoletaPdfService::formatExtraGrade($this->puntaje));
    }
}
