<template>
    <AppLayout>
        <AdminLayout>
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

                <h1 class="mb-4 text-2xl font-bold">Gestión de Inventario</h1>
                <p class="mb-6 text-gray-600">Controla los movimientos de entrada y salida de productos</p>

                <!-- Botones de acción -->
                <div class="flex gap-4 mb-6">
                    <button @click="showCreateModal" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                        📦 Registrar Movimiento
                    </button>
                    <button @click="verResumen" class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600">
                        📊 Resumen de Inventario
                    </button>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Producto:</label>
                        <select v-model="filtros.producto_id" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los productos</option>
                            <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                {{ producto.nombre }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Tipo:</label>
                        <select v-model="filtros.tipo_movimiento" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los tipos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="compra">Compra</option>
                            <option value="venta">Venta</option>
                            <option value="ajuste">Ajuste</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button @click="limpiarFiltros" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de movimientos -->
                <div v-if="inventariosFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Resultante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="inventario in inventariosFiltrados" :key="inventario.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ inventario.producto?.nombre || 'Producto no encontrado' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ inventario.producto?.categoria?.nombre || 'Sin categoría' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getTipoMovimientoClass(inventario.tipo_movimiento)" 
                                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ getTipoMovimientoText(inventario.tipo_movimiento) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span :class="getCantidadClass(inventario.tipo_movimiento)">
                                        {{ formatCantidad(inventario.tipo_movimiento, inventario.cantidad_movimiento) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ inventario.cantidad_disponible }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ inventario.created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button @click="showEditModal(inventario)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Editar
                                    </button>
                                    <button @click="eliminarMovimiento(inventario.id)"
                                        class="text-red-600 hover:text-red-900">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay movimientos -->
                <div v-else class="p-8 text-center bg-gray-50 rounded-lg">
                    <div class="text-6xl mb-4">📦</div>
                    <p class="text-gray-500 mb-2">No hay movimientos de inventario registrados.</p>
                    <button @click="showCreateModal" class="mt-4 px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                        Registrar primer movimiento
                    </button>
                </div>

                <!-- Modal para crear/editar movimiento -->
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-lg">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Movimiento' : 'Registrar Movimiento' }}</h2>

                        <form @submit.prevent="isEditing ? updateMovimiento() : createMovimiento()">
                            <div class="mb-4">
                                <label for="producto_id" class="block mb-2 text-sm font-medium text-gray-700">Producto</label>
                                <select 
                                    v-model="form.producto_id" 
                                    id="producto_id"
                                    :disabled="isEditing"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100"
                                    required
                                >
                                    <option value="">Seleccionar producto</option>
                                    <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                        {{ producto.nombre }} (Stock: {{ producto.cantidad }})
                                    </option>
                                </select>
                                <span v-if="errors.producto_id" class="text-sm text-red-500">{{ errors.producto_id }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="tipo_movimiento" class="block mb-2 text-sm font-medium text-gray-700">Tipo de Movimiento</label>
                                <select 
                                    v-model="form.tipo_movimiento" 
                                    id="tipo_movimiento"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                    <option value="entrada">🔄 Entrada (Incrementa stock)</option>
                                    <option value="salida">📤 Salida (Reduce stock)</option>
                                    <option value="compra">🛒 Compra (Incrementa stock)</option>
                                    <option value="venta">💰 Venta (Reduce stock)</option>
                                    <option value="ajuste">⚖️ Ajuste de inventario</option>
                                </select>
                                <span v-if="errors.tipo_movimiento" class="text-sm text-red-500">{{ errors.tipo_movimiento }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="cantidad_movimiento" class="block mb-2 text-sm font-medium text-gray-700">
                                    {{ form.tipo_movimiento === 'ajuste' ? 'Nueva Cantidad Total' : 'Cantidad del Movimiento' }}
                                </label>
                                <input 
                                    v-model.number="form.cantidad_movimiento" 
                                    type="number" 
                                    id="cantidad_movimiento"
                                    min="1"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                />
                                <span v-if="errors.cantidad_movimiento" class="text-sm text-red-500">{{ errors.cantidad_movimiento }}</span>
                            </div>

                            <!-- Preview del resultado -->
                            <div v-if="form.producto_id && form.tipo_movimiento && form.cantidad_movimiento" class="mb-4 p-3 bg-blue-50 rounded border">
                                <p class="text-sm text-blue-800">
                                    <strong>Vista previa:</strong><br>
                                    Stock actual: {{ getStockActual() }}<br>
                                    Después del movimiento: {{ calcularNuevoStock() }}
                                </p>
                            </div>

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
                                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
                                    :disabled="processing"
                                >
                                    {{ processing ? 'Procesando...' : (isEditing ? 'Actualizar' : 'Registrar') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </AppLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    inventarios: {
        type: Array,
        default: () => []
    },
    productos: {
        type: Array,
        default: () => []
    },
});

const showModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);

const form = ref({
    id: null,
    producto_id: '',
    tipo_movimiento: 'entrada',
    cantidad_movimiento: 1,
});

const filtros = ref({
    producto_id: '',
    tipo_movimiento: '',
});

const errors = ref({});

// Computed para filtrar inventarios
const inventariosFiltrados = computed(() => {
    let resultado = [...props.inventarios];
    
    if (filtros.value.producto_id) {
        resultado = resultado.filter(item => item.producto_id == filtros.value.producto_id);
    }
    
    if (filtros.value.tipo_movimiento) {
        resultado = resultado.filter(item => item.tipo_movimiento === filtros.value.tipo_movimiento);
    }
    
    return resultado;
});

// Métodos para formateo
const getTipoMovimientoClass = (tipo) => {
    const classes = {
        entrada: 'bg-green-100 text-green-800',
        salida: 'bg-red-100 text-red-800',
        compra: 'bg-blue-100 text-blue-800',
        venta: 'bg-purple-100 text-purple-800',
        ajuste: 'bg-yellow-100 text-yellow-800',
    };
    return classes[tipo] || 'bg-gray-100 text-gray-800';
};

const getTipoMovimientoText = (tipo) => {
    const texts = {
        entrada: '🔄 Entrada',
        salida: '📤 Salida',
        compra: '🛒 Compra',
        venta: '💰 Venta',
        ajuste: '⚖️ Ajuste',
    };
    return texts[tipo] || tipo;
};

const getCantidadClass = (tipo) => {
    return ['entrada', 'compra'].includes(tipo) ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
};

const formatCantidad = (tipo, cantidad) => {
    const signo = ['entrada', 'compra'].includes(tipo) ? '+' : '-';
    return tipo === 'ajuste' ? cantidad : `${signo}${cantidad}`;
};

const getStockActual = () => {
    const producto = props.productos.find(p => p.id == form.value.producto_id);
    return producto ? producto.cantidad : 0;
};

const calcularNuevoStock = () => {
    const stockActual = getStockActual();
    const { tipo_movimiento, cantidad_movimiento } = form.value;
    
    switch (tipo_movimiento) {
        case 'entrada':
        case 'compra':
            return stockActual + cantidad_movimiento;
        case 'salida':
        case 'venta':
            return stockActual - cantidad_movimiento;
        case 'ajuste':
            return cantidad_movimiento;
        default:
            return stockActual;
    }
};

// Métodos de modal
const showCreateModal = () => {
    form.value = {
        id: null,
        producto_id: '',
        tipo_movimiento: 'entrada',
        cantidad_movimiento: 1,
    };
    errors.value = {};
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (inventario) => {
    form.value = {
        id: inventario.id,
        producto_id: inventario.producto_id,
        tipo_movimiento: inventario.tipo_movimiento,
        cantidad_movimiento: inventario.cantidad_movimiento,
    };
    errors.value = {};
    showModal.value = true;
    isEditing.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = {
        id: null,
        producto_id: '',
        tipo_movimiento: 'entrada',
        cantidad_movimiento: 1,
    };
    errors.value = {};
    processing.value = false;
};

// Métodos CRUD
const createMovimiento = () => {
    processing.value = true;
    errors.value = {};
    
    router.post(route('admin.inventarios.store'), form.value, {
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            errors.value = error;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

const updateMovimiento = () => {
    processing.value = true;
    errors.value = {};
    
    router.put(route('admin.inventarios.update', form.value.id), form.value, {
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            errors.value = error;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

const eliminarMovimiento = (id) => {
    if (confirm('¿Estás seguro de eliminar este movimiento? Esta acción revertirá el cambio en el inventario.')) {
        router.delete(route('admin.inventarios.destroy', id));
    }
};

const limpiarFiltros = () => {
    filtros.value = {
        producto_id: '',
        tipo_movimiento: '',
    };
};

const verResumen = () => {
    router.visit(route('admin.inventarios.resumen'));
};
</script>