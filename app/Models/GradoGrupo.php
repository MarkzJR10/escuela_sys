<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradoGrupo extends Model
{
    use HasFactory;

    protected $fillable = ['grado', 'grupo', 'maestro_id'];

    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Profesor::class, 'maestro_id');
    }
}
