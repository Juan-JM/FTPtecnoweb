<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos si no existen
        Permission::firstOrCreate(['name' => 'ver usuarios']);
        Permission::firstOrCreate(['name' => 'crear usuarios']);
        Permission::firstOrCreate(['name' => 'editar usuarios']);
        Permission::firstOrCreate(['name' => 'eliminar usuarios']);

        // Crear roles y asignar permisos si no existen
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $encargado = Role::firstOrCreate(['name' => 'encargado']);
        $encargado->givePermissionTo(['ver usuarios', 'editar usuarios']);

        $proveedor = Role::firstOrCreate(['name' => 'proveedor']);
        $proveedor->givePermissionTo(['ver usuarios', 'editar usuarios']);

        $usuario = Role::firstOrCreate(['name' => 'cliente']);
        $usuario->givePermissionTo(['ver usuarios']);
    }
}
