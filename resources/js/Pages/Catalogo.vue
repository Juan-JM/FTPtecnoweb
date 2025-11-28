<template>
    <AppLayout title="Catálogo">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">Catálogo de Productos</h2>
                            <p class="text-gray-600 mt-1">{{ productos.length }} productos disponibles</p>
                        </div>
                    </div>

                    <!-- Filtros por categoría -->
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-2">
                            <button 
                                @click="categoriaSeleccionada = null"
                                :class="[
                                    'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                    categoriaSeleccionada === null 
                                        ? 'bg-blue-600 text-white' 
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]"
                            >
                                Todas
                            </button>
                            <button 
                                v-for="categoria in categorias" 
                                :key="categoria.id"
                                @click="categoriaSeleccionada = categoria.id"
                                :class="[
                                    'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                    categoriaSeleccionada === categoria.id 
                                        ? 'bg-blue-600 text-white' 
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]"
                            >
                                {{ categoria.nombre }}
                            </button>
                        </div>
                    </div>

                    <!-- Grid de productos -->
                    <div v-if="productosFiltrados.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                        <ProductCard 
                            v-for="producto in productosFiltrados" 
                            :key="producto.id"
                            :producto="producto"
                            :usuario="usuario"
                            @producto-agregado="handleProductoAgregado"
                        />
                    </div>

                    <!-- Sin productos -->
                    <div v-else class="text-center py-12">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-4h-2M7 9h-.01M13 9h-.01" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos disponibles</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ categoriaSeleccionada ? 'No hay productos en esta categoría.' : 'No hay productos en el catálogo.' }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    productos: {
        type: Array,
        default: () => []
    },
    categorias: {
        type: Array,
        default: () => []
    },
    usuario: {
        type: Object,
        default: null
    }
});

const categoriaSeleccionada = ref(null);

const productosFiltrados = computed(() => {
    if (categoriaSeleccionada.value === null) {
        return props.productos;
    }
    
    return props.productos.filter(producto => 
        producto.categoria && producto.categoria.id === categoriaSeleccionada.value
    );
});

const handleProductoAgregado = (data) => {
    // Manejar cuando se agrega un producto al carrito
    console.log('Producto agregado:', data);
    // Aquí puedes agregar notificaciones si las tienes configuradas
};
</script>