<template>
    <AppLayout>
        <AdminLayout>
            <div>
                <h1 class="mb-4 text-2xl font-bold">Categorías</h1>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded">
                    Crear Nueva Categoría
                </button>

                <!-- Tabla para mostrar las categorías -->
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="categoria in categorias" :key="categoria.id">
                            <td class="px-4 py-2">{{ categoria.nombre }}</td>
                            <td class="px-4 py-2">
                                <button @click="showEditModal(categoria)"
                                    class="px-4 py-2 text-white bg-yellow-500 rounded">
                                    Editar
                                </button>
                                <button @click="deleteCategoria(categoria.id)"
                                    class="px-4 py-2 text-white bg-red-500 rounded">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Modal para crear o editar una categoría -->
                <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-1/3 p-6 bg-white rounded-md">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Categoría' : 'Crear Categoría' }}</h2>

                        <form @submit.prevent="isEditing ? updateCategoria() : createCategoria()">
                            <div class="mb-4">
                                <label for="nombre" class="block">Nombre</label>
                                <input v-model="form.nombre" type="text" id="nombre"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.nombre" class="text-sm text-red-500">{{ errors.nombre }}</span>
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
    categorias: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
    id: null,
    nombre: ''
});
const errors = ref({});

const showCreateModal = () => {
    form.value = { id: null, nombre: '' };
    errors.value = {};
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (categoria) => {
    form.value = { ...categoria };         // Llena el formulario con los datos de la categoría
    errors.value = {};                     // Resetea los errores
    showModal.value = true;                // Muestra el modal
    isEditing.value = true;                // Establece que estamos en modo de edición
};

const closeModal = () => {
    showModal.value = false;
};

const createCategoria = () => {
    errors.value = {};  // Limpiar los errores
    Inertia.post(route('admin.categorias.store'), form.value, {
        onSuccess: () => {
            closeModal();  // Cerrar el modal al crear
        },
        onError: (error) => {
            errors.value = error;  // Mostrar los errores
        }
    });
};

const updateCategoria = () => {
    errors.value = {};  // Limpiar los errores
    Inertia.put(route('admin.categorias.update', form.value.id), form.value, {
        onSuccess: () => {
            closeModal();  // Cerrar el modal al actualizar
        },
        onError: (error) => {
            errors.value = error;  // Mostrar los errores
        }
    });
};

const deleteCategoria = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
        Inertia.delete(route('admin.categorias.destroy', id));
    }
};

</script>

<style scoped>
/* Puedes agregar tus estilos personalizados aquí */
</style>
