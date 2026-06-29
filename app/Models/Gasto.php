<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'concepto', 'monto', 'fecha', 'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function cajero()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
