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

                <h1 class="mb-4 text-2xl font-bold">Gestión de Compras - Proveedor</h1>
                <p class="mb-6 text-gray-600">Registra tus compras de productos para actualizar el inventario</p>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded hover:bg-blue-600">
                    📦 Nueva Compra
                </button>

                <!-- Tabla de compras -->
                <div v-if="compras && compras.length > 0" class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Fecha</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Total</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Productos</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="compra in compras" :key="compra.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    {{ formatearFecha(compra.fecha_compra) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 font-semibold">
                                    ${{ formatearPrecio(compra.total) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ compra.detalles ? compra.detalles.length : 0 }} productos
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <button @click="verDetalles(compra.id)"
                                        class="px-3 py-1 mr-2 text-white bg-green-500 rounded hover:bg-green-600">
                                        Ver Detalles
                                    </button>
                                    <button @click="showEditModal(compra)"
                                        class="px-3 py-1 mr-2 text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                        Editar
                                    </button>
                                    <button @click="deleteCompra(compra.id)"
                                        class="px-3 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay compras -->
                <div v-else class="p-8 text-center bg-gray-50 rounded-lg">
                    <div class="text-6xl mb-4">📦</div>
                    <p class="text-gray-500 mb-2">No hay compras registradas.</p>
                    <button @click="showCreateModal" class="mt-4 px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                        Registrar primera compra
                    </button>
                </div>

                <!-- Modal para crear/editar compra -->
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-4xl p-6 mx-4 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Compra' : 'Nueva Compra' }}</h2>

                        <form @submit.prevent="isEditing ? updateCompra() : createCompra()">
                            <!-- Información general -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label for="fecha_compra" class="block mb-2 text-sm font-medium text-gray-700">Fecha de Compra</label>
                                    <input 
                                        v-model="form.fecha_compra" 
                                        type="date" 
                                        id="fecha_compra"
                                        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        required
                                    />
                                    <span v-if="errors.fecha_compra" class="text-sm text-red-500">{{ errors.fecha_compra }}</span>
                                </div>

                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">Total Calculado</label>
                                    <div class="w-full p-2 bg-gray-100 border border-gray-300 rounded text-lg font-bold">
                                        ${{ formatearPrecio(totalCalculado) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Productos -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold">Productos a Comprar</h3>
                                    <button 
                                        type="button" 
                                        @click="agregarProducto"
                                        class="px-3 py-1 text-white bg-green-500 rounded hover:bg-green-600"
                                    >
                                        + Agregar Producto
                                    </button>
                                </div>

                                <div v-if="form.detalles.length === 0" class="p-4 text-center bg-gray-50 rounded border-2 border-dashed border-gray-300">
                                    <p class="text-gray-500">No hay productos agregados. Haz clic en "Agregar Producto" para comenzar.</p>
                                </div>

                                <!-- Lista de productos -->
                                <div v-else class="space-y-4">
                                    <div 
                                        v-for="(detalle, index) in form.detalles" 
                                        :key="index"
                                        class="p-4 border border-gray-200 rounded-lg bg-gray-50"
                                    >
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700">Producto</label>
                                                <select 
                                                    v-model="detalle.producto_id" 
                                                    @change="actualizarPrecioProducto(index)"
                                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    required
                                                >
                                                    <option value="">Seleccionar producto</option>
                                                    <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                                        {{ producto.nombre }} - Stock: {{ producto.cantidad }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700">Cantidad</label>
                                                <input 
                                                    v-model.number="detalle.cantidad" 
                                                    type="number" 
                                                    min="1"
                                                    @input="calcularTotal"
                                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    required
                                                />
                                            </div>

                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700">Precio Unitario</label>
                                                <input 
                                                    v-model.number="detalle.precio_unitario" 
                                                    type="number" 
                                                    step="0.01"
                                                    min="0"
                                                    @input="calcularTotal"
                                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    required
                                                />
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-medium">
                                                    Subtotal: ${{ formatearPrecio(detalle.cantidad * detalle.precio_unitario) }}
                                                </div>
                                                <button 
                                                    type="button" 
                                                    @click="eliminarProducto(index)"
                                                    class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                    :disabled="processing || form.detalles.length === 0"
                                >
                                    {{ processing ? 'Procesando...' : (isEditing ? 'Actualizar Compra' : 'Registrar Compra') }}
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
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ProveedorLayout from '@/Layouts/ProveedorLayout.vue';

const props = defineProps({
    compras: {
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

const form = ref({
    id: null,
    fecha_compra: new Date().toISOString().split('T')[0],
    total: 0,
    detalles: [],
});

const errors = ref({});

const totalCalculado = computed(() => {
    return form.value.detalles.reduce((total, detalle) => {
        return total + (detalle.cantidad * detalle.precio_unitario);
    }, 0);
});

const formatearPrecio = (precio) => {
    if (!precio) return '0.00';
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(precio);
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-BO');
};

const showCreateModal = () => {
    form.value = {
        id: null,
        fecha_compra: new Date().toISOString().split('T')[0],
        total: 0,
        detalles: [],
    };
    errors.value = {};
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (compra) => {
    form.value = {
        id: compra.id,
        fecha_compra: compra.fecha_compra,
        total: compra.total,
        detalles: compra.detalles ? compra.detalles.map(detalle => ({
            producto_id: detalle.producto_id,
            cantidad: detalle.cantidad,
            precio_unitario: detalle.precio_unitario,
        })) : [],
    };
    errors.value = {};
    showModal.value = true;
    isEditing.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = {
        id: null,
        fecha_compra: new Date().toISOString().split('T')[0],
        total: 0,
        detalles: [],
    };
    errors.value = {};
    processing.value = false;
};

const agregarProducto = () => {
    form.value.detalles.push({
        producto_id: '',
        cantidad: 1,
        precio_unitario: 0,
    });
};

const eliminarProducto = (index) => {
    form.value.detalles.splice(index, 1);
    calcularTotal();
};

const actualizarPrecioProducto = (index) => {
    const productoId = form.value.detalles[index].producto_id;
    const producto = props.productos.find(p => p.id == productoId);
    if (producto) {
        form.value.detalles[index].precio_unitario = producto.precio || 0;
        calcularTotal();
    }
};

const calcularTotal = () => {
    form.value.total = totalCalculado.value;
};

const createCompra = () => {
    processing.value = true;
    errors.value = {};
    
    const formData = {
        ...form.value,
        total: totalCalculado.value,
    };
    
    router.post(route('proveedor.compras.store'), formData, {
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

const updateCompra = () => {
    processing.value = true;
    errors.value = {};
    
    const formData = {
        fecha_compra: form.value.fecha_compra,
        total: totalCalculado.value,
    };
    
    router.put(route('proveedor.compras.update', form.value.id), formData, {
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

const deleteCompra = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta compra? Esta acción también revertirá los cambios en el inventario.')) {
        router.delete(route('proveedor.compras.destroy', id));
    }
};

const verDetalles = (compraId) => {
    router.visit(route('proveedor.detallecompras', compraId));
};
</script>