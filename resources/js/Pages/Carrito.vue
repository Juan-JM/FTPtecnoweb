<template>
    <AppLayout title="Mi Carrito">
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Mi Carrito de Compras</h1>
                    <p class="text-gray-600">Revisa y modifica tus productos antes de finalizar la compra</p>
                </div>

                <!-- Notificaciones -->
                <div v-if="notification.show" 
                     class="fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all"
                     :class="{
                        'bg-green-500 text-white': notification.type === 'success',
                        'bg-red-500 text-white': notification.type === 'error',
                        'bg-blue-500 text-white': notification.type === 'info'
                     }">
                    {{ notification.message }}
                </div>

                <!-- Indicador de cambios -->
                <div v-if="hayChangios" class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    <div class="flex items-center justify-between">
                        <span>Tienes cambios sin guardar</span>
                        <button @click="restaurarCantidades" class="text-sm underline">
                            Deshacer cambios
                        </button>
                    </div>
                </div>

                <div v-if="carritoLocal.length > 0">
                    <!-- Items del carrito -->
                    <div class="bg-white rounded-lg shadow-md mb-6">
                        <div class="p-6">
                            <div v-for="item in carritoLocal" :key="item.id" class="flex items-center gap-4 py-4 border-b border-gray-200 last:border-b-0">
                                <!-- Imagen del producto -->
                                <div class="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                    <img 
                                        :src="item.producto.imagen_url" 
                                        :alt="item.producto.nombre"
                                        class="w-full h-full object-cover"
                                        @error="handleImageError"
                                    />
                                </div>
                                
                                <!-- Información del producto -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-medium text-gray-900 truncate">
                                        {{ item.producto.nombre }}
                                    </h3>
                                    <p class="text-gray-600 text-sm truncate">
                                        {{ item.producto.descripcion }}
                                    </p>
                                    <p class="text-blue-600 font-medium">
                                        ${{ formatearPrecio(item.precio_unitario) }} c/u
                                    </p>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Stock disponible: {{ item.producto.cantidad }}
                                    </div>
                                </div>
                                
                                <!-- Controles de cantidad -->
                                <div class="flex items-center gap-3">
                                    <button 
                                        @click="cambiarCantidadLocal(item.id, item.cantidad_local - 1)"
                                        :disabled="item.cantidad_local <= 1"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        -
                                    </button>
                                    
                                    <input 
                                        type="number" 
                                        :value="item.cantidad_local"
                                        @input="cambiarCantidadLocal(item.id, parseInt($event.target.value) || 1)"
                                        :max="item.producto.cantidad"
                                        min="1"
                                        class="w-16 text-center border rounded px-2 py-1"
                                    />
                                    
                                    <button 
                                        @click="cambiarCantidadLocal(item.id, item.cantidad_local + 1)"
                                        :disabled="item.cantidad_local >= item.producto.cantidad"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        +
                                    </button>
                                </div>
                                
                                <!-- Subtotal -->
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-900">
                                        ${{ formatearPrecio(calcularSubtotal(item)) }}
                                    </div>
                                    <div v-if="item.cantidad_local !== item.cantidad" class="text-xs text-orange-600">
                                        Nuevo subtotal
                                    </div>
                                </div>
                                
                                <!-- Botón eliminar -->
                                <button 
                                    @click="eliminarItem(item.id)"
                                    :disabled="eliminando[item.id]"
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-200 text-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    title="Eliminar producto"
                                >
                                    <span v-if="!eliminando[item.id]">×</span>
                                    <div v-else class="animate-spin rounded-full h-4 w-4 border-2 border-red-600 border-t-transparent"></div>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen del pedido -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4">Resumen del Pedido</h2>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Subtotal ({{ carritoLocal.length }} productos)</span>
                                <span class="font-medium">${{ formatearPrecio(subtotalCarrito) }}</span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>Descuentos</span>
                                <span class="font-medium">-${{ formatearPrecio(descuentosCarrito) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span>${{ formatearPrecio(totalCarrito) }}</span>
                            </div>
                            <div v-if="!carritoActualizado" class="text-orange-600 text-sm mt-2">
                                * Los totales se actualizarán al guardar los cambios
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="mt-6 space-y-3">
                            <!-- Botón actualizar carrito -->
                            <button 
                                v-if="hayChangios"
                                @click="actualizarCarrito"
                                :disabled="actualizandoCarrito"
                                class="w-full bg-orange-600 hover:bg-orange-700 disabled:bg-orange-400 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
                            >
                                <span v-if="!actualizandoCarrito">Actualizar Carrito</span>
                                <span v-else class="flex items-center justify-center">
                                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent mr-2"></div>
                                    Actualizando...
                                </span>
                            </button>
                            
                            <!-- Botón proceder al pago -->
                            <button 
                                @click="procederAComprar"
                                :disabled="!carritoActualizado || carritoLocal.length === 0"
                                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
                            >
                                Proceder al Pago
                            </button>
                            
                            <!-- Botones secundarios -->
                            <div class="flex gap-3">
                                <button 
                                    @click="verCatalogo"
                                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors"
                                >
                                    Seguir Comprando
                                </button>
                                
                                <button 
                                    @click="limpiarCarrito"
                                    :disabled="limpiandoCarrito"
                                    class="flex-1 bg-red-100 hover:bg-red-200 text-red-600 font-medium py-2 px-4 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <span v-if="!limpiandoCarrito">Limpiar Carrito</span>
                                    <span v-else>Limpiando...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estado vacío -->
                <div v-else class="text-center py-16">
                    <div class="mb-6">
                        <svg class="mx-auto h-24 w-24 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 4V2C7 1.45 7.45 1 8 1H16C16.55 1 17 1.45 17 2V4H20C20.55 4 21 4.45 21 5S20.55 6 20 6H19V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V6H4C3.45 6 3 5.55 3 5S3.45 4 4 4H7ZM9 3V4H15V3H9ZM7 6V19H17V6H7Z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Tu carrito está vacío</h3>
                    <p class="text-gray-600 mb-6">Agrega algunos productos para comenzar tu compra</p>
                    <button 
                        @click="verCatalogo"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors"
                    >
                        Explorar Productos
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    carritoItems: {
        type: Array,
        default: () => []
    },
    total: {
        type: [Number, String],
        default: 0
    }
});

