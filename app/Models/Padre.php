<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Padre extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'nombre', 
        'apellido_paterno', 
        'apellido_materno', 
        'genero', 
        'curp', 
        'fecha_nacimiento', 
        'domicilio', 
        'telefono', 
        'celular', 
        'fotografia'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function datosFacturacion()
    {
        return $this->hasOne(DatosFacturacion::class);
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }
}
