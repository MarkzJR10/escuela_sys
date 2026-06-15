<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteConducta extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id', 'user_id', 'no_reporte', 'fecha',
        'razon1', 'razon2', 'razon3', 'razon4', 'razon5',
        'razon6', 'razon7', 'razon8', 'razon9', 'razon10', 'razon11',
        'otro', 'estatus'
    ];

    protected $casts = [
        'fecha' => 'date',
        'razon1' => 'boolean', 'razon2' => 'boolean', 'razon3' => 'boolean',
        'razon4' => 'boolean', 'razon5' => 'boolean', 'razon6' => 'boolean',
        'razon7' => 'boolean', 'razon8' => 'boolean', 'razon9' => 'boolean',
        'razon10' => 'boolean', 'razon11' => 'boolean',
    ];

    public const RAZONES = [
        'razon1'  => 'Faltar el respeto al maestro.',
        'razon2'  => 'Molestar a sus compañeros.',
        'razon3'  => 'Pelear.',
        'razon4'  => 'Jugar dentro del aula.',
        'razon5'  => 'Utilizar lenguaje inadecuado.',
        'razon6'  => 'Hacer caso omiso de indicaciones.',
        'razon7'  => 'Incumplimiento de más de 3 tareas.',
        'razon8'  => 'No atender la clase por hacer tarea de otra materia.',
        'razon9'  => 'Indisciplina.',
        'razon10' => 'Dañar las instalaciones, mobiliario y/o material escolar.',
        'razon11' => 'Presentar un promedio semanal de conducta menor de 7.',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Devuelve las razones marcadas como un array de textos.
     */
    public function getRazonesMarcadasAttribute(): array
    {
        $marcadas = [];
        foreach (self::RAZONES as $campo => $texto) {
            if ($this->$campo) {
                $marcadas[] = $texto;
            }
        }
        return $marcadas;
    }
}