// Estados reactivos
const carritoLocal = ref([]);
const carritoOriginal = ref([]);
const actualizando = reactive({});
const eliminando = ref({});
const actualizandoCarrito = ref(false);
const limpiandoCarrito = ref(false);
const carritoActualizado = ref(true);

// Estados para totales del carrito (solo se actualizan con el botón)
const subtotalCarrito = ref(0);
const descuentosCarrito = ref(0);
const totalCarrito = ref(0);

const notification = reactive({
    show: false,
    message: '',
    type: 'success'
});

// Inicializar carrito local
onMounted(() => {
    carritoLocal.value = props.carritoItems.map(item => ({
        ...item,
        cantidad_local: parseInt(item.cantidad) || 1
    }));
    carritoOriginal.value = JSON.parse(JSON.stringify(carritoLocal.value));
    
    // Calcular totales iniciales del carrito
    calcularTotalesCarrito();
});

// Computadas
const hayChangios = computed(() => {
    return carritoLocal.value.some(item => {
        const original = carritoOriginal.value.find(orig => orig.id === item.id);
        return original && parseInt(original.cantidad) !== parseInt(item.cantidad_local);
    });
});

// Funciones
const formatearPrecio = (precio) => {
    const numero = parseFloat(precio);
    if (isNaN(numero)) return '0.00';
    
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numero);
};

const calcularSubtotal = (item) => {
    const cantidad = item.cantidad_local || item.cantidad || 0;
    const precio = parseFloat(item.precio_unitario) || 0;
    return cantidad * precio;
};

const calcularTotalesCarrito = () => {
    // Usar cantidades del carritoOriginal para los totales oficiales del carrito
    const itemsParaCalcular = carritoActualizado.value ? carritoOriginal.value : carritoOriginal.value;
    
    subtotalCarrito.value = itemsParaCalcular.reduce((total, item) => {
        return total + (parseInt(item.cantidad) * parseFloat(item.precio_unitario));
    }, 0);
    
    // Calcular descuentos (aquí puedes agregar lógica de descuentos por producto)
    descuentosCarrito.value = itemsParaCalcular.reduce((totalDesc, item) => {
        // Ejemplo: si el producto tiene descuento
        const descuentoProducto = item.producto?.descuento || 0;
        const subtotalProducto = parseInt(item.cantidad) * parseFloat(item.precio_unitario);
        return totalDesc + (subtotalProducto * descuentoProducto / 100);
    }, 0);
    
    totalCarrito.value = subtotalCarrito.value - descuentosCarrito.value;
};

