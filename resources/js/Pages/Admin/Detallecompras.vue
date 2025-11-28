<template>
    <AppLayout>
        <AdminLayout>
            <div>
                <h1 class="mb-4 text-2xl font-bold">Detalle de Compra</h1>
                <div class="mb-4">
                    <p><strong>Compra:</strong> {{ compras.id }}</p>
                    <p><strong>Fecha de Compra:</strong> {{ compras.fecha_venta }}</p>
                    <p><strong>Total:</strong> {{ compras.total }}</p>
                    <p><strong>Usuario:</strong> {{ compras.usuario_id || 'Usuario no disponible' }}</p>
                </div>

                <div class="mb-4">
                    <p><strong>Total Calculado:</strong> {{ totalCompra }}</p>
                </div>
                <button @click="guardarTotal" class="px-4 py-2 mt-4 text-white bg-green-500 rounded">
                    Guardar Total
                </button>


                <h2 class="mb-2 text-xl font-semibold">Productos en la Compra</h2>
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Producto</th>
                            <th class="px-4 py-2 text-left">Cantidad</th>
                            <th class="px-4 py-2 text-left">Precio Unitario</th>
                            <th class="px-4 py-2 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="detalleCompra in detallesCompra" :key="detalleCompra.id">
                            <td class="px-4 py-2">{{ detalleCompra.producto_id }}</td>
                            <td class="px-4 py-2">{{ detalleCompra.cantidad }}</td>
                            <td class="px-4 py-2">{{ detalleCompra.precio_unitario }}</td>
                            <td class="px-4 py-2">{{ detalleCompra.cantidad * detalleCompra.precio_unitario }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="mt-4 mb-2 text-lg font-semibold">Agregar Nuevo Producto</h3>
                <form @submit.prevent="agregarDetalleCompra" class="space-y-4">
                    <div>
                        <label for="producto_id" class="block text-sm font-medium">Producto</label>
                        <select v-model="nuevoDetalle.producto_id" id="producto_id"
                            class="block w-full mt-1 border-gray-300 rounded">
                            <option v-for="producto in productosDisponibles" :key="producto.id" :value="producto.id">
                                {{ producto.nombre }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="cantidad" class="block text-sm font-medium">Cantidad</label>
                        <input v-model="nuevoDetalle.cantidad" type="number" id="cantidad"
                            class="block w-full mt-1 border-gray-300 rounded" min="1" required>
                    </div>

                    <div>
                        <label for="precio_unitario" class="block text-sm font-medium">Precio Unitario</label>
                        <input v-model="nuevoDetalle.precio_unitario" type="number" id="precio_unitario"
                            class="block w-full mt-1 border-gray-300 rounded" min="0" required>
                    </div>

                    <div>
                        <label for="venta_id" class="block text-sm font-medium">ID de Compra</label>
                        <input type="text" id="venta_id" class="block w-full mt-1 border-gray-300 rounded"
                            :value="compras.id" readonly>
                    </div>


                    <button @click="agregarDetalleCompra" class="px-4 py-2 mt-4 text-white bg-blue-500 rounded">Agregar
                        Detalle</button>
                </form>

                <button @click="volver" class="px-4 py-2 mt-4 text-white bg-blue-500 rounded">
                    Volver a Compras
                </button>
            </div>
        </AdminLayout>
    </AppLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { defineProps, ref, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    compras: Object,
    detallesCompra: Array,
    productosDisponibles: Array,
});

const nuevoDetalle = ref({
    producto_id: '',
    cantidad: 1,
    precio_unitario: 0,
    compra_id: props.compras.id,
});

const agregarDetalleCompra = async () => {
    if (!nuevoDetalle.value.producto_id || !nuevoDetalle.value.cantidad || !nuevoDetalle.value.precio_unitario) {
        alert('Por favor complete todos los campos.');
        return;
    }

    try {
        const response = await Inertia.post(route('admin.detallecompras', props.compras.id), {
            producto_id: nuevoDetalle.value.producto_id,
            cantidad: nuevoDetalle.value.cantidad,
            precio_unitario: nuevoDetalle.value.precio_unitario,
            compra_id: nuevoDetalle.value.compra_id,
        });

        if (response && response.nuevoDetalle) {
            detallesCompra.value.push(response.nuevoDetalle);
        } else {
            console.error('Error: Detalle de venta no encontrado en la respuesta');
        }
    } catch (error) {
        console.error('Error al agregar el detalle de venta:', error);
        alert('Hubo un error al agregar el detalle de venta.');
    }
};

const totalCompra = computed(() => {
    return props.detallesCompra.reduce((suma, detalle) => {
        return suma + (detalle.cantidad * detalle.precio_unitario);
    }, 0);
});

const guardarTotal = async () => {
    try {
        await Inertia.put(route('admin.compras.update', props.compras.id), {
            total: totalCompra.value, // Enviar el total calculado al backend
        });

        alert('Total actualizado con éxito.');
    } catch (error) {
        console.error('Error al guardar el total:', error);
        alert('Hubo un error al guardar el total.');
    }
};

const volver = () => {
    Inertia.get(route('admin.compras'));
};


</script>

<style scoped></style>
