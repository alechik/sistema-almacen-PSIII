<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear roles
        $roles = [
            'admin',
            'propietario',
            'administrador',
            'transportista',
            'operador'
        ];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // 2. Crear permisos base (puedes ampliar luego)
        $permissions = [
            'dashboard.view',
            'users.manage',
            'roles.manage',
            'products.manage',
            'inventory.manage',
            'reports.view'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 3. Dar TODOS los permisos al rol admin
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->syncPermissions(Permission::all());

        // 4. Crear usuario administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'full_name' => 'Administrador del Sistema',
                'name' => 'admin',
                'password' => bcrypt('admin123'),
                'estado' => 'ACTIVO',
                'company' => 'Mi Empresa',
                'phone_number' => '70000000',
            ]
        );

        // 5. Asignarle rol
        $admin->assignRole('admin');
    }
}
