<template>
    <div class="flex flex-col min-h-screen">
        <header class="p-4 text-white bg-blue-600">
            <h1 class="text-lg font-bold">Panel Administrativo</h1>
        </header>

        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-64 p-4 text-white bg-gray-800">
                <nav>
                    <ul>
                        <li><Link :href="route('admin.ventas')" class="block px-4 py-2 hover:bg-gray-700">Ventas</Link></li>
                        <li><Link :href="route('admin.usuarios')" class="block px-4 py-2 hover:bg-gray-700">Usuarios</Link></li>
                        <li><Link :href="route('admin.categorias')" class="block px-4 py-2 hover:bg-gray-700">Categorías</Link></li>
                        <li><Link :href="route('admin.pagos')" class="block px-4 py-2 hover:bg-gray-700">Pagos</Link></li>
                        <li><Link :href="route('admin.productos')" class="block px-4 py-2 hover:bg-gray-700">Productos</Link></li>
                        <li><Link :href="route('admin.promociones')" class="block px-4 py-2 hover:bg-gray-700">Promociones</Link></li>
                        <li><Link :href="route('admin.inventarios')" class="block px-4 py-2 hover:bg-gray-700">Inventario</Link></li>
                        <li><Link :href="route('admin.compras')" class="block px-4 py-2 hover:bg-gray-700">Compras</Link></li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6 bg-gray-100">
                <slot />
            </main>
        </div>
        
        <!-- Footer con contador de visitas -->
        
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';
</script>