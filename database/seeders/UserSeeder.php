<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un usuario de prueba si no existe
        $user = User::firstOrCreate(
            ['email' => 'admin@dame.com'],
            [
                'nombre' => 'Admin', 
                'apellido' => 'Admin', 
                'password' => bcrypt('password'),
                'edad' => 30, 
                'celular' => '123456789',
                'sexo' => 'F',
            ]
        );

        // Asignar el rol 'admin' al usuario
        $user->assignRole('admin');
    }
}
