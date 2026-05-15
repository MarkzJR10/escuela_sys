<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'pago_id',
        'adeudo_id',
        'monto_adeudo',
        'descuento',
        'monto_pagado',
        'notas'
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    public function adeudo()
    {
        return $this->belongsTo(Adeudo::class);
    }
}
