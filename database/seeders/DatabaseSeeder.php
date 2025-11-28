<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear un usuario de prueba
        User::factory()->create([
            'nombre' => 'Juan Pérez',
            'apellido' => 'Pérez',
            'email' => 'juan.perez@example.com',
            'password' => bcrypt('password123'),  // Asegúrate de usar bcrypt para la contraseña
            'edad' => 30,  // Asegúrate de incluir 'edad'
            'celular' => '123456789',  // Asegúrate de incluir 'celular'
            'sexo' => 'M',  // Asegúrate de incluir 'sexo'
        ]);
    }
}
