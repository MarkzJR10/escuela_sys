<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'genero',
        'curp',
        'fecha_nacimiento',
        'domicilio',
        'alergias',
        'telefono',
        'celular',
        'grado_grupo_id',
        'colegiatura_id',
        'colegiatura',
        'monto_extracurricular',
        'fotografia',
        'padre_id',
        'activo',
        'estatus'
    ];

    public function gradoGrupo()
    {
        return $this->belongsTo(GradoGrupo::class);
    }

    public function colegiaturaBase()
    {
        return $this->belongsTo(Colegiatura::class, 'colegiatura_id');
    }

    public function adeudos()
    {
        return $this->hasMany(Adeudo::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function padre()
    {
        return $this->belongsTo(Padre::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    public function boletas()
    {
        return $this->hasMany(Boleta::class, 'matricula', 'matricula');
    }
}
