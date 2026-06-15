<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteTarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id', 'user_id', 'fecha', 'materia', 'descripcion', 'estatus'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
