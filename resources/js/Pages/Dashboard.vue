<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { ref, onMounted } from 'vue';
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
        default: () => ({
            total_productos: 0,
            productos_en_stock: 0,
            productos_sin_stock: 0
        })
    }
});

const carritoCount = ref(0);

const crearVentaYRedirigir = async () => {
    try {
        const baseUrl = window.appBaseUrl || window.location.origin;
        const response = await fetch(`${baseUrl}/ventas/cliente`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({
                usuario_id: props.usuario.id,
                total: 0,
                estado: 'Pendiente',
            })
        });
        
        const data = await response.json();
        const ventaId = data.id;
        
        router.visit(`/ventas/cliente/${ventaId}/detalle`);
    } catch (error) {
        console.error('Error creating sale:', error);
        showNotification('Error al crear la venta', 'error');
    }
};

const handleProductoAgregado = (data) => {
    carritoCount.value = data.carritoCount;
    showNotification(data.mensaje, 'success');
};

const verCatalogo = () => {
    router.visit(route('catalogo'));
};

const verCarrito = () => {
    router.visit(route('carrito.index'));
};

const cargarContadorCarrito = async () => {
    if (props.usuario && props.usuario.rol === 'cliente') {
        try {
            const baseUrl = window.appBaseUrl || window.location.origin;
            const response = await fetch(`${baseUrl}/carrito/contar`);
            const data = await response.json();
            carritoCount.value = data.count;
        } catch (error) {
            console.error('Error al cargar contador del carrito:', error);
        }
    }
};

const showNotification = (mensaje, tipo) => {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 ${
        tipo === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = mensaje;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
};

onMounted(() => {
    cargarContadorCarrito();
});
</script>

<template>
    <AppLayout title="Inicio">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Bienvenido {{ usuario?.nombre || 'Usuario' }}
                    </h2>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Tienda Dame - Tu Tienda Virtual
                    </h2>
                </div>
                
                <!-- Botones de acción según el rol -->
                <div class="flex gap-3">
                    <!-- Para todos los usuarios autenticados -->
                    <button
                        @click="verCatalogo"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                        📚 Ver Catálogo Completo
                    </button>
                    
                    <!-- Solo para clientes -->
                    <button
                        v-if="usuario?.rol === 'cliente'"
                        @click="verCarrito"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-purple-600 rounded-lg shadow hover:bg-purple-700">
                        🛒 Mi Carrito ({{ carritoCount }})
                    </button>
                    
                    <!-- Solo para clientes -->
                    <button
                        v-if="usuario?.rol === 'cliente'"
                        @click="crearVentaYRedirigir"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-lg shadow hover:bg-green-700">
                        💰 Realizar Venta
                    </button>

                    <!-- Solo para proveedores -->
                     <button
                        v-if="usuario?.rol === 'proveedor'"
                         @click="router.visit(route('proveedor.compras'))"
                            class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-lg shadow hover:bg-green-700">
                        🛒 Mis Compras
                        </button>
                    
                    <!-- Solo para administradores -->
                    <button
                        v-if="usuario?.rol === 'admin'"
                        @click="router.visit(route('admin.productos'))"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-orange-600 rounded-lg shadow hover:bg-orange-700">
                        ⚙️ Panel Admin
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Estadísticas rápidas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-3xl">📦</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total de Productos
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ estadisticas.total_productos }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-3xl">✅</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Productos en Stock
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ estadisticas.productos_en_stock }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="usuario?.rol === 'cliente'" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-3xl">🛒</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            En tu Carrito
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ carritoCount }} productos
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos destacados -->
                <div class="bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">
                                Productos Destacados
                            </h3>
                            <button 
                                @click="verCatalogo"
                                class="text-blue-600 hover:text-blue-700 font-medium">
                                Ver todos →
                            </button>
                        </div>

                        <!-- Grid de productos (máximo 6 en el dashboard) -->
                        <div v-if="productos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <ProductCard 
                                v-for="producto in productos.slice(0, 6)" 
                                :key="producto.id"
                                :producto="producto"
                                :usuario="usuario"
                                @producto-agregado="handleProductoAgregado"
                            />
                        </div>

                        <!-- Mensaje cuando no hay productos -->
                        <div v-else class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">📦</div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No hay productos disponibles</h3>
                            <p class="text-gray-600 mb-6">Contacta al administrador para agregar productos al catálogo.</p>
                            <button 
                                v-if="usuario?.rol === 'admin'"
                                @click="router.visit(route('admin.productos'))"
                                class="bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                            >
                                Agregar Productos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Información para clientes -->
                    <div v-if="usuario?.rol === 'cliente'" class="bg-blue-50 rounded-lg p-6">
                        <h4 class="text-lg font-bold text-blue-900 mb-3">💡 Para Clientes</h4>
                        <ul class="text-blue-800 space-y-2">
                            <li>• Explora nuestro catálogo completo de productos</li>
                            <li>• Agrega productos a tu carrito</li>
                            <li>• Realiza compras de forma segura</li>
                            <li>• Consulta tu historial de pedidos</li>
                        </ul>
                    </div>

                    <!-- Información para administradores -->
                    <div v-if="usuario?.rol === 'admin'" class="bg-orange-50 rounded-lg p-6">
                        <h4 class="text-lg font-bold text-orange-900 mb-3">⚙️ Panel de Administración</h4>
                        <ul class="text-orange-800 space-y-2">
                            <li>• Gestiona productos e inventario</li>
                            <li>• Administra usuarios y roles</li>
                            <li>• Supervisa ventas y estadísticas</li>
                            <li>• Configura promociones</li>
                        </ul>
                    </div>

                    
                </div>
            </div>
        </div>
    </AppLayout>
</template>