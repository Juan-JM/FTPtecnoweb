<template>
    <AppLayout>
        <AdminLayout>
            <div>
                <h1 class="mb-4 text-2xl font-bold">Promociones</h1>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded">
                    Crear Nueva Promoción
                </button>

                <!-- Tabla para mostrar las promociones -->
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Título</th>
                            <th class="px-4 py-2 text-left">Descuento</th>
                            <th class="px-4 py-2 text-left">Fecha de Inicio</th>
                            <th class="px-4 py-2 text-left">Fecha de Fin</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="promocion in promociones" :key="promocion.id">
                            <td class="px-4 py-2">{{ promocion.nombre }}</td>
                            <td class="px-4 py-2">{{ promocion.descuento_porcentaje }}%</td>
                            <td class="px-4 py-2">{{ promocion.fecha_inicio }}</td>
                            <td class="px-4 py-2">{{ promocion.fecha_fin }}</td>
                            <td class="px-4 py-2">
                                <button @click="showEditModal(promocion)"
                                    class="px-4 py-2 text-white bg-yellow-500 rounded">
                                    Editar
                                </button>
                                <button @click="deletePromocion(promocion.id)"
                                    class="px-4 py-2 text-white bg-red-500 rounded">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Modal para crear o editar una promoción -->
                <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-1/3 p-6 bg-white rounded-md">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Promoción' : 'Crear Promoción' }}</h2>

                        <form @submit.prevent="isEditing ? updatePromocion() : createPromocion()">
                            <div class="mb-4">
                                <label for="titulo" class="block">Título</label>
                                <input v-model="form.nombre" type="text" id="titulo"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.nombre" class="text-sm text-red-500">{{ errors.nombre }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="descripcion" class="block">Descripción</label>
                                <textarea v-model="form.descripcion" id="descripcion"
                                    class="w-full p-2 border border-gray-300 rounded"></textarea>
                                <span v-if="errors.descripcion" class="text-sm text-red-500">{{ errors.descripcion }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="descuento" class="block">Descuento (%)</label>
                                <input v-model="form.descuento_porcentaje" type="number" id="descuento"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.descuento_porcentaje" class="text-sm text-red-500">{{ errors.descuento_porcentaje }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="fecha_inicio" class="block">Fecha de Inicio</label>
                                <input v-model="form.fecha_inicio" type="date" id="fecha_inicio"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.fecha_inicio" class="text-sm text-red-500">{{ errors.fecha_inicio }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="fecha_fin" class="block">Fecha de Fin</label>
                                <input v-model="form.fecha_fin" type="date" id="fecha_fin"
                                    class="w-full p-2 border border-gray-300 rounded" />
                                <span v-if="errors.fecha_fin" class="text-sm text-red-500">{{ errors.fecha_fin }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="producto_id" class="block">Producto</label>
                                <select v-model="form.producto_id" id="producto_id"
                                    class="w-full p-2 border border-gray-300 rounded">
                                    <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                        {{ producto.nombre }}
                                    </option>
                                </select>
                                <span v-if="errors.producto_id" class="text-sm text-red-500">{{ errors.producto_id }}</span>
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
    promociones: Array,
    productos: Array // Lista de productos para el selector
});

const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
    id: null,
    titulo: '',
    descripcion: '',
    descuento_porcentaje: null,
    fecha_inicio: '',
    fecha_fin: '',
    producto_id: null
});
const errors = ref({});

const showCreateModal = () => {
    form.value = { id: null, titulo: '', descripcion: '', descuento_porcentaje: null, fecha_inicio: '', fecha_fin: '', producto_id: null };
    errors.value = {};
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (promocion) => {
    form.value = { ...promocion };
    errors.value = {};
    showModal.value = true;
    isEditing.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const createPromocion = () => {
    errors.value = {};
    Inertia.post(route('admin.promociones.store'), form.value, {
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            errors.value = error;
        }
    });
};

const updatePromocion = () => {
    errors.value = {};
    Inertia.put(route('admin.promociones.update', form.value.id), form.value, {
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            errors.value = error;
        }
    });
};

const deletePromocion = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta promoción?')) {
        Inertia.delete(route('admin.promociones.destroy', id));
    }
};
</script>

<style scoped>
/* Agrega estilos personalizados aquí si es necesario */
</style>
