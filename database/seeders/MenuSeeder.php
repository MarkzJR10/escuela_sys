<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Menu;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['text' => 'Usuarios', 'url' => 'users', 'icon' => 'fas fa-fw fa-users'],
            ['text' => 'Alumnos', 'url' => 'alumnos', 'icon' => 'fas fa-fw fa-user-graduate'],
            ['text' => 'Profesores', 'url' => 'profesores', 'icon' => 'fas fa-fw fa-user-tie'],
            ['text' => 'Grados y Grupos', 'url' => 'grado_grupos', 'icon' => 'fas fa-fw fa-chalkboard-teacher'],
            ['text' => 'Materias', 'url' => 'materias', 'icon' => 'fas fa-fw fa-book'],
            ['text' => 'Calificaciones', 'url' => 'calificaciones', 'icon' => 'fas fa-fw fa-star'],
            ['text' => 'Control de Periodos', 'url' => 'periodos', 'icon' => 'fas fa-fw fa-clock'],
            ['text' => 'Asistencia', 'url' => 'asistencias', 'icon' => 'fas fa-fw fa-clipboard-check'],
        ];

        $adminRole = Role::where('name', 'administrador')->first();

        foreach ($menus as $m) {
            $menu = Menu::firstOrCreate($m);
            if ($adminRole) {
                // Assign this menu to administrador by default going through menu relation
                $menu->roles()->syncWithoutDetaching([$adminRole->id]);
            }
        }
    }
}
