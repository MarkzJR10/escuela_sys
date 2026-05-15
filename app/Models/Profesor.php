<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;
    
    protected $table = 'profesores';
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'genero',
        'curp',
        'fecha_nacimiento',
        'domicilio',
        'telefono',
        'celular',
        'fotografia',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
