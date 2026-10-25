<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;
    
    protected $table = 'profesores';
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'genero',
        'curp',
        'fecha_nacimiento',
        'domicilio',
        'telefono',
        'celular',
        'fotografia',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el nombre completo del profesor formateado.
     */
    public function getNombreCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
        ], fn($v) => !is_null($v) && trim((string)$v) !== '');

        if (!empty($partes)) {
            return implode(' ', $partes);
        }

        if ($this->user && !empty($this->user->name)) {
            return $this->user->name;
        }

        return 'Profesor #' . $this->id;
    }

    /**
     * Sincroniza o crea el registro en la tabla profesores a partir de un User con rol 'profesor'.
     */
    public static function syncFromUser(User $user): ?self
    {
        if (!$user->hasRole('profesor')) {
            return null;
        }

        $profesor = static::where('user_id', $user->id)->first();

        // Si no existe por user_id, buscar si hay un profesor sin user_id con nombre exacto
        if (!$profesor) {
            $profesor = static::whereNull('user_id')
                ->whereRaw("CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) = ?", [trim($user->name)])
                ->first();
            if ($profesor) {
                $profesor->update(['user_id' => $user->id]);
            }
        }

        // Si ya existe el profesor, solo actualizamos si está incompleto
        if ($profesor) {
            if (empty($profesor->nombre)) {
                $parsed = static::parseFullName($user->name);
                $profesor->update($parsed);
            }
            return $profesor;
        }

        // Crear nuevo registro de profesor a partir del nombre del usuario
        $parsed = static::parseFullName($user->name);
        return static::create(array_merge($parsed, [
            'user_id' => $user->id,
        ]));
    }

    /**
     * Desglosa un nombre completo en nombre, apellido_paterno y apellido_materno.
     */
    public static function parseFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));
        $count = count($parts);

        if ($count <= 1) {
            return [
                'nombre' => $parts[0] ?? $fullName,
                'apellido_paterno' => null,
                'apellido_materno' => null,
            ];
        }

        if ($count === 2) {
            return [
                'nombre' => $parts[0],
                'apellido_paterno' => $parts[1],
                'apellido_materno' => null,
            ];
        }

        if ($count === 3) {
            return [
                'nombre' => $parts[0],
                'apellido_paterno' => $parts[1],
                'apellido_materno' => $parts[2],
            ];
        }

        // 4 o más palabras
        $materno = array_pop($parts);
        $paterno = array_pop($parts);
        $nombre = implode(' ', $parts);

        return [
            'nombre' => $nombre,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
        ];
    }

    /**
     * Sincroniza todos los usuarios con rol 'profesor' que aún no tengan perfil en 'profesores'.
     */
    public static function syncAllProfesorUsers(): int
    {
        $users = User::role('profesor')->get();
        $count = 0;
        foreach ($users as $user) {
            if (static::syncFromUser($user)) {
                $count++;
            }
        }
        return $count;
    }
}
