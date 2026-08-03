<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colegiatura extends Model
{
    use HasFactory;

    protected $table = 'colegiaturas';

    protected $fillable = [
        'nombre',
        'monto',
    ];

    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'colegiatura_id');
    }
}
