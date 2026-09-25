<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\GradoGrupo;
use App\Models\Colegiatura;

class InyectarColegiaturasAlumnoSeeder extends Seeder
{
    public function run()
    {
        $matricula = '999666';
        
        $alumno = Alumno::where('matricula', $matricula)->first();

        if (!$alumno) {
            $grupo = GradoGrupo::first();
            $colegiatura = Colegiatura::first();

            $alumno = Alumno::create([
                'matricula' => $matricula,
                'nombre' => 'Alumno',
                'apellido_paterno' => 'Prueba',
                'apellido_materno' => 'Excel',
                'genero' => 'M',
                'curp' => 'PRUEBA999666MDF',
                'fecha_nacimiento' => '2015-01-01',
                'grado_grupo_id' => $grupo->id ?? 1,
                'colegiatura_id' => $colegiatura->id ?? 1,
                'colegiatura' => $colegiatura->monto ?? 1500.00,
                'activo' => false,
                'estatus' => 'baja'
            ]);
            $this->command->info("Alumno creado con matrícula {$matricula}.");
        } else {
            $this->command->info("Alumno encontrado: {$alumno->nombre} {$alumno->apellido_paterno}.");
        }

        $montoColegiatura = $alumno->colegiatura ?: 1500.00;
        $anio = date('Y');

        $periodos = [
            [
                'periodo' => "{$anio}-08",
                'concepto' => "Colegiatura Mensual (Agosto {$anio})"
            ],
            [
                'periodo' => "{$anio}-09",
                'concepto' => "Colegiatura Mensual (Septiembre {$anio})"
            ],
        ];

        foreach ($periodos as $p) {
            $adeudo = Adeudo::where('alumno_id', $alumno->id)
                ->where('periodo', $p['periodo'])
                ->first();

            if (!$adeudo) {
                Adeudo::create([
                    'alumno_id' => $alumno->id,
                    'tipo' => 'colegiatura',
                    'concepto' => $p['concepto'],
                    'monto_base' => $montoColegiatura,
                    'monto_actual' => $montoColegiatura,
                    'periodo' => $p['periodo'],
                    'status' => 'pendiente',
                ]);
                $this->command->info("Adeudo creado exitosamente para el periodo {$p['periodo']}.");
            } else {
                $this->command->info("El adeudo para el periodo {$p['periodo']} ya existía.");
            }
        }
    }
}
