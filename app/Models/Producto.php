<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'clave_sat',
        'stock',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2'
    ];

    public function bitacorasStock()
    {
        return $this->hasMany(BitacoraStock::class, 'producto_id')->orderBy('created_at', 'desc');
    }
}
