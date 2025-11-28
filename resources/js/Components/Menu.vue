<template>
    <Menu v-if="usuario?.rol === 'admin'" as="div" class="relative inline-block text-left">
        <div>
            <MenuButton class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Menú
                <ChevronDownIcon class="-mr-1 text-gray-400 size-5" aria-hidden="true" />
            </MenuButton>
        </div>

        <transition 
            enter-active-class="transition duration-100 ease-out" 
            enter-from-class="transform scale-95 opacity-0" 
            enter-to-class="transform scale-100 opacity-100" 
            leave-active-class="transition duration-75 ease-in" 
            leave-from-class="transform scale-100 opacity-100" 
            leave-to-class="transform scale-95 opacity-0"
        >
            <MenuItems  class="absolute right-0 z-10 w-56 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black/5 focus:outline-none">
                <div  class="py-1">
                    <!-- Menús dinámicos desde la base de datos -->
                    <MenuItem v-for="menuItem in menus" :key="menuItem.id" v-slot="{ active }">
                        <a 
                            :href="menuItem.url" 
                            :class="[
                                active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 
                                'block px-4 py-2 text-sm'
                            ]"
                        >
                            {{ menuItem.nombre }}
                        </a>
                    </MenuItem>
                    
                    <!-- Separador si hay menús dinámicos -->
                    <div v-if="menus.length > 0" class="border-t border-gray-200"></div>
                    
                    <!-- Menús estáticos -->
                    <MenuItem v-slot="{ active }">
                        <a 
                            :href="route('admin.productos')" 
                            :class="[
                                active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 
                                'block px-4 py-2 text-sm'
                            ]"
                        >
                            Panel Administrativo
                        </a>
                    </MenuItem>
                    
                    <MenuItem v-slot="{ active }">
                        <a 
                            :href="route('estadisticas.index')" 
                            :class="[
                                active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 
                                'block px-4 py-2 text-sm'
                            ]"
                        >
                            Estadísticas
                        </a>
                    </MenuItem>

                    <!-- Mensaje de error si falla la carga -->
                    <div v-if="error" class="px-4 py-2 text-sm text-red-600">
                        Error al cargar menús
                    </div>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';

const menus = ref([]);
const error = ref(false);

const cargarMenus = async () => {
    try {
        // Usar la URL base configurada globalmente desde Ziggy
        const baseUrl = window.appBaseUrl || window.location.origin;
        const menuUrl = `${baseUrl}/menu`;
            
        const response = await fetch(menuUrl, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                menus.value = data.menus || [];
                error.value = false;
            } else {
                console.warn('La respuesta no es JSON válido');
                error.value = true;
                // Usar menús de respaldo
                useBackupMenus();
            }
        } else {
            console.error('Error al cargar menús:', response.statusText);
            error.value = true;
            useBackupMenus();
        }
    } catch (err) {
        console.error('Error al cargar menús:', err);
        error.value = true;
        useBackupMenus();
    }
};

const useBackupMenus = () => {
    // Menús de respaldo en caso de error
    menus.value = [
        { id: 1, nombre: 'Productos', url: '/productos' },
        { id: 2, nombre: 'Categorías', url: '/categorias' },
        { id: 3, nombre: 'Promociones', url: '/promociones' },
    ];
};

onMounted(() => {
    cargarMenus();
});
</script>