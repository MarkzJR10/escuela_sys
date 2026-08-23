<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraEliminacionAdeudo extends Model
{
    use HasFactory;

    protected $table = 'bitacora_eliminacion_adeudos';

    protected $fillable = [
        'user_id',
        'alumno_id',
        'matricula',
        'nombre_alumno',
        'ciclo',
        'accion',
        'monto_anterior',
        'monto_eliminado',
        'monto_nuevo',
        'meses_afectados',
        'total_registros_eliminados',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
}
