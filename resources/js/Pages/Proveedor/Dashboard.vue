<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    productos: {
        type: Array,
        default: () => []
    },
    usuario: {
        type: Object,
        default: null
    },
    estadisticas: {
        type: Object,
        default: () => ({})
    }
});

const formatearPrecio = (precio) => {
    if (!precio) return '0.00';
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(precio);
};

const formatearFecha = (fecha) => {
    if (!fecha) return 'Nunca';
    return new Date(fecha).toLocaleDateString('es-BO');
};

const irACompras = () => {
    router.visit(route('proveedor.compras'));
};

const verCatalogo = () => {
    router.visit(route('catalogo'));
};

const verMisCompras = () => {
    router.visit(route('proveedor.compras'));
    // El modal se abrirá automáticamente desde la página de compras
};
</script>

<template>
    <AppLayout title="Dashboard - Proveedor">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Bienvenido {{ usuario?.nombre || 'Proveedor' }}
                    </h2>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Panel de Proveedor - TeamCell
                    </h2>
                </div>
                
                <!-- Botones de acción para proveedores -->
                <div class="flex gap-3">
                    <button
                        @click="verCatalogo"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                        📚 Ver Catálogo
                    </button>
                    
                    <button
                        @click="irACompras"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-lg shadow hover:bg-green-700">
                        📦 Gestionar Compras
                    </button>
                    
                    <button
                        @click="nuevaCompra"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-purple-600 rounded-lg shadow hover:bg-purple-700">
                        ➕ Nueva Compra
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Estadísticas del proveedor -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Compras realizadas -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-3xl">📦</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Compras Realizadas
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ estadisticas.compras_realizadas || 0 }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total comprado -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-3xl">💰</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Invertido
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            ${{ formatearPrecio(estadisticas.total_comprado) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Última compra -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-3xl">📅</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Última Compra
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ formatearFecha(estadisticas.ultima_compra?.fecha_compra) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos disponibles -->
                <div class="bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">
                                Productos Disponibles para Compra
                            </h3>
                            <button 
                                @click="verCatalogo"
                                class="text-blue-600 hover:text-blue-700 font-medium">
                                Ver todos →
                            </button>
                        </div>

                        <!-- Grid de productos (máximo 6 en el dashboard) -->
                        <div v-if="productos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div 
                                v-for="producto in productos.slice(0, 6)" 
                                :key="producto.id"
                                class="overflow-hidden bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300"
                            >
                                <!-- Imagen del producto -->
                                <div class="relative h-48 bg-gray-200">
                                    <img 
                                        :src="producto.imagen_url || '/images/producto-placeholder.jpg'" 
                                        :alt="producto.nombre"
                                        class="w-full h-full object-cover"
                                    />
                                    
                                    <!-- Badge de stock -->
                                    <div class="absolute top-2 right-2">
                                        <span 
                                            :class="producto.cantidad > 0 ? 'bg-green-500' : 'bg-red-500'"
                                            class="px-2 py-1 text-xs text-white rounded-full"
                                        >
                                            {{ producto.cantidad > 0 ? `${producto.cantidad} en stock` : 'Sin stock' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Badge de categoría -->
                                    <div class="absolute top-2 left-2">
                                        <span class="px-2 py-1 text-xs text-white bg-blue-500 rounded-full">
                                            {{ producto.categoria?.nombre || 'Sin categoría' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Información del producto -->
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2">
                                        {{ producto.nombre }}
                                    </h3>
                                    
                                    <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                                        {{ producto.descripcion }}
                                    </p>
                                    
                                    <!-- Precio -->
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-2xl font-bold text-green-600">
                                            ${{ formatearPrecio(producto.precio) }}
                                        </span>
                                    </div>
                                    
                                    <!-- Botón para comprar -->
                                    <button 
                                        @click="irACompras"
                                        class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 transition-colors duration-200"
                                    >
                                        🛒 Comprar para Inventario
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje cuando no hay productos -->
                        <div v-else class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">📦</div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No hay productos disponibles</h3>
                            <p class="text-gray-600">Contacta al administrador para agregar productos al catálogo.</p>
                        </div>
                    </div>
                </div>

                <!-- Información para proveedores -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Guía para proveedores -->
                    <div class="bg-purple-50 rounded-lg p-6">
                        <h4 class="text-lg font-bold text-purple-900 mb-3">📋 Guía del Proveedor</h4>
                        <ul class="text-purple-800 space-y-2">
                            <li>• <strong>Nueva Compra:</strong> Registra productos que vas a suministrar</li>
                            <li>• <strong>Inventario:</strong> Tus compras actualizan automáticamente el stock</li>
                            <li>• <strong>Precios:</strong> Puedes establecer el precio de compra de cada producto</li>
                            <li>• <strong>Historial:</strong> Consulta todas tus compras anteriores</li>
                        </ul>
                    </div>

                    <!-- Información del sistema -->
                    <div class="bg-green-50 rounded-lg p-6">
                        <h4 class="text-lg font-bold text-green-900 mb-3">🔄 Proceso de Compra</h4>
                        <ul class="text-green-800 space-y-2">
                            <li>• <strong>1. Seleccionar:</strong> Elige los productos a comprar</li>
                            <li>• <strong>2. Cantidades:</strong> Define cuánto vas a suministrar</li>
                            <li>• <strong>3. Precios:</strong> Establece el costo de compra</li>
                            <li>• <strong>4. Confirmar:</strong> El inventario se actualiza automáticamente</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>