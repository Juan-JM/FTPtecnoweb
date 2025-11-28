<template>
    <AppLayout>
        <AdminLayout>
            <div>
                <h1 class="mb-4 text-2xl font-bold">Ventas</h1>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded">
                    Crear Nueva Venta
                </button>

                <!-- Tabla para mostrar las ventas -->
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Fecha de Venta</th>
                            <th class="px-4 py-2 text-left">Total</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                            <th class="px-4 py-2 text-left">Usuario</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="venta in ventas" :key="venta.id">
                            <td class="px-4 py-2">{{ venta.fecha_venta }}</td>
                            <td class="px-4 py-2">{{ venta.total }}</td>
                            <td class="px-4 py-2">{{ venta.estado }}</td>
                            <td class="px-4 py-2">{{ venta.usuario_id }}</td>
                            <td class="px-4 py-2">
                                <button @click="showEditModal(venta)"
                                    class="px-4 py-2 text-white bg-yellow-500 rounded">
                                    Editar
                                </button>
                                <button @click="deleteVenta(venta.id)" class="px-4 py-2 text-white bg-red-500 rounded">
                                    Eliminar
                                </button>
                                <button @click="goToDetalleVenta(venta.id)"
                                    class="px-4 py-2 text-white bg-green-500 rounded">
                                    Detalle
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Modal para crear o editar una venta -->
                <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-1/3 p-6 bg-white rounded-md">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Venta' : 'Crear Venta' }}</h2>

                        <form @submit.prevent="isEditing ? updateVenta() : createVenta()">
                            <div class="mb-4">
                                <label for="fecha_venta" class="block">Fecha de Venta</label>
                                <input v-model="form.fecha_venta" type="date" id="fecha_venta"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.fecha_venta" class="text-sm text-red-500">{{ errors.fecha_venta
                                    }}</span>
                            </div>
                            <div class="mb-4">
                                <label for="total" class="block">Total</label>
                                <input v-model="form.total" type="number" step="0.01" id="total"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.total" class="text-sm text-red-500">{{ errors.total }}</span>
                            </div>
                            <div class="mb-4">
                                <label for="estado" class="block">Estado</label>
                                <select v-model="form.estado" id="estado"
                                    class="w-full p-2 border border-gray-300 rounded">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                                <span v-if="errors.estado" class="text-sm text-red-500">{{ errors.estado }}</span>
                            </div>
                            <div class="mb-4">
                                <label for="usuario_id" class="block">Usuario</label>
                                <select v-model="form.usuario_id" id="usuario_id"
                                    class="w-full p-2 border border-gray-300 rounded">
                                    <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">{{
                                        usuario.nombre }}</option>
                                </select>
                                <span v-if="errors.usuario_id" class="text-sm text-red-500">{{ errors.usuario_id
                                    }}</span>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="closeModal"
                                    class="px-4 py-2 mr-2 text-white bg-gray-500 rounded">Cancelar</button>
                                <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded">
                                    {{ isEditing ? 'Actualizar' : 'Crear' }}
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
import { ref, render } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    ventas: Array,
    usuarios: Array  // Lista de usuarios que se pueden asignar a una venta
});

const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
    id: null,
    fecha_venta: '',
    total: '',
    estado: 'pendiente',
    usuario_id: null
});
const errors = ref({});

const showCreateModal = () => {
    form.value = { id: null, fecha_venta: '', total: '', estado: 'pendiente', usuario_id: null };
    errors.value = {};
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (venta) => {
    form.value = { ...venta };         // Llena el formulario con los datos de la venta
    errors.value = {};                 // Resetea los errores
    showModal.value = true;            // Muestra el modal
    isEditing.value = true;            // Establece que estamos en modo de edición
};

const closeModal = () => {
    showModal.value = false;
};

const createVenta = () => {
    errors.value = {};  // Limpiar los errores
    Inertia.post(route('admin.ventas.store'), form.value, {
        onSuccess: () => {
            closeModal();  // Cerrar el modal al crear
            console.log('Venta creada correctamente');
        },
        onError: (error) => {
            errors.value = error;  // Mostrar los errores
        }
    });
};

const updateVenta = () => {
    errors.value = {};  // Limpiar los errores
    Inertia.put(route('admin.ventas.update', form.value.id), form.value, {
        onSuccess: () => {
            console.log('Venta actualizada correctamente');
            closeModal();  // Cerrar el modal al actualizar
        },
        onError: (error) => {
            errors.value = error;  // Mostrar los errores
        }
    });
};

const deleteVenta = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta venta?')) {
        Inertia.delete(route('admin.ventas.destroy', id), {
            onSuccess: () => {
                console.log('Venta eliminada correctamente');
            }
        });
    }
};

const goToDetalleVenta = (id) => {
    Inertia.get(route('admin.detalleventas', id));
};
</script>

<style scoped>
/* Puedes agregar tus estilos personalizados aquí */
</style>
