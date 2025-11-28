<template>
    <AppLayout>
        <ProveedorLayout>
            <div class="p-6">
                <!-- Mensajes de éxito/error -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="p-4 mb-4 text-green-800 bg-green-100 border border-green-300 rounded">
                    {{ $page.props.flash.success }}
                </div>
                
                <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="p-4 mb-4 text-red-800 bg-red-100 border border-red-300 rounded">
                    <ul>
                        <li v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <h1 class="mb-4 text-2xl font-bold">Gestión de Inventarios - Proveedor</h1>
                <p class="mb-6 text-gray-600">Registra movimientos de entrada de productos al inventario</p>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-green-500 rounded hover:bg-green-600">
                    📦 Movimiento de Entrada
                </button>

                <!-- Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Producto:</label>
                        <select v-model="filtroProducto" class="w-full p-2 border border-gray-300 rounded">
                            <option value="">Todos los productos</option>
                            <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                {{ producto.nombre }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Movimiento:</label>
                        <select v-model="filtroTipoMovimiento" class="w-full p-2 border border-gray-300 rounded">
                            <option value="">Todos los movimientos</option>
                            <option value="entrada">Entradas</option>
                            <option value="salida">Salidas</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button @click="limpiarFiltros" class="w-full px-4 py-2 text-gray-600 bg-gray-200 rounded hover:bg-gray-300">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de inventarios -->
                <div v-if="inventariosFiltrados.length > 0" class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Producto</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Movimiento</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Cantidad</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Stock Actual</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Fecha</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="inventario in inventariosFiltrados" :key="inventario.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <div>
                                        <div class="font-medium">{{ inventario.producto.nombre }}</div>
                                        <div class="text-xs text-gray-500">{{ inventario.producto.categoria?.nombre }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <span :class="inventario.tipo_movimiento === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ inventario.tipo_movimiento === 'entrada' ? '📥 Entrada' : '📤 Salida' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 font-semibold">
                                    {{ inventario.cantidad_movimiento }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 font-semibold">
                                    {{ inventario.cantidad_disponible }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <div>
                                        <div>{{ formatearFecha(inventario.fecha_ultima_actualizacion) }}</div>
                                        <div class="text-xs text-gray-500">{{ formatearHora(inventario.created_at) }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <!-- Solo mostrar acciones para entradas recientes (24 horas) -->
                                    <div v-if="inventario.tipo_movimiento === 'entrada' && esReciente(inventario.created_at)">
                                        <button @click="showEditModal(inventario)"
                                            class="px-3 py-1 mr-2 text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                            Editar
                                        </button>
                                        <button @click="deleteInventario(inventario.id)"
                                            class="px-3 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                                            Eliminar
                                        </button>
                                    </div>
                                    <div v-else class="text-gray-400 text-xs">
                                        {{ inventario.tipo_movimiento === 'salida' ? 'Solo lectura' : 'Expirado (>24h)' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay inventarios -->
                <div v-else class="p-8 text-center bg-gray-50 rounded-lg">
                    <div class="text-6xl mb-4">📊</div>
                    <p class="text-gray-500 mb-2">No hay movimientos de inventario registrados.</p>
                    <button @click="showCreateModal" class="mt-4 px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600">
                        Registrar primer movimiento
                    </button>
                </div>

                <!-- Modal para crear/editar movimiento -->
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-lg">
                        <h2 class="mb-4 text-xl font-bold">
                            {{ isEditing ? 'Editar Movimiento' : 'Nuevo Movimiento de Entrada' }}
                        </h2>

                        <form @submit.prevent="isEditing ? updateInventario() : createInventario()">
                            <div class="mb-4">
                                <label for="producto_id" class="block mb-2 text-sm font-medium text-gray-700">Producto</label>
                                <select 
                                    v-model="form.producto_id" 
                                    id="producto_id"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    required
                                    :disabled="isEditing"
                                >
                                    <option value="">Seleccionar producto</option>
                                    <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                        {{ producto.nombre }} (Stock: {{ producto.cantidad }})
                                    </option>
                                </select>
                                <span v-if="errors.producto_id" class="text-sm text-red-500">{{ errors.producto_id }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="cantidad_movimiento" class="block mb-2 text-sm font-medium text-gray-700">Cantidad de Entrada</label>
                                <input 
                                    v-model.number="form.cantidad_movimiento" 
                                    type="number" 
                                    min="1"
                                    id="cantidad_movimiento"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    required
                                />
                                <span v-if="errors.cantidad_movimiento" class="text-sm text-red-500">{{ errors.cantidad_movimiento }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="fecha_ultima_actualizacion" class="block mb-2 text-sm font-medium text-gray-700">Fecha del Movimiento</label>
                                <input 
                                    v-model="form.fecha_ultima_actualizacion" 
                                    type="date" 
                                    id="fecha_ultima_actualizacion"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    required
                                />
                                <span v-if="errors.fecha_ultima_actualizacion" class="text-sm text-red-500">{{ errors.fecha_ultima_actualizacion }}</span>
                            </div>

                            <!-- Campo oculto para tipo de movimiento -->
                            <input type="hidden" v-model="form.tipo_movimiento" value="entrada" />

                            <div class="flex justify-end space-x-2">
                                <button 
                                    type="button" 
                                    @click="closeModal"
                                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="submit" 
                                    class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600"
                                    :disabled="processing"
                                >
                                    {{ processing ? 'Procesando...' : (isEditing ? 'Actualizar' : 'Registrar Entrada') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </ProveedorLayout>
    </AppLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ProveedorLayout from '@/Layouts/ProveedorLayout.vue';

const props = defineProps({
    inventarios: {
        type: Array,
        default: () => []
    },
    productos: {
        type: Array,
        default: () => []
    },
    usuario: {
        type: Object,
        default: null
    },
});

const showModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);
const filtroProducto = ref('');
const filtroTipoMovimiento = ref('');

const form = ref({
    id: null,
    producto_id: '',
    cantidad_movimiento: '',
    tipo_movimiento: 'entrada',
    fecha_ultima_actualizacion: new Date().toISOString().split('T')[0],
});

const errors = ref({});

const inventariosFiltrados = computed(() => {
    let inventarios = [...props.inventarios];
    
    if (filtroProducto.value) {
        inventarios = inventarios.filter(inv => inv.producto_id == filtroProducto.value);
    }
    
    if (filtroTipoMovimiento.value) {
        inventarios = inventarios.filter(inv => inv.tipo_movimiento === filtroTipoMovimiento.value);
    }
    
    return inventarios;
});

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-BO');
};

</script>