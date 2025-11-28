<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'nombre' => 'Productos',
                'url' => '/productos',
            ],
            [
                'nombre' => 'Categorías',
                'url' => '/categorias',
            ],
            [
                'nombre' => 'Promociones',
                'url' => '/promociones',
            ],
            [
                'nombre' => 'Nosotros',
                'url' => '/nosotros',
            ],
            [
                'nombre' => 'Contacto',
                'url' => '/contacto',
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}