<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Adeudo extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'tipo',
        'concepto',
        'monto_base',
        'monto_actual',
        'periodo',
        'status',
        'fecha_pago'
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * Calcula el monto actual basado en la fecha del sistema o una fecha dada.
     * 
     * Reglas:
     * - Antes o igual al día 10: 10% de descuento sobre monto_base.
     * - Después del día 10: 10% de recargo sobre monto_base.
     * - Si el periodo es anterior al mes actual y sigue pendiente:
     *   Se aplica un recargo acumulativo del 10% cada mes.
     * 
     * NOTA: Este método es para visualización dinámica. 
     * El valor real de 'monto_actual' en la BD se actualiza mediante un comando mensual.
     */
    /**
     * Retorna el nombre del mes basado en el periodo (YYYY-MM).
     */
    public function getMesNombreAttribute()
    {
        if (!$this->periodo) return 'N/A';
        try {
            return Carbon::parse($this->periodo . '-01')->translatedFormat('F');
        } catch (\Exception $e) {
            return 'Mes Inválido';
        }
    }

    /**
     * Retorna el año basado en el periodo (YYYY-MM).
     */
    public function getAnioAttribute()
    {
        if (!$this->periodo) return '';
        try {
            return Carbon::parse($this->periodo . '-01')->format('Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Retorna el concepto con un fallback si es nulo
     */
    public function getConceptoAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        if ($this->tipo === 'colegiatura') {
            if ($this->periodo) {
                return 'Colegiatura Mensual (' . ucfirst($this->mes_nombre) . ' ' . $this->anio . ')';
            }
            return 'Colegiatura Mensual';
        }

        if ($this->tipo === 'inscripcion') {
            return 'Inscripción';
        }

        return 'Adeudo ' . ucfirst($this->tipo);
    }

    public function getMontoCalculadoAttribute()
    {
        // Si no es colegiatura, el monto es directo
        if ($this->tipo !== 'colegiatura') {
            return $this->monto_actual;
        }

        // Si ya está pagado, mostrar lo que se pagó finalmente
        if ($this->status === 'pagado') {
            return $this->monto_actual;
        }

        $hoy = Carbon::now();
        $periodoActual = $hoy->format('Y-m');

        // REGLA: Si el periodo es el mes actual
        if ($this->periodo === $periodoActual) {
            // Si es día 1 a 10: monto base
            if ($hoy->day <= 10) {
                return $this->monto_base;
            } else {
                // Si es día 11 en adelante: base + 10%
                return $this->monto_base * 1.10;
            }
        }

        // REGLA: Si el periodo ya pasó, el monto_actual ya debe tener los recargos acumulados (vía comando)
        return $this->monto_actual;
    }
}
