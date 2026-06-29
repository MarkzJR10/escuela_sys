<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discrepancia extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'monto_sistema', 'monto_fisico', 'diferencia', 'motivo', 'fecha'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function cajero()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
