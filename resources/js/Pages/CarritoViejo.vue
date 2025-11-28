<template>
    <AppLayout title="Mi Carrito">
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Mi Carrito de Compras</h1>
                    <p class="text-gray-600">Revisa y modifica tus productos antes de finalizar la compra</p>
                </div>

                <div v-if="carritoItems.length > 0">
                    <!-- Items del carrito -->
                    <div class="bg-white rounded-lg shadow-md mb-6">
                        <div class="p-6">
                            <div v-for="item in carritoItems" :key="item.id" class="flex items-center gap-4 py-4 border-b border-gray-200 last:border-b-0">
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
                                </div>
                                
                                <!-- Controles de cantidad -->
                                <div class="flex items-center gap-3">
                                    <button 
                                        @click="cambiarCantidad(item.id, item.cantidad - 1)"
                                        :disabled="item.cantidad <= 1 || actualizando[item.id]"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        -
                                    </button>
                                    
                                    <span class="w-12 text-center font-medium">
                                        {{ item.cantidad }}
                                    </span>
                                    
                                    <button 
                                        @click="cambiarCantidad(item.id, item.cantidad + 1)"
                                        :disabled="item.cantidad >= item.producto.cantidad || actualizando[item.id]"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        +
                                    </button>
                                </div>
                                
                                <!-- Subtotal -->
                                <div class="text-right min-w-0 w-24">
                                    <p class="text-lg font-bold text-gray-900">
                                        ${{ formatearPrecio(item.subtotal) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Stock: {{ item.producto.cantidad }}
                                    </p>
                                </div>
                                
                                <!-- Botón eliminar -->
                                <button 
                                    @click="eliminarItem(item.id)"
                                    :disabled="eliminando[item.id]"
                                    class="w-8 h-8 flex items-center justify-center rounded-full text-red-600 hover:bg-red-50 disabled:opacity-50"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen del pedido -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Resumen del Pedido</h2>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal ({{ totalItems }} productos)</span>
                                    <span class="font-medium">${{ formatearPrecio(total) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Envío</span>
                                    <span class="font-medium">Gratis</span>
                                </div>
                                <hr class="my-2">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span class="text-green-600">${{ formatearPrecio(total) }}</span>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="space-y-3">
                                <button 
                                    @click="procederCompra"
                                    :disabled="procesandoCompra"
                                    class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <span v-if="procesandoCompra">Procesando...</span>
                                    <span v-else>Proceder a Comprar</span>
                                </button>
                                
                                <button 
                                    @click="limpiarCarrito"
                                    :disabled="limpiandoCarrito"
                                    class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg font-medium hover:bg-gray-300 disabled:opacity-50 transition-colors"
                                >
                                    <span v-if="limpiandoCarrito">Limpiando...</span>
                                    <span v-else">Vaciar Carrito</span>
                                </button>
                                
                                <button 
                                    @click="continuarComprando"
                                    class="w-full text-blue-600 py-2 px-4 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                                >
                                    ← Continuar Comprando
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Carrito vacío -->
                <div v-else class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">🛒</div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Tu carrito está vacío</h3>
                    <p class="text-gray-600 mb-6">¡Agrega algunos productos para comenzar!</p>
                    <button 
                        @click="continuarComprando"
                        class="bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                    >
                        Explorar Productos
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    carritoItems: Array,
    total: Number,
});

const actualizando = ref({});
const eliminando = ref({});
const procesandoCompra = ref(false);
const limpiandoCarrito = ref(false);

const totalItems = computed(() => {
    return props.carritoItems.reduce((sum, item) => sum + item.cantidad, 0);
});

const formatearPrecio = (precio) => {
    const numero = parseFloat(precio);
    if (isNaN(numero)) return '0.00';
    
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numero);
};

const handleImageError = (event) => {
    event.target.src = '/images/producto-placeholder.jpg';
};

const cambiarCantidad = async (itemId, nuevaCantidad) => {
    if (nuevaCantidad < 1) return;
    
    actualizando.value[itemId] = true;
    
    try {
        const baseUrl = window.appBaseUrl || window.location.origin;
        const response = await fetch(`${baseUrl}/carrito/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({ cantidad: nuevaCantidad })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Recargar la página para actualizar los datos
            router.reload();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al actualizar cantidad:', error);
        showNotification('Error al actualizar la cantidad', 'error');
    } finally {
        actualizando.value[itemId] = false;
    }
};

const eliminarItem = async (itemId) => {
    if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) return;
    
    eliminando.value[itemId] = true;
    
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
            router.reload();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al eliminar producto:', error);
        showNotification('Error al eliminar el producto', 'error');
    } finally {
        eliminando.value[itemId] = false;
    }
};

const limpiarCarrito = async () => {
    if (!confirm('¿Estás seguro de que quieres vaciar todo el carrito?')) return;
    
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
            router.reload();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al limpiar carrito:', error);
        showNotification('Error al vaciar el carrito', 'error');
    } finally {
        limpiandoCarrito.value = false;
    }
};

const procederCompra = () => {
    // Aquí puedes redirigir a la página de checkout
    router.visit(route('pagos.index'));
};

const continuarComprando = () => {
    router.visit(route('dashboard'));
};

const showNotification = (mensaje, tipo) => {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${
        tipo === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = mensaje;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
};
</script>