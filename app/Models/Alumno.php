<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula',
        'numero_lista',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'genero',
        'curp',
        'fecha_nacimiento',
        'domicilio',
        'alergias',
        'telefono',
        'celular',
        'grado_grupo_id',
        'colegiatura_id',
        'colegiatura',
        'monto_extracurricular',
        'fotografia',
        'padre_id',
        'activo',
        'estatus'
    ];

    /**
     * Obtiene el siguiente número de lista sugerido (al final de la lista) para un grupo.
     */
    public static function siguienteNumeroLista($gradoGrupoId): int
    {
        if (!$gradoGrupoId) {
            return 1;
        }
        $max = static::where('grado_grupo_id', $gradoGrupoId)->max('numero_lista');
        return ((int) $max) + 1;
    }

    public function gradoGrupo()
    {
        return $this->belongsTo(GradoGrupo::class);
    }

    public function colegiaturaBase()
    {
        return $this->belongsTo(Colegiatura::class, 'colegiatura_id');
    }

    public function adeudos()
    {
        return $this->hasMany(Adeudo::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function padre()
    {
        return $this->belongsTo(Padre::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    public function boletas()
    {
        return $this->hasMany(Boleta::class, 'matricula', 'matricula');
    }

    public function reportesConducta()
    {
        return $this->hasMany(ReporteConducta::class);
    }

    public function reportesTareas()
    {
        return $this->hasMany(ReporteTarea::class);
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function getApellidosAttribute()
    {
        return trim("{$this->apellido_paterno} {$this->apellido_materno}");
    }

    /**
     * Obtiene los datos consolidados de la boleta actual del alumno.
     */
    public function getBoletaActualData()
    {
        return \App\Services\BoletaPdfService::buildBoletaData($this);
    }

    /**
     * Obtiene el histórico completo de calificaciones del alumno (Kardex) agrupado por grado.
     */
    public function getKardexData()
    {
        $gradosCollection = collect();

        // 1. Obtener registros históricos guardados en la tabla boletas
        $boletas = Boleta::where('matricula', $this->matricula)
            ->orderBy('grado')
            ->orderBy('materia')
            ->get();

        foreach ($boletas as $b) {
            $gradoKey = $b->grado ?: ($this->gradoGrupo ? $this->gradoGrupo->grado : '1°');
            if (!$gradosCollection->has($gradoKey)) {
                $gradosCollection->put($gradoKey, [
                    'grado' => $gradoKey,
                    'ciclo' => $b->ciclo ?: '',
                    'materias' => collect(),
                ]);
            }

            $p1 = !is_null($b->p1) ? floatval($b->p1) : null;
            $p2 = !is_null($b->p2) ? floatval($b->p2) : null;
            $p3 = !is_null($b->p3) ? floatval($b->p3) : null;
            $validScores = array_values(array_filter([$p1, $p2, $p3], fn($v) => !is_null($v)));
            $pFinal = !is_null($b->p_final) ? floatval($b->p_final) : (count($validScores) > 0 ? round(array_sum($validScores) / count($validScores), 1) : null);

            $materiaKey = strtolower(trim($b->materia));
            $gradosCollection[$gradoKey]['materias']->put($materiaKey, [
                'id' => $b->id,
                'materia' => $b->materia,
                'p1' => $p1,
                'p2' => $p2,
                'p3' => $p3,
                'promedio' => $pFinal,
                'fuente' => 'boletas'
            ]);
        }

        // 2. Incorporar el grado actual desde calificaciones en vivo
        $boletaActual = $this->getBoletaActualData();
        $gradoActualKey = $boletaActual['grado'];

        if (!$gradosCollection->has($gradoActualKey)) {
            $gradosCollection->put($gradoActualKey, [
                'grado' => $gradoActualKey,
                'ciclo' => $boletaActual['ciclo'],
                'materias' => collect(),
            ]);
        }

        // Mezclar las materias del grado actual
        foreach ($boletaActual['materias'] as $item) {
            $mKey = strtolower(trim($item['materia']));
            if (!$gradosCollection[$gradoActualKey]['materias']->has($mKey) || $item['fuente'] === 'calificaciones') {
                $gradosCollection[$gradoActualKey]['materias']->put($mKey, [
                    'id' => null,
                    'materia' => $item['materia'],
                    'p1' => $item['p1'],
                    'p2' => $item['p2'],
                    'p3' => $item['p3'],
                    'promedio' => $item['p_final'],
                    'fuente' => $item['fuente']
                ]);
            }
        }

        // 3. Procesar y estructurar cada grado
        $gradosFinal = [];
        $todosLosPromedios = [];
        $totalMateriasCursadas = 0;
        $totalMateriasAprobadas = 0;
        $totalMateriasReprobadas = 0;

        foreach ($gradosCollection as $gKey => $gData) {
            $materiasList = $gData['materias']->sortBy('materia')->values();
            $proms = $materiasList->pluck('promedio')->filter(fn($v) => !is_null($v))->all();
            $promedioGrado = count($proms) > 0 ? round(array_sum($proms) / count($proms), 1) : null;

            $aprobadasGrado = $materiasList->filter(fn($m) => !is_null($m['promedio']) && $m['promedio'] >= 6.0)->count();
            $reprobadasGrado = $materiasList->filter(fn($m) => !is_null($m['promedio']) && $m['promedio'] < 6.0)->count();

            $gradoNum = preg_replace('/[^0-9]/', '', $gKey);
            $gradoLabel = $gradoNum ? "{$gradoNum}° Grado" : (ucfirst($gKey) . (str_contains(strtolower($gKey), 'grado') ? '' : ' Grado'));

            $gradosFinal[$gKey] = [
                'grado' => $gKey,
                'grado_label' => $gradoLabel,
                'grado_numero' => $gradoNum ? intval($gradoNum) : 99,
                'ciclo' => $gData['ciclo'],
                'materias' => $materiasList,
                'promedio_grado' => $promedioGrado,
                'total_materias' => $materiasList->count(),
                'materias_aprobadas' => $aprobadasGrado,
                'materias_reprobadas' => $reprobadasGrado,
            ];

            foreach ($proms as $p) {
                $todosLosPromedios[] = $p;
            }
            $totalMateriasCursadas += $materiasList->count();
            $totalMateriasAprobadas += $aprobadasGrado;
            $totalMateriasReprobadas += $reprobadasGrado;
        }

        // Ordenar grados cronológicamente por número de grado
        uasort($gradosFinal, function($a, $b) {
            return $a['grado_numero'] <=> $b['grado_numero'];
        });

        $promedioGeneralAcumulado = count($todosLosPromedios) > 0 ? round(array_sum($todosLosPromedios) / count($todosLosPromedios), 1) : null;

        return [
            'alumno' => $this,
            'grados' => $gradosFinal,
            'promedio_general_acumulado' => $promedioGeneralAcumulado,
            'total_materias_cursadas' => $totalMateriasCursadas,
            'total_materias_aprobadas' => $totalMateriasAprobadas,
            'total_materias_reprobadas' => $totalMateriasReprobadas,
            'total_grados' => count($gradosFinal),
        ];
    }
}
