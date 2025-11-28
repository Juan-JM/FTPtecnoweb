<template>
    <div class="overflow-hidden bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <!-- Imagen del producto -->
        <div class="relative h-48 bg-gray-200">
            <img 
                :src="producto.imagen_url" 
                :alt="producto.nombre"
                class="w-full h-full object-cover"
                @error="handleImageError"
            />
            
            <!-- Badge de stock -->
            <div class="absolute top-2 right-2">
                <span 
                    :class="producto.cantidad > 0 ? 'bg-green-500' : 'bg-red-500'"
                    class="px-2 py-1 text-xs text-white rounded-full"
                >
                    {{ producto.cantidad > 0 ? `${producto.cantidad} disponibles` : 'Sin stock' }}
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
            
            <!-- Botones de acción -->
            <div class="flex gap-2">
                <!-- Botón Ver Detalles (para todos) -->
                <button 
                    @click="verDetalles"
                    class="flex-1 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors duration-200"
                >
                    Ver Detalles
                </button>
                
                <!-- Botón Comprar (solo para clientes) -->
                <button 
                    v-if="mostrarBotonComprar"
                    @click="agregarAlCarrito"
                    :disabled="producto.cantidad === 0 || agregando"
                    :class="[
                        'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                        producto.cantidad > 0 && !agregando
                            ? 'text-white bg-green-600 hover:bg-green-700'
                            : 'text-gray-400 bg-gray-200 cursor-not-allowed'
                    ]"
                >
                    <span v-if="agregando" class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Agregando...
                    </span>
                    <span v-else-if="producto.cantidad > 0">
                        🛒 Agregar al Carrito
                    </span>
                    <span v-else>
                        Sin Stock
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    producto: {
        type: Object,
        required: true
    },
    usuario: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['producto-agregado']);

const agregando = ref(false);

const mostrarBotonComprar = computed(() => {
    return props.usuario && props.usuario.rol === 'cliente';
});

const formatearPrecio = (precio) => {
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(precio);
};

const handleImageError = (event) => {
    event.target.src = '/images/producto-placeholder.jpg';
};

const verDetalles = () => {
    router.visit(`/productos/${props.producto.id}`);
};

const agregarAlCarrito = async () => {
    if (agregando.value || props.producto.cantidad === 0) return;
    
    agregando.value = true;
    
    try {
        const baseUrl = window.appBaseUrl || window.location.origin;
        const response = await fetch(`${baseUrl}/carrito/agregar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({
                producto_id: props.producto.id,
                cantidad: 1
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            emit('producto-agregado', {
                mensaje: data.message,
                carritoCount: data.carrito_count
            });
            
            // Mostrar notificación de éxito
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al agregar al carrito:', error);
        showNotification('Error al agregar el producto al carrito', 'error');
    } finally {
        agregando.value = false;
    }
};

const showNotification = (mensaje, tipo) => {
    // Crear notificación temporal
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