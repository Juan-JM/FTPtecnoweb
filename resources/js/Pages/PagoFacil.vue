<template>
    <AppLayout title="Procesar Pago">
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Procesar Pago</h1>
                    <p class="text-gray-600">Completa la información para procesar tu pago</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Formulario de Pago -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-6">Información de Pago</h2>
                        
                        <form @submit.prevent="enviarDatos">
                            <!-- Información Personal -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos del Cliente</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="tcRazonSocial" class="block text-sm font-medium text-gray-700 mb-2">
                                            Nombre Completo *
                                        </label>
                                        <input 
                                            v-model="form.tcRazonSocial" 
                                            type="text" 
                                            id="tcRazonSocial" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                    
                                    <div>
                                        <label for="tcCiNit" class="block text-sm font-medium text-gray-700 mb-2">
                                            CI/NIT *
                                        </label>
                                        <input 
                                            v-model="form.tcCiNit" 
                                            type="text" 
                                            id="tcCiNit" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label for="tcCorreo" class="block text-sm font-medium text-gray-700 mb-2">
                                            Correo Electrónico *
                                        </label>
                                        <input 
                                            v-model="form.tcCorreo" 
                                            type="email" 
                                            id="tcCorreo" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                    
                                    <div>
                                        <label for="tnTelefono" class="block text-sm font-medium text-gray-700 mb-2">
                                            Teléfono *
                                        </label>
                                        <input 
                                            v-model="form.tnTelefono" 
                                            type="tel" 
                                            id="tnTelefono" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Método de Pago -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Método de Pago</h3>
                                
                                <div class="mb-4">
                                    <label for="tnTipoServicio" class="block text-sm font-medium text-gray-700 mb-2">
                                        Selecciona el tipo de pago
                                    </label>
                                    <select 
                                        v-model="form.tnTipoServicio" 
                                        id="tnTipoServicio" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="1">Pago QR</option>
                                        <option value="2">Tigo Money</option>
                                    </select>
                                </div>

                                <!-- Tipo de Pago: Contado o Crédito -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Modalidad de Pago
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label 
                                            :class="[
                                                'flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-all',
                                                form.tipoPago === 'contado' 
                                                    ? 'border-blue-500 bg-blue-50 text-blue-700' 
                                                    : 'border-gray-300 hover:border-gray-400'
                                            ]"
                                        >
                                            <input 
                                                type="radio" 
                                                v-model="form.tipoPago" 
                                                value="contado" 
                                                class="sr-only"
                                            />
                                            <div class="text-center">
                                                <div class="font-semibold">💵 Contado</div>
                                                <div class="text-xs text-gray-500">Pago único</div>
                                            </div>
                                        </label>
                                        
                                        <label 
                                            :class="[
                                                'flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-all',
                                                form.tipoPago === 'credito' 
                                                    ? 'border-green-500 bg-green-50 text-green-700' 
                                                    : 'border-gray-300 hover:border-gray-400'
                                            ]"
                                        >
                                            <input 
                                                type="radio" 
                                                v-model="form.tipoPago" 
                                                value="credito" 
                                                class="sr-only"
                                            />
                                            <div class="text-center">
                                                <div class="font-semibold">📅 Crédito</div>
                                                <div class="text-xs text-gray-500">2 cuotas (50%/50%)</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Info de Crédito -->
                                <div v-if="form.tipoPago === 'credito'" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <h4 class="font-medium text-green-800 mb-2">📋 Plan de Pagos</h4>
                                    <div class="text-sm text-green-700 space-y-1">
                                        <p><strong>Cuota 1:</strong> ${{ formatearPrecio(total / 2) }} - Pago inmediato</p>
                                        <p><strong>Cuota 2:</strong> ${{ formatearPrecio(total - (total / 2)) }} - Vence en {{ form.diasSegundaCuota }} días</p>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label class="block text-xs font-medium text-green-700 mb-1">
                                            Días para la segunda cuota
                                        </label>
                                        <select 
                                            v-model="form.diasSegundaCuota" 
                                            class="w-full px-2 py-1 text-sm border border-green-300 rounded focus:outline-none focus:ring-1 focus:ring-green-500"
                                        >
                                            <option :value="15">15 días</option>
                                            <option :value="30">30 días</option>
                                            <option :value="45">45 días</option>
                                            <option :value="60">60 días</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de Pago -->
                            <button 
                                type="submit" 
                                :disabled="procesando"
                                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
                            >
                                <span v-if="!procesando">Procesar Pago</span>
                                <span v-else class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando...
                                </span>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Resumen del Pedido -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-6">Resumen del Pedido</h2>
                        
                        <!-- Productos -->
                        <div class="space-y-4 mb-6">
                            <div 
                                v-for="item in carritoItems" 
                                :key="item.id" 
                                class="flex items-center gap-3 py-3 border-b border-gray-200 last:border-b-0"
                            >
                                <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                    <img 
                                        :src="item.producto.imagen_url" 
                                        :alt="item.producto.nombre"
                                        class="w-full h-full object-cover"
                                        @error="handleImageError"
                                    />
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ item.producto.nombre }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ item.cantidad }} x ${{ formatearPrecio(item.precio_unitario) }}
                                    </p>
                                </div>
                                
                                <div class="text-sm font-medium text-gray-900">
                                    ${{ formatearPrecio(item.cantidad * item.precio_unitario) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Totales -->
                        <div class="space-y-2 border-t border-gray-200 pt-4">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal</span>
                                <span>${{ formatearPrecio(subtotal) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Descuentos</span>
                                <span>-${{ formatearPrecio(descuentos) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                                <span>Total</span>
                                <span>${{ formatearPrecio(total) }}</span>
                            </div>

                            <!-- Resumen si es crédito -->
                            <div v-if="form.tipoPago === 'credito'" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm font-medium text-yellow-800">
                                    💳 Pagarás ahora: ${{ formatearPrecio(total / 2) }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Volver al Carrito -->
                        <div class="mt-6">
                            <button 
                                @click="volverAlCarrito"
                                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors"
                            >
                                Volver al Carrito
                            </button>
                        </div>
                    </div>
                </div>

                <!-- QR y Respuesta -->
                <div v-if="response || qrImage" class="mt-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4">Resultado del Pago</h2>
                        
                        <!-- Mensaje de Error -->
                        <div v-if="response && response.error" class="mb-6 bg-red-50 border border-red-200 rounded p-4">
                            <h3 class="font-medium text-red-800 mb-2">❌ Error en el Pago</h3>
                            
                            <!-- Información de Debug -->
                            <div v-if="response.debug_info" class="mt-4 p-3 bg-red-100 rounded text-sm">
                                <h4 class="font-medium text-red-800 mb-2">Información de Debug:</h4>
                                <div v-if="response.debug_info.urls_tried" class="mb-2">
                                    <strong>URLs intentadas:</strong>
                                    <ul class="list-disc ml-4 text-red-700">
                                        <li v-for="url in response.debug_info.urls_tried" :key="url">{{ url }}</li>
                                    </ul>
                                </div>
                                <div v-if="response.debug_info.commerce_id" class="mb-2">
                                    <strong>Commerce ID:</strong> <code class="bg-red-200 px-1 rounded">{{ response.debug_info.commerce_id }}</code>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-sm text-red-600">
                                <strong>💡 Posibles soluciones:</strong>
                                <ul class="list-disc ml-4 mt-1">
                                    <li>Verifica que tus credenciales de PagoFacil sean correctas</li>
                                    <li>Contacta con PagoFacil para obtener las URLs actuales de su API</li>
                                    <li>Revisa que el servicio de PagoFacil esté activo</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Pago Completado -->
                        <div v-if="pagoCompletado" class="mb-6 bg-green-50 border-2 border-green-400 rounded-lg p-6 text-center animate-fade-in">
                            <div class="text-6xl mb-4">🎉</div>
                            <h3 class="text-2xl font-bold text-green-800 mb-2">
                                {{ response?.tipoPago === 'credito' ? '¡Primera Cuota Pagada!' : '¡Pago Completado!' }}
                            </h3>
                            <p class="text-green-700 mb-4">
                                {{ response?.tipoPago === 'credito' 
                                    ? 'Tu primera cuota ha sido procesada. Recuerda pagar la segunda cuota antes del vencimiento.' 
                                    : 'Tu pago ha sido procesado exitosamente.' 
                                }}
                            </p>
                            
                            <!-- Info de siguiente cuota si es crédito -->
                            <div v-if="response?.tipoPago === 'credito' && response?.cuotaInfo" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-left">
                                <h4 class="font-medium text-yellow-800 mb-2">📅 Próxima Cuota</h4>
                                <p class="text-sm text-yellow-700">
                                    <strong>Monto:</strong> ${{ formatearPrecio(response.cuotaInfo.cuotas[1]?.monto || (total / 2)) }}<br>
                                    <strong>Vencimiento:</strong> {{ response.cuotaInfo.cuotas[1]?.fecha_vencimiento }}
                                </p>
                                <p class="text-xs text-yellow-600 mt-2">
                                    Podrás pagar tu segunda cuota desde tu historial de compras.
                                </p>
                            </div>
                            
                            <button 
                                @click="volverAlCarrito" 
                                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition"
                            >
                                Continuar
                            </button>
                        </div>
                        
                        <!-- QR Code Exitoso -->
                        <div v-if="qrImage && !pagoCompletado" class="text-center mb-6">
                            <h3 class="text-lg font-medium mb-4 text-green-700">
                                ✅ ¡Pago Generado! Escanea el código QR
                            </h3>
                            
                            <!-- Indicador de cuota si es crédito -->
                            <div v-if="form.tipoPago === 'credito'" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm font-semibold text-blue-800">
                                    📋 Cuota 1 de 2 - ${{ formatearPrecio(response?.amount || (total / 2)) }}
                                </p>
                                <p class="text-xs text-blue-600 mt-1">
                                    Total de la compra: ${{ formatearPrecio(total) }}
                                </p>
                            </div>
                            
                            <div class="bg-white p-4 border-2 border-green-300 rounded-lg inline-block">
                                <img :src="qrImage" alt="Código QR" class="mx-auto max-w-xs" />
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                Escanea este código QR con tu aplicación de pagos
                            </p>
                            
                            <!-- Indicador de verificación -->
                            <div v-if="verificandoPago" class="mt-4 flex items-center justify-center text-blue-600">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Esperando confirmación de pago...</span>
                            </div>
                            
                            <!-- Información de expiración -->
                            <div v-if="response?.expirationDate" class="mt-3 text-sm text-orange-600">
                                ⏱️ Expira: {{ response.expirationDate }}
                            </div>
                        </div>
                        
                        <!-- Respuesta Raw (solo para debug) -->
                        <div v-if="response && !response.error" class="bg-gray-50 rounded p-4">
                            <h3 class="font-medium mb-2">Respuesta del servicio:</h3>
                            <pre class="text-sm text-gray-600 overflow-x-auto">{{ JSON.stringify(response, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive, ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    carritoItems: {
        type: Array,
        default: () => []
    },
    subtotal: {
        type: [Number, String],
        default: 0
    },
    descuentos: {
        type: [Number, String], 
        default: 0
    },
    total: {
        type: [Number, String],
        default: 0
    },
    usuario: {
        type: Object,
        default: null
    }
});

const procesando = ref(false);
const response = ref(null);
const qrImage = ref(null);
const transactionId = ref(null);
const paymentNumber = ref(null);
const paymentStatus = ref(null); // 1=pendiente, 2=pagado, 3=expirado
const pollingInterval = ref(null);
const verificandoPago = ref(false);
const pagoCompletado = ref(false);

const form = reactive({
    tnTelefono: '',
    tcRazonSocial: '',
    tcCiNit: '',
    tcCorreo: '',
    tnMonto: 0,
    tcSerial: '',
    tnDescuento: 0,
    tnTotal: 0,
    tnTipoServicio: 1,
    taPedidoDetalle: '',
    tipoPago: 'contado', // 'contado' o 'credito'
    diasSegundaCuota: 30
});

// Inicializar formulario con datos del usuario y carrito
onMounted(() => {
    if (props.usuario) {
        form.tcRazonSocial = props.usuario.name || '';
        form.tcCorreo = props.usuario.email || '';
        // Si hay más campos en el perfil del usuario, agregarlos aquí
    }
    
    form.tnMonto = parseFloat(props.total);
    form.tnDescuento = parseFloat(props.descuentos); 
    form.tnTotal = parseFloat(props.total);
    form.tcSerial = `TECNO_${Date.now()}`; // Serial único
    
    // Detalle del pedido con productos del carrito
    const detalleProductos = props.carritoItems.map(item => 
        `${item.cantidad}x ${item.producto.nombre} - $${formatearPrecio(item.precio_unitario)}`
    ).join(', ');
    
    form.taPedidoDetalle = `Compra de productos: ${detalleProductos}`;
});

// Limpiar el polling cuando el componente se desmonte
onUnmounted(() => {
    detenerPolling();
});

// Función para iniciar el polling de verificación de pago
const iniciarPolling = (txId, pNumber) => {
    transactionId.value = txId;
    paymentNumber.value = pNumber;
    verificandoPago.value = true;
    
    // Verificar cada 5 segundos
    pollingInterval.value = setInterval(async () => {
        await verificarEstadoPago();
    }, 5000);
    
    // También verificar inmediatamente
    verificarEstadoPago();
};

// Función para detener el polling
const detenerPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
    verificandoPago.value = false;
};

// Función para verificar el estado del pago
const verificarEstadoPago = async () => {
    if (!transactionId.value && !paymentNumber.value) return;
    
    try {
        // Primero verificar en nuestra BD por paymentNumber
        const { data } = await axios.post('/consultar', {
            transactionId: transactionId.value,
            paymentNumber: paymentNumber.value
        });
        
        if (data.success && data.data) {
            paymentStatus.value = data.data.status;
            
            // Estado 2 = Pagado
            if (data.data.status === 2 || data.data.status === '2') {
                pagoCompletado.value = true;
                detenerPolling();
                
                // Mostrar mensaje de éxito
                response.value = {
                    ...response.value,
                    pagoConfirmado: true,
                    mensaje: '¡Pago completado exitosamente!'
                };
            }
            // Estado 3 = Expirado
            else if (data.data.status === 3 || data.data.status === '3') {
                detenerPolling();
                response.value = {
                    ...response.value,
                    error: true,
                    message: 'El código QR ha expirado. Por favor genera uno nuevo.'
                };
            }
        }
    } catch (error) {
        console.error('Error verificando estado del pago:', error);
    }
};

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

const volverAlCarrito = () => {
    router.visit(route('carrito.index'));
};

const enviarDatos = async () => {
    procesando.value = true;
    response.value = null;
    qrImage.value = null;
    
    try {
        // Validar datos antes de enviar
        if (!form.tnTelefono || !form.tcRazonSocial || !form.tcCiNit || !form.tcCorreo) {
            throw new Error('Todos los campos marcados con * son obligatorios');
        }
        
        // Limpiar teléfono (solo números)
        form.tnTelefono = form.tnTelefono.replace(/[^0-9]/g, '');
        
        // Limpiar CI/NIT (solo números)
        form.tcCiNit = form.tcCiNit.replace(/[^0-9]/g, '');
        
        const { data } = await axios.post("/recolectar-datos", form);
        
        if (data.success) {
            response.value = data.data;
            
            // Manejo de QR - PagoFacil v2 devuelve qrBase64
            const qrData = data.data.qrBase64 || 
                           data.data.qrImage || 
                           data.data.values?.qrBase64 || 
                           data.data.values?.qrImage;
            
            if (qrData) {
                // Si ya tiene el prefijo data:image, usarlo directamente
                if (qrData.startsWith('data:image')) {
                    qrImage.value = qrData;
                } else {
                    qrImage.value = `data:image/png;base64,${qrData}`;
                }
            }
            
            // Iniciar polling para verificar estado del pago
            const txId = data.data.transactionId || data.data.values?.transactionId;
            const pNumber = data.data.paymentNumber || data.data.values?.paymentNumber;
            if (txId || pNumber) {
                iniciarPolling(txId, pNumber);
            }
        } else {
            // Mostrar información de debug si está disponible
            console.error('Error de PagoFacil:', data);
            if (data.debug_info) {
                console.error('Debug Info:', data.debug_info);
                console.error('URLs intentadas:', data.debug_info.urls_tried);
                console.error('Commerce ID:', data.debug_info.commerce_id);
                if (data.debug_info.request_data) {
                    console.error('Datos enviados:', data.debug_info.request_data);
                }
            }
            throw new Error(data.message || 'Error al procesar el pago');
        }
    } catch (error) {
        console.error('Error al procesar pago:', error);
        
        let errorMessage = 'Error al procesar el pago';
        let debugInfo = null;
        
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
            debugInfo = error.response.data.debug_info;
            
            // Mostrar información de debug en consola
            if (debugInfo) {
                console.error('URLs intentadas:', debugInfo.urls_tried);
                console.error('Commerce ID utilizado:', debugInfo.commerce_id);
                if (debugInfo.request_data) {
                    console.error('Datos de la solicitud:', debugInfo.request_data);
                }
            }
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        response.value = {
            error: true,
            message: errorMessage,
            details: error.response?.data,
            debug_info: debugInfo
        };
    } finally {
        procesando.value = false;
    }
};
</script>

<style scoped>
.container {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

/* Animación para el QR */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
</style>
