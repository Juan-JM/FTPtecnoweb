<template>
    <AppLayout>
        <AdminLayout>
            <div>
                <h1 class="mb-4 text-2xl font-bold">Compras</h1>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded">
                    Crear Nueva Compra
                </button>

                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Fecha de Compra</th>
                            <th class="px-4 py-2 text-left">Total</th>
                            <th class="px-4 py-2 text-left">Proveedor</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="compra in compras" :key="compra.id">
                            <td class="px-4 py-2">{{ compra.fecha_compra }}</td>
                            <td class="px-4 py-2">{{ compra.total }}</td>
                            <td class="px-4 py-2">{{ compra.usuario_id }}</td>
                            <td class="px-4 py-2">
                                <button @click="showEditModal(compra)"
                                    class="px-4 py-2 text-white bg-yellow-500 rounded">
                                    Editar
                                </button>
                                <button @click="deleteCompra(compra.id)"
                                    class="px-4 py-2 text-white bg-red-500 rounded">
                                    Eliminar
                                </button>
                                <button @click="goToDetalleCompra(compra.id)"
                                    class="px-4 py-2 text-white bg-green-500 rounded">
                                    Detalle
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Modal para crear o editar una compra -->
                <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-1/3 p-6 bg-white rounded-md">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Compra' : 'Crear Compra' }}</h2>

                        <form @submit.prevent="isEditing ? updateCompra() : createCompra()">
                            <div class="mb-4">
                                <label for="fecha_compra" class="block">Fecha de Compra</label>
                                <input v-model="form.fecha_compra" type="date" id="fecha_compra"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.fecha_compra" class="text-sm text-red-500">{{ errors.fecha_compra }}</span>
                            </div>
                            <div class="mb-4">
                                <label for="total" class="block">Total</label>
                                <input v-model="form.total" type="number" step="0.01" id="total"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.total" class="text-sm text-red-500">{{ errors.total }}</span>
                            </div>
                            <div class="mb-4">
                                <label for="usuario_id" class="block">Proveedor</label>
                                <select v-model="form.usuario_id" id="usuario_id" class="w-full p-2 border border-gray-300 rounded">
                                    <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">{{ usuario.nombre }}</option>
                                </select>
                                <span v-if="errors.usuario_id" class="text-sm text-red-500">{{ errors.usuario_id }}</span>
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
    compras: Array,
    usuarios: Array  // Lista de proveedores que se pueden asignar a una compra
});

const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
    id: null,
    fecha_compra: '',
    total: '',
    usuario_id: null
});
const errors = ref({});

const showCreateModal = () => {
    form.value = { id: null, fecha_compra: '', total: '', usuario_id: null };
    errors.value = {};
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (compra) => {
    form.value = { ...compra };
    errors.value = {};
    showModal.value = true;
    isEditing.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const createCompra = () => {
    errors.value = {};
    Inertia.post(route('admin.compras.store'), form.value, {
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            errors.value = error;
        }
    });
};

const updateCompra = () => {
    errors.value = {};
    Inertia.put(route('admin.compras.update', form.value.id), form.value, {
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            errors.value = error;
        }
    });
};

const deleteCompra = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta compra?')) {
        Inertia.delete(route('admin.compras.destroy', id));
    }
};

const goToDetalleCompra = (id) => {
    Inertia.get(route('admin.detallecompras', id));
};
</script>

<style scoped>
/* Agrega tus estilos personalizados aquí */
</style>
