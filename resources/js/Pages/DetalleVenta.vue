<template>
    <AppLayout>
        <div class="container p-6 mx-auto">
            <h1 class="mb-6 text-3xl font-semibold">Detalle de Venta</h1>

            <div class="p-6 mb-6 bg-white rounded-md shadow-md">
                <p><strong>ID de Venta:</strong> {{ venta.id }}</p>
                <p><strong>Total:</strong> ${{ venta.total }}</p>
                <p><strong>Estado:</strong> {{ venta.estado }}</p>
            </div>


            <h2 class="mb-4 text-2xl font-semibold">Productos en esta venta</h2>
            <ul v-if="productosEnVenta.length" class="space-y-4">
                <li v-for="producto in productosEnVenta" :key="producto.id"
                    class="p-4 bg-gray-100 rounded-lg shadow-md">
                    <p><strong>{{ producto.nombre }}</strong></p>
                    <p>Precio Unitario: ${{ producto.precio_unitario }}</p>
                    <p>Cantidad: {{ producto.cantidad }}</p> <!-- Mostrar la cantidad desde detalle_venta -->
                    <p>Subtotal: ${{ producto.precio_unitario * producto.cantidad }}</p> <!-- Mostrar el subtotal -->
                </li>
            </ul>
            <p v-else class="text-gray-500">No hay productos en esta venta.</p>


            <p v-else class="text-gray-500">No hay productos en esta venta.</p>

            <button @click="mostrarFormulario"
                class="px-6 py-3 mt-10 text-white transition-all bg-blue-500 rounded-lg shadow-md hover:bg-blue-600">
                Añadir Producto
            </button>
            <button @click="realizarPago"
                class="px-6 py-3 mt-6 text-white transition-all bg-blue-500 rounded-lg shadow-md hover:bg-blue-600">
                Realizar Pago
            </button>

            <div v-if="formVisible" class="p-6 mt-6 bg-white rounded-md shadow-md">
                <h3 class="mb-4 text-xl font-semibold">Agregar Producto a la Venta</h3>

                <form @submit.prevent="agregarProductoVenta">
                    <div class="mb-4">
                        <label for="producto" class="block text-sm font-medium">Selecciona un producto</label>
                        <select v-model="productoSeleccionado" id="producto"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option v-for="producto in productosDisponibles" :key="producto.id" :value="producto.id">
                                {{ producto.nombre }} - ${{ producto.precio }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="cantidad" class="block text-sm font-medium">Cantidad</label>
                        <input v-model="cantidad" id="cantidad" type="number" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg" required />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-3 text-white bg-green-500 rounded-lg hover:bg-green-600">Agregar
                            Producto</button>
                    </div>
                </form>

                <button @click="cerrarFormulario"
                    class="px-6 py-3 mt-4 text-white bg-red-500 rounded-lg hover:bg-red-600">Cancelar</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { defineProps, computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    venta: Object,
    productos: Array,
    detallesVenta: Array
});

const formVisible = ref(false);
const productoSeleccionado = ref(null);
const cantidad = ref(1);

const realizarPago = async () => {
    try {
        const response = Inertia.post(route('pagos.crear'), {
            fecha_pago: new Date(),
            monto: props.venta.total,
            metodo_pago: 'Pago Qr',
            venta_id: props.venta.id,
        });

        Inertia.get(route('pagos.index'));
        if (response.success) {
            Inertia.get(route('pagos.index'));
        } else {
            console.error('Error en la respuesta del servidor:', response.message);
        }
    } catch (error) {
        console.error('Error al guardar el pago:', error);
    }
};


const productosDisponibles = computed(() => {
    return props.productos.filter(producto => !productosEnVenta.value.some(p => p.id === producto.id));
});

const productosEnVenta = computed(() => {
    return props.detallesVenta.map(detalle => {
        const producto = props.productos.find(producto => producto.id === detalle.producto_id);
        if (!producto) return null; // Filtra automáticamente si no hay producto

        // Retorna un objeto que combina datos del detalle y del producto
        return {
            id: detalle.id,
            producto_id: producto.id,
            nombre: producto.nombre,
            precio_unitario: producto.precio,
            cantidad: detalle.cantidad,
            subtotal: detalle.precio_unitario * detalle.cantidad // Calcula el subtotal
        };
    }).filter(item => item !== null); // Elimina cualquier entrada nula
});


const mostrarFormulario = () => {
    formVisible.value = true;
};

const cerrarFormulario = () => {
    formVisible.value = false;
};

const agregarProductoVenta = async () => {
    if (!productoSeleccionado.value) {
        alert('Por favor, selecciona un producto.');
        return;
    }

    try {
        const response = await axios.post(route('ventas.cliente.detalle.agregar', props.venta.id), {
            producto_id: productoSeleccionado.value,
            cantidad: cantidad.value
        });

        productosEnVenta.value.push(response.data.detalle);

        props.venta.total += response.data.detalle.precio_unitario * cantidad.value;

        cerrarFormulario();
    } catch (error) {
        alert('Error al agregar el producto.');
    }
};
</script>

<style scoped></style>
