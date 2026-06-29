<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaMaestro extends Model
{
    use HasFactory;

    protected $fillable = [
        'profesor_id', 'fecha', 'hora_entrada', 'hora_salida', 'estado', 'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }
}
