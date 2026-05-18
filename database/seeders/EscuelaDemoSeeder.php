<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Padre;
use App\Models\Profesor;
use App\Models\Alumno;
use App\Models\GradoGrupo;
use App\Models\Materia;
use App\Models\Calificacion;
use App\Models\Adeudo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Producto;
use App\Models\PeriodoControl;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EscuelaDemoSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Iniciando la carga de datos Demo...');

        $adminUser = User::first() ?? User::factory()->create(['name' => 'Admin']);

        // 1. Periodos de Control (Trimestres)
        foreach ([1, 2, 3] as $t) {
            PeriodoControl::firstOrCreate(
                ['trimestre' => $t],
                ['activo' => $t == 3] // Trimestre 3 activo por defecto
            );
        }

        // 2. Grados y Grupos
        $grupos = [];
        $gradosCombinaciones = [
            ['grado' => '1', 'grupo' => 'A'],
            ['grado' => '1', 'grupo' => 'B'],
            ['grado' => '2', 'grupo' => 'A'],
            ['grado' => '3', 'grupo' => 'A'],
        ];

        foreach ($gradosCombinaciones as $combo) {
            $grupos[] = GradoGrupo::firstOrCreate([
                'grado' => $combo['grado'],
                'grupo' => $combo['grupo']
            ], ['turno' => 'Matutino']);
        }

        // 3. Materias
        $nombresMaterias = ['Español', 'Matemáticas', 'Ciencias', 'Historia', 'Conducta'];
        $materiasIds = [];
        foreach ($nombresMaterias as $nm) {
            $materia = Materia::firstOrCreate(['nombre' => $nm], ['grado' => 'General']);
            $materiasIds[] = $materia->id;
        }

        // 4. Profesores
        for ($i = 1; $i <= 3; $i++) {
            $userProf = User::firstOrCreate(
                ['email' => "profesor{$i}@demo.com"],
                [
                    'name' => "Profesor Demo $i",
                    'password' => Hash::make('password')
                ]
            );
            if (!$userProf->hasRole('profesor')) {
                $userProf->assignRole('profesor');
            }

            Profesor::firstOrCreate(
                ['user_id' => $userProf->id],
                [
                    'nombre' => "Profe",
                    'apellido_paterno' => "Demo $i",
                    'apellido_materno' => "Test",
                    'genero' => 'M',
                    'telefono' => '555000000' . $i,
                ]
            );
        }

        // 5. Padres
        $padresIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $userPadre = User::firstOrCreate(
                ['email' => "padre{$i}@demo.com"],
                [
                    'name' => "Padre Demo $i",
                    'password' => Hash::make('password')
                ]
            );
            if (!$userPadre->hasRole('padre')) {
                $userPadre->assignRole('padre');
            }

            $padre = Padre::firstOrCreate(
                ['user_id' => $userPadre->id],
                [
                    'nombre' => "Padre",
                    'apellido_paterno' => "Familia $i",
                    'apellido_materno' => "Test",
                    'telefono' => '555111000' . $i,
                ]
            );
            $padresIds[] = $padre->id;
        }

        // 6. Alumnos, Calificaciones y Adeudos
        $nombres = ['Ana', 'Juan', 'Carlos', 'Maria', 'Luis', 'Sofia', 'Pedro', 'Laura', 'Miguel', 'Diana', 'Roberto', 'Carmen'];
        $apellidos = ['Garcia', 'Martinez', 'Lopez', 'Hernandez', 'Gonzalez', 'Perez', 'Sanchez', 'Ramirez', 'Torres', 'Flores'];

        for ($i = 1; $i <= 30; $i++) {
            $alumno = Alumno::firstOrCreate(
                ['matricula' => '2026' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                [
                    'nombre' => $nombres[array_rand($nombres)],
                    'apellido_paterno' => $apellidos[array_rand($apellidos)],
                    'apellido_materno' => $apellidos[array_rand($apellidos)],
                    'genero' => rand(0,1) ? 'M' : 'F',
                    'padre_id' => $padresIds[array_rand($padresIds)],
                    'grado_grupo_id' => $grupos[array_rand($grupos)]->id,
                    'colegiatura' => 2500,
                ]
            );

            // Calificaciones (sólo crear si no tiene, para no saturar si se corre varias veces)
            if ($alumno->calificaciones()->count() == 0) {
                foreach ([1, 2, 3] as $trimestre) {
                    foreach ($materiasIds as $mId) {
                        $materia = Materia::find($mId);
                        $puntaje = strtolower($materia->nombre) == 'conducta' 
                            ? (rand(80, 100) / 10) 
                            : (rand(60, 100) / 10);

                        Calificacion::firstOrCreate(
                            [
                                'alumno_id' => $alumno->id,
                                'materia_id' => $mId,
                                'trimestre' => $trimestre
                            ],
                            ['puntaje' => $puntaje]
                        );
                    }
                }
            }

            // Adeudos
            Adeudo::firstOrCreate(
                [
                    'alumno_id' => $alumno->id,
                    'concepto' => 'Inscripción Ciclo 2026-2027',
                    'periodo' => '2026-08'
                ],
                [
                    'monto_base' => 3000,
                    'monto_actual' => 3000,
                    'status' => 'pagado'
                ]
            );

            $adeudoMensual = Adeudo::firstOrCreate(
                [
                    'alumno_id' => $alumno->id,
                    'concepto' => 'Colegiatura Mensual (Mayo 2026)',
                    'periodo' => Carbon::now()->format('Y-m')
                ],
                [
                    'monto_base' => 2500,
                    'monto_actual' => 2500,
                    'status' => rand(0,1) ? 'pagado' : 'pendiente'
                ]
            );

            if ($adeudoMensual->status === 'pagado') {
                $pago = Pago::firstOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'total' => 2500
                    ],
                    [
                        'user_id' => $adminUser->id,
                        'fecha_pago' => Carbon::now()->subDays(rand(1, 10)),
                        'referencia_ticket' => 'DEMO-' . rand(1000, 9999)
                    ]
                );

                PagoDetalle::firstOrCreate([
                    'pago_id' => $pago->id,
                    'adeudo_id' => $adeudoMensual->id
                ], [
                    'monto_adeudo' => 2500,
                    'monto_pagado' => 2500
                ]);
            }
        }

        // 7. Productos
        $productosDemo = [
            ['nombre' => 'Jugo de Manzana', 'precio' => 15.00, 'stock' => 50],
            ['nombre' => 'Sándwich de Jamón', 'precio' => 35.00, 'stock' => 20],
            ['nombre' => 'Uniforme Deportivo Talla 10', 'precio' => 450.00, 'stock' => 15],
            ['nombre' => 'Libreta Profesional Cuadro Chico', 'precio' => 45.00, 'stock' => 100],
        ];

        foreach ($productosDemo as $pd) {
            Producto::firstOrCreate(
                ['nombre' => $pd['nombre']],
                ['precio' => $pd['precio'], 'stock' => $pd['stock'], 'activo' => true]
            );
        }

        $this->command->info('Escuela Demo data seeded successfully!');
    }
}
