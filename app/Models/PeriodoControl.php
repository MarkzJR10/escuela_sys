<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoControl extends Model
{
    protected $table = 'periodo_controles';

    protected $fillable = ['trimestre', 'activo'];

    protected $casts = [
        'activo' => 'boolean'
    ];
}
