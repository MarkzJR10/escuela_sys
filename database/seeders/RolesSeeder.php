<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['administrador', 'coordinador', 'maestro', 'socio', 'cajero', 'padre'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com'
        ], [
            'name' => 'Admin Principal',
            'password' => bcrypt('password')
        ]);

        $admin->assignRole('administrador');
    }
}
