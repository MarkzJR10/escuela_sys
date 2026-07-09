<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Padre;
use App\Models\GradoGrupo;
use App\Models\User;
use Carbon\Carbon;

class AlumnoRecargosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando seeder de prueba: Alumno con recargos desde enero...');

        // 1. Obtener o crear un tutor (Padre)
        $padre = Padre::first();
        if (!$padre) {
            $userPadre = User::create([
                'name' => 'Tutor de Pruebas',
                'email' => 'tutor_pruebas@escuela.com',
                'password' => bcrypt('password')
            ]);
            $userPadre->assignRole('padre');
            $padre = Padre::create([
                'user_id' => $userPadre->id,
                'nombre' => 'Tutor',
                'apellido_paterno' => 'De Pruebas',
                'rfc' => 'TUTR800101XYZ',
                'telefono' => '5559876543'
            ]);
        }

        // 2. Obtener un grado y grupo
        $gradoGrupo = GradoGrupo::first();
        if (!$gradoGrupo) {
            $turno = \App\Models\Turno::firstOrCreate(['nombre' => 'Matutino'], [
                'hora_inicio' => '07:00:00',
                'hora_fin' => '13:00:00'
            ]);
            $gradoGrupo = GradoGrupo::create([
                'grado' => '1',
                'grupo' => 'A',
                'turno_id' => $turno->id
            ]);
        }

        // 3. Crear o buscar Alumno de Prueba (limpiando datos previos para permitir re-ejecución)
        $alumno = Alumno::where('matricula', 'TEST' . date('Y') . 'JAN')->first();
        if ($alumno) {
            $alumno->pagos()->delete();
            $alumno->adeudos()->delete();
            $this->command->info("Alumno existente encontrado. Limpiando adeudos y pagos previos...");
        } else {
            $alumno = Alumno::create([
                'matricula' => 'TEST' . date('Y') . 'JAN',
                'nombre' => 'Mateo',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'López',
                'genero' => 'M',
                'padre_id' => $padre->id,
                'grado_grupo_id' => $gradoGrupo->id,
                'colegiatura' => 2000.00
            ]);
            $this->command->info("Alumno creado: {$alumno->nombre} {$alumno->apellido_paterno} (Matrícula: {$alumno->matricula})");
        }

        // 4. Crear Adeudo de Inscripción pagado en Enero
        $inscripcion = Adeudo::create([
            'alumno_id' => $alumno->id,
            'tipo' => 'especial',
            'concepto' => 'Inscripción Ciclo 2025-2026',
            'monto_base' => 3000.00,
            'monto_actual' => 3000.00,
            'periodo' => '2026-01',
            'status' => 'pagado',
            'fecha_pago' => Carbon::parse('2026-01-05')
        ]);

        $adminUser = User::role('administrador')->first() ?? User::first();
        
        $pago = Pago::create([
            'alumno_id' => $alumno->id,
            'user_id' => $adminUser->id,
            'total' => 3000.00,
            'referencia_ticket' => 'TKT-INS-' . rand(10000, 99999),
            'fecha_pago' => Carbon::parse('2026-01-05')
        ]);

        PagoDetalle::create([
            'pago_id' => $pago->id,
            'adeudo_id' => $inscripcion->id,
            'monto_adeudo' => 3000.00,
            'monto_pagado' => 3000.00
        ]);

        $this->command->info("Inscripción del alumno registrada y pagada en Enero.");

        // 5. Generar las colegiaturas mensuales desde Enero 2026 hasta Julio 2026
        $montoBase = 2000.00;
        $startMonth = Carbon::parse('2026-01-01');
        $endMonth = Carbon::now(); // Julio 2026
        
        $current = $startMonth->copy();
        while ($current->format('Y-m') <= $endMonth->format('Y-m')) {
            $periodo = $current->format('Y-m');
            $isCurrentMonth = ($periodo === $endMonth->format('Y-m'));
            
            if ($isCurrentMonth) {
                // El mes actual (Julio 2026) está pendiente y sin recargos (porque hoy es día 9, <= 10)
                $status = 'pendiente';
                $montoActual = $montoBase;
            } else {
                // Meses anteriores (Ene a Jun) están vencidos con recargos acumulativos del 10%
                $status = 'vencido';
                
                // Día 11 del mes de origen: Primer recargo del 10%
                $montoActual = $montoBase * 1.10;
                
                // Meses subsiguientes hasta hoy: Recargo acumulativo (+10% cada primer día de mes)
                $mesesTranscurridos = $current->diffInMonths($endMonth);
                for ($i = 0; $i < $mesesTranscurridos; $i++) {
                    $montoActual *= 1.10;
                }
            }

            // Nombre del mes formateado en español
            $mesNombre = ucfirst($current->translatedFormat('F'));
            $concepto = "Colegiatura Mensual ({$mesNombre} {$current->format('Y')})";

            Adeudo::create([
                'alumno_id' => $alumno->id,
                'tipo' => 'colegiatura',
                'concepto' => $concepto,
                'monto_base' => $montoBase,
                'monto_actual' => round($montoActual, 2),
                'periodo' => $periodo,
                'status' => $status
            ]);

            $this->command->info("- Colegiatura {$periodo} creada como '{$status}'. Monto actual calculado: $" . number_format($montoActual, 2));

            $current->addMonth();
        }

        $this->command->info('Seeder de prueba finalizado con éxito.');
    }
}
