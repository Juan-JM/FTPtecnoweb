<template>
    <AppLayout title="Mis Cuotas Pendientes">
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Mis Cuotas Pendientes</h1>
                    <p class="text-gray-600">Aquí puedes ver y pagar tus cuotas pendientes</p>
                </div>

                <!-- Loading -->
                <div v-if="cargando" class="bg-white rounded-lg shadow p-6 text-center">
                    <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto"></div>
                    <p class="mt-2 text-gray-600">Cargando cuotas...</p>
                </div>

                <!-- Sin cuotas -->
                <div v-else-if="cuotas.length === 0" class="bg-white rounded-lg shadow p-6 text-center">
                    <div class="text-4xl mb-4">✅</div>
                    <h2 class="text-xl font-semibold text-gray-800">¡Sin cuotas pendientes!</h2>
                    <p class="text-gray-600 mt-2">No tienes cuotas pendientes de pago.</p>
                    <button 
                        @click="irAlCatalogo" 
                        class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                    >
                        Ir al catálogo
                    </button>
                </div>

                <!-- Lista de cuotas -->
                <div v-else class="space-y-4">
                    <div 
                        v-for="cuota in cuotas" 
                        :key="cuota.cuota_id"
                        :class="[
                            'bg-white rounded-lg shadow p-6',
                            cuota.esta_vencida ? 'border-l-4 border-red-500' : 'border-l-4 border-yellow-400'
                        ]"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    Venta #{{ cuota.venta_id }} - Cuota {{ cuota.numero_cuota }}/2
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Vencimiento: {{ formatearFecha(cuota.fecha_vencimiento) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-gray-900">${{ formatearPrecio(cuota.monto) }}</p>
                                <span 
                                    :class="[
                                        'text-xs px-2 py-1 rounded-full',
                                        cuota.esta_vencida 
                                            ? 'bg-red-100 text-red-800' 
                                            : 'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ cuota.esta_vencida ? '⚠️ Vencida' : '⏳ Pendiente' }}
                                </span>
                            </div>
                        </div>

                        <!-- Productos de la venta -->
                        <div class="mb-4 p-3 bg-gray-50 rounded">
                            <p class="text-sm font-medium text-gray-700 mb-1">Productos:</p>
                            <ul class="text-sm text-gray-600">
                                <li v-for="(prod, idx) in cuota.productos" :key="idx">
                                    {{ prod.cantidad }}x {{ prod.nombre }}
                                </li>
                            </ul>
                            <p class="text-sm text-gray-500 mt-2">
                                Total de la venta: ${{ formatearPrecio(cuota.total_venta) }}
                            </p>
                        </div>

                        <!-- Botón de pagar -->
                        <button 
                            @click="pagarCuota(cuota)"
                            :disabled="procesandoPago === cuota.cuota_id"
                            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
                        >
                            <span v-if="procesandoPago === cuota.cuota_id" class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Generando QR...
                            </span>
                            <span v-else>💳 Pagar Cuota</span>
                        </button>
                    </div>
                </div>

                <!-- Modal de QR -->
                <div v-if="mostrarModalQR" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                        <h3 class="text-xl font-semibold mb-4 text-center">Escanea para pagar</h3>
                        
                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-600">Cuota {{ qrData.cuota_numero }}/2</p>
                            <p class="text-2xl font-bold text-gray-900">${{ formatearPrecio(qrData.amount) }}</p>
                        </div>
                        
                        <div class="flex justify-center mb-4">
                            <img :src="qrData.qrImage" alt="QR Code" class="max-w-xs" />
                        </div>

                        <!-- Verificando pago -->
                        <div v-if="verificandoPago" class="flex items-center justify-center text-blue-600 mb-4">
                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Esperando confirmación...</span>
                        </div>

                        <!-- Pago completado -->
                        <div v-if="pagoExitoso" class="text-center mb-4 p-4 bg-green-50 rounded-lg">
                            <div class="text-4xl mb-2">🎉</div>
                            <p class="text-green-800 font-semibold">¡Cuota pagada exitosamente!</p>
                        </div>
                        
                        <button 
                            @click="cerrarModal" 
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg"
                        >
                            {{ pagoExitoso ? 'Cerrar' : 'Cancelar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    usuario: Object
});

const cuotas = ref([]);
const cargando = ref(true);
const procesandoPago = ref(null);
const mostrarModalQR = ref(false);
const qrData = ref({});
const verificandoPago = ref(false);
const pagoExitoso = ref(false);
let pollingInterval = null;

onMounted(async () => {
    await cargarCuotas();
});

onUnmounted(() => {
    detenerPolling();
});

const cargarCuotas = async () => {
    try {
        cargando.value = true;
        const { data } = await axios.get('/mis-cuotas-pendientes');
        if (data.success) {
            cuotas.value = data.cuotas;
        }
    } catch (error) {
        console.error('Error cargando cuotas:', error);
    } finally {
        cargando.value = false;
    }
};

const pagarCuota = async (cuota) => {
    procesandoPago.value = cuota.cuota_id;
    
    try {
        const { data } = await axios.post('/generar-qr-cuota', {
            venta_id: cuota.venta_id,
            tcRazonSocial: props.usuario?.name || 'Cliente',
            tcCiNit: '0',
            tnTelefono: '70000000',
            tcCorreo: props.usuario?.email || 'cliente@example.com'
        });

        if (data.success) {
            let qrImage = data.data.qrImage;
            if (qrImage && !qrImage.startsWith('data:image')) {
                qrImage = `data:image/png;base64,${qrImage}`;
            }
            
            qrData.value = {
                ...data.data,
                qrImage: qrImage,
                cuota_id: cuota.cuota_id
            };
            mostrarModalQR.value = true;
            pagoExitoso.value = false;
            
            // Iniciar polling
            iniciarPolling(data.data.paymentNumber);
        }
    } catch (error) {
        console.error('Error generando QR:', error);
        alert('Error al generar el código QR');
    } finally {
        procesandoPago.value = null;
    }
};

const iniciarPolling = (paymentNumber) => {
    verificandoPago.value = true;
    
    pollingInterval = setInterval(async () => {
        try {
            const { data } = await axios.post('/verificar-pago', { paymentNumber });
            
            if (data.pagado) {
                pagoExitoso.value = true;
                verificandoPago.value = false;
                detenerPolling();
                
                // Recargar cuotas después de un momento
                setTimeout(() => {
                    cargarCuotas();
                }, 2000);
            }
        } catch (error) {
            console.error('Error verificando pago:', error);
        }
    }, 5000);
};

const detenerPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    verificandoPago.value = false;
};

const cerrarModal = () => {
    mostrarModalQR.value = false;
    detenerPolling();
    if (pagoExitoso.value) {
        cargarCuotas();
    }
};

const irAlCatalogo = () => {
    router.visit('/catalogo');
};

const formatearPrecio = (precio) => {
    const numero = parseFloat(precio);
    if (isNaN(numero)) return '0.00';
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numero);
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>
