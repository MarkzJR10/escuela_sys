<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'key',
        'value',
        'descripcion',
    ];

    /**
     * Obtiene un valor de configuración por su clave.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Establece un valor de configuración.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $descripcion
     * @return self
     */
    public static function set($key, $value, $descripcion = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'descripcion' => $descripcion]
        );
    }
}
