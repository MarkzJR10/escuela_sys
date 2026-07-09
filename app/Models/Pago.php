<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'user_id',
        'total',
        'referencia_ticket',
        'fecha_pago',
        'corte_id'
    ];

    protected $casts = [
        'fecha_pago' => 'datetime'
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function cajero()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(PagoDetalle::class);
    }

    public function corte()
    {
        return $this->belongsTo(Corte::class);
    }
}
