<template>
    <AppLayout>
        <AdminLayout>
            <div>
                <h1 class="mb-4 text-2xl font-bold">Gestión de Pagos</h1>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded">
                    Crear Nuevo Pago
                </button>

                <!-- Tabla para mostrar los pagos -->
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Fecha de Pago</th>
                            <th class="px-4 py-2 text-left">Monto</th>
                            <th class="px-4 py-2 text-left">Método de Pago</th>
                            <th class="px-4 py-2 text-left">Venta Asociada</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="pago in pagos" :key="pago.id">
                            <td class="px-4 py-2">{{ pago.fecha_pago }}</td>
                            <td class="px-4 py-2">{{ pago.monto }}</td>
                            <td class="px-4 py-2">{{ pago.metodo_pago }}</td>
                            <td class="px-4 py-2">{{ pago.venta_id }}</td>
                            <td class="px-4 py-2">
                                <button @click="showEditModal(pago)" class="px-4 py-2 text-white bg-yellow-500 rounded">
                                    Editar
                                </button>
                                <button @click="deletePago(pago.id)" class="px-4 py-2 text-white bg-red-500 rounded">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Modal para crear o editar un pago -->
                <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-1/3 p-6 bg-white rounded-md">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Pago' : 'Crear Pago' }}</h2>

                        <form @submit.prevent="isEditing ? updatePago() : createPago()">
                            <div class="mb-4">
                                <label for="fecha_pago" class="block">Fecha de Pago</label>
                                <input v-model="form.fecha_pago" type="date" id="fecha_pago"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.fecha_pago" class="text-sm text-red-500">{{ errors.fecha_pago }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="monto" class="block">Monto</label>
                                <input v-model="form.monto" type="number" id="monto"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.monto" class="text-sm text-red-500">{{ errors.monto }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="metodo_pago" class="block">Método de Pago</label>
                                <input v-model="form.metodo_pago" type="text" id="metodo_pago"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.metodo_pago" class="text-sm text-red-500">{{ errors.metodo_pago }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="venta_id" class="block">Venta Asociada</label>
                                <select v-model="form.venta_id" id="venta_id" class="w-full p-2 border border-gray-300 rounded">
                                    <option v-for="venta in ventas" :key="venta.id" :value="venta.id">
                                        {{ venta.id }} - {{ venta.total }} - {{ venta.estado }}
                                    </option>
                                </select>
                                <span v-if="errors.venta_id" class="text-sm text-red-500">{{ errors.venta_id }}</span>
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
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    pagos: Array,
    ventas: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
    id: null,
    fecha_pago: '',
    monto: '',
    metodo_pago: '',
    venta_id: null,
});
const errors = ref({});

const showCreateModal = () => {
    form.value = { id: null, fecha_pago: '', monto: '', metodo_pago: '', venta_id: null };
    errors.value = {};
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (pago) => {
    form.value = { ...pago };  // Llena el formulario con los datos del pago
    errors.value = {};         // Resetea los errores
    showModal.value = true;    // Muestra el modal
    isEditing.value = true;    // Establece que estamos en modo de edición
};

const closeModal = () => {
    showModal.value = false;
};

const createPago = () => {
    errors.value = {};  // Limpiar los errores
    Inertia.post(route('admin.pagos.store'), form.value, {
        onSuccess: () => {
            closeModal();  // Cerrar el modal al crear
        },
        onError: (error) => {
            errors.value = error;  // Mostrar los errores
        }
    });
};

const updatePago = () => {
    errors.value = {};  // Limpiar los errores
    Inertia.put(route('admin.pagos.update', form.value.id), form.value, {
        onSuccess: () => {
            closeModal();  // Cerrar el modal al actualizar
        },
        onError: (error) => {
            errors.value = error;  // Mostrar los errores
        }
    });
};

const deletePago = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este pago?')) {
        Inertia.delete(route('admin.pagos.destroy', id));
    }
};
</script>

<style scoped>
/* Puedes agregar tus estilos personalizados aquí */
</style>
