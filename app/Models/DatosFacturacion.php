<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatosFacturacion extends Model
{
    use HasFactory;

    protected $table = 'datos_facturacion';

    protected $fillable = [
        'padre_id',
        'rfc',
        'razon_social',
        'calle',
        'numero',
        'colonia',
        'ciudad',
        'codigo_postal',
        'sep',
        'sat',
        'estado'
    ];

    public function padre()
    {
        return $this->belongsTo(Padre::class);
    }
}
