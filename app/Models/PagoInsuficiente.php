<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoInsuficiente extends Model
{
    use HasFactory;

    protected $table = 'pago_insuficientes';

    protected $fillable = [
        'alumno_id',
        'matricula',
        'alumno_nombre',
        'grado',
        'periodo',
        'fecha_pago',
        'referencia',
        'referencia_leyenda',
        'monto_abonado',
        'monto_debido',
        'diferencia',
        'user_id',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