const handleImageError = (event) => {
    event.target.src = '/images/producto-placeholder.jpg';
};

const cambiarCantidadLocal = (itemId, nuevaCantidad) => {
    if (nuevaCantidad < 1) return;
    
    const item = carritoLocal.value.find(i => i.id === itemId);
    if (item && nuevaCantidad <= item.producto.cantidad) {
        item.cantidad_local = nuevaCantidad;
        carritoActualizado.value = false;
    }
};

const restaurarCantidades = () => {
    carritoLocal.value.forEach(item => {
        const original = carritoOriginal.value.find(orig => orig.id === item.id);
        if (original) {
            item.cantidad_local = original.cantidad;
        }
    });
    carritoActualizado.value = true;
};

const actualizarCarrito = async () => {
    actualizandoCarrito.value = true;
    
    try {
        const baseUrl = window.appBaseUrl || window.location.origin;
        
        // Actualizar cada item que cambió
        const promesas = carritoLocal.value
            .filter(item => {
                const original = carritoOriginal.value.find(orig => orig.id === item.id);
                return original && original.cantidad !== item.cantidad_local;
            })
            .map(async (item) => {
                const response = await fetch(`${baseUrl}/carrito/${item.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    },
                    body: JSON.stringify({ cantidad: item.cantidad_local })
                });
                
                if (!response.ok) {
                    throw new Error(`Error al actualizar producto ${item.producto.nombre}`);
                }
                
                return response.json();
            });
        
        await Promise.all(promesas);
        
        // Actualizar estado
        carritoOriginal.value = JSON.parse(JSON.stringify(carritoLocal.value));
        carritoActualizado.value = true;
        
        // Recalcular totales del carrito con los nuevos datos
        calcularTotalesCarrito();
        
        showNotification('Carrito actualizado exitosamente', 'success');
        
        // Recargar los datos usando Inertia
        setTimeout(() => {
            router.visit(route('carrito.index'));
        }, 1000);
        
    } catch (error) {
        console.error('Error al actualizar carrito:', error);
        showNotification('Error al actualizar el carrito', 'error');
    } finally {
        actualizandoCarrito.value = false;
    }
};

const eliminarItem = async (itemId) => {
    if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) return;
    
    eliminando.value = { ...eliminando.value, [itemId]: true };
    
    try {
        const baseUrl = window.appBaseUrl || window.location.origin;
        const response = await fetch(`${baseUrl}/carrito/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remover del carrito local
            carritoLocal.value = carritoLocal.value.filter(item => item.id !== itemId);
            carritoOriginal.value = carritoOriginal.value.filter(item => item.id !== itemId);
            
            showNotification('Producto eliminado del carrito', 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al eliminar producto:', error);
        showNotification('Error al eliminar el producto', 'error');
    } finally {
        const newEliminando = { ...eliminando.value };
        delete newEliminando[itemId];
        eliminando.value = newEliminando;
    }
};

const limpiarCarrito = async () => {
    if (!confirm('¿Estás seguro de que quieres limpiar todo el carrito?')) return;
    
    limpiandoCarrito.value = true;
    
    try {
        const baseUrl = window.appBaseUrl || window.location.origin;
        const response = await fetch(`${baseUrl}/carrito/limpiar`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            carritoLocal.value = [];
            carritoOriginal.value = [];
            showNotification('Carrito limpiado exitosamente', 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al limpiar carrito:', error);
        showNotification('Error al limpiar el carrito', 'error');
    } finally {
        limpiandoCarrito.value = false;
    }
};

const procederAComprar = () => {
    if (!carritoActualizado.value) {
        showNotification('Por favor actualiza el carrito primero', 'error');
        return;
    }
    
    // Ir a la página de pago
    router.visit(route('pagos.index'));
};

const verCatalogo = () => {
    router.visit(route('catalogo'));
};

const showNotification = (message, type = 'success') => {
    notification.message = message;
    notification.type = type;
    notification.show = true;
    
    setTimeout(() => {
        notification.show = false;
    }, 3000);
};
</script>