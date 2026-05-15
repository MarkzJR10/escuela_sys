<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatConcepto extends Model
{
    use HasFactory;

    protected $table = 'sat_conceptos';

    protected $fillable = ['clave', 'descripcion', 'active'];
}
