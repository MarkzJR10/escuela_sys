<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\Menu;
use App\Models\User;

DB::transaction(function() {
    $roleMaestro = Role::where('name', 'maestro')->first();
    $roleProfesor = Role::where('name', 'profesor')->first();

    if ($roleMaestro && $roleProfesor) {
        // Mover usuarios de maestro a profesor
        $users = User::role('maestro')->get();
        foreach ($users as $user) {
            $user->assignRole('profesor');
            $user->removeRole('maestro');
        }

        // Actualizar menús que usan maestro
        $menus = Menu::whereHas('roles', function($q) use ($roleMaestro) {
            $q->where('roles.id', $roleMaestro->id);
        })->get();

        foreach ($menus as $menu) {
            $menu->roles()->detach($roleMaestro->id);
            $menu->roles()->syncWithoutDetaching([$roleProfesor->id]);
        }

        // Eliminar el rol maestro
        $roleMaestro->delete();
        echo "Rol 'maestro' eliminado y fusionado con 'profesor'.\n";
    } else {
        echo "No se encontraron ambos roles para fusionar.\n";
    }
});
