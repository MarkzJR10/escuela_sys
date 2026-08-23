<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraStock extends Model
{
    use HasFactory;

    protected $table = 'bitacora_stocks';

    protected $fillable = [
        'producto_id',
        'user_id',
        'cantidad_agregada',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
