<template>
    <AppLayout>
        <AdminLayout>
            <div class="p-6">
                <!-- Mensajes de éxito/error con verificación segura -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="p-4 mb-4 text-green-800 bg-green-100 border border-green-300 rounded">
                    {{ $page.props.flash.success }}
                </div>
                
                <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="p-4 mb-4 text-red-800 bg-red-100 border border-red-300 rounded">
                    <ul>
                        <li v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <h1 class="mb-4 text-2xl font-bold">Gestión de Productos</h1>

                <button @click="showCreateModal" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded hover:bg-blue-600">
                    Crear Nuevo Producto
                </button>

                <!-- Verificar si hay productos antes de mostrar la tabla -->
                <div v-if="productos && productos.length > 0" class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Imagen</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Nombre</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Precio</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Cantidad</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Categoría</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Descripción</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="producto in productos" :key="producto.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    <img 
                                        :src="producto.imagen_url || '/images/producto-placeholder.jpg'" 
                                        :alt="producto.nombre"
                                        class="w-16 h-16 object-cover rounded border"
                                        @error="handleImageError"
                                    />
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ producto.nombre || 'Sin nombre' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">${{ formatearPrecio(producto.precio) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <span :class="producto.cantidad > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ producto.cantidad || '0' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    {{ producto.categoria ? producto.categoria.nombre : 'Sin categoría' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <div class="max-w-xs truncate" :title="producto.descripcion">
                                        {{ producto.descripcion || 'Sin descripción' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <button @click="showEditModal(producto)"
                                        class="px-3 py-1 mr-2 text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                        Editar
                                    </button>
                                    <button @click="deleteProducto(producto.id)"
                                        class="px-3 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay productos -->
                <div v-else class="p-8 text-center bg-gray-50 rounded-lg">
                    <div class="text-6xl mb-4">📦</div>
                    <p class="text-gray-500 mb-2">No hay productos registrados.</p>
                    <button @click="showCreateModal" class="mt-4 px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                        Crear el primer producto
                    </button>
                </div>

                <!-- Modal para crear o editar un producto -->
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-2xl p-6 mx-4 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
                        <h2 class="mb-4 text-xl font-bold">{{ isEditing ? 'Editar Producto' : 'Crear Producto' }}</h2>

                        <form @submit.prevent="isEditing ? updateProducto() : createProducto()">
                            <!-- Vista previa de imagen actual (solo en edición) -->
                            <div v-if="isEditing && form.imagen_url" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagen Actual:</label>
                                <img 
                                    :src="form.imagen_url" 
                                    :alt="form.nombre"
                                    class="w-32 h-32 object-cover rounded border"
                                />
                            </div>

                            <!-- Campo de imagen -->
                            <div class="mb-4">
                                <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ isEditing ? 'Cambiar Imagen (opcional)' : 'Imagen del Producto' }}
                                </label>
                                <input 
                                    ref="imageInput"
                                    type="file" 
                                    id="imagen"
                                    accept="image/*"
                                    @change="handleImageChange"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB
                                </p>
                                <span v-if="errors.imagen" class="text-sm text-red-500">{{ errors.imagen }}</span>
                                
                                <!-- Vista previa de nueva imagen -->
                                <div v-if="imagePreview" class="mt-2">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Vista previa:</p>
                                    <img 
                                        :src="imagePreview" 
                                        alt="Vista previa"
                                        class="w-32 h-32 object-cover rounded border"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nombre" class="block mb-2 text-sm font-medium text-gray-700">Nombre</label>
                                    <input 
                                        v-model="form.nombre" 
                                        type="text" 
                                        id="nombre"
                                        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        required
                                    />
                                    <span v-if="errors.nombre" class="text-sm text-red-500">{{ errors.nombre }}</span>
                                </div>

                                <div>
                                    <label for="precio" class="block mb-2 text-sm font-medium text-gray-700">Precio</label>
                                    <input 
                                        v-model="form.precio" 
                                        type="number" 
                                        step="0.01"
                                        id="precio"
                                        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        required
                                    />
                                    <span v-if="errors.precio" class="text-sm text-red-500">{{ errors.precio }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="descripcion" class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                                <textarea 
                                    v-model="form.descripcion" 
                                    id="descripcion"
                                    rows="3"
                                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                ></textarea>
                                <span v-if="errors.descripcion" class="text-sm text-red-500">{{ errors.descripcion }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="cantidad" class="block mb-2 text-sm font-medium text-gray-700">Cantidad</label>
                                    <input 
                                        v-model="form.cantidad" 
                                        type="number" 
                                        id="cantidad" 
                                        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    />
                                    <span v-if="errors.cantidad" class="text-sm text-red-500">{{ errors.cantidad }}</span>
                                </div>

                                <div>
                                    <label for="categoria_id" class="block mb-2 text-sm font-medium text-gray-700">Categoría</label>
                                    <select 
                                        v-model="form.categoria_id" 
                                        id="categoria_id"
                                        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                        <option value="">Seleccionar categoría</option>
                                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                            {{ categoria.nombre }}
                                        </option>
                                    </select>
                                    <span v-if="errors.categoria_id" class="text-sm text-red-500">{{ errors.categoria_id }}</span>
                                </div>

                                <div>
                                    <label for="proveedor_id" class="block mb-2 text-sm font-medium text-gray-700">Proveedor</label>
                                    <select 
                                        v-model="form.proveedor_id" 
                                        id="proveedor_id"
                                        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                        <option value="">Seleccionar proveedor</option>
                                        <option v-for="usuario in usuariosProveedores" :key="usuario.id" :value="usuario.id">
                                            {{ usuario.nombre }} {{ usuario.apellido }}
                                        </option>
                                    </select>
                                    <span v-if="errors.proveedor_id" class="text-sm text-red-500">{{ errors.proveedor_id }}</span>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-2 mt-6">
                                <button 
                                    type="button" 
                                    @click="closeModal"
                                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="submit" 
                                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
                                    :disabled="processing"
                                >
                                    {{ processing ? 'Procesando...' : (isEditing ? 'Actualizar' : 'Crear') }}
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
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    productos: {
        type: Array,
        default: () => []
    },
    categorias: {
        type: Array,
        default: () => []
    },
    usuarios: {
        type: Array,
        default: () => []
    },
    products: {
        type: Array,
        default: () => []
    },
});

// Computed para filtrar solo proveedores
const usuariosProveedores = computed(() => {
    return props.usuarios.filter(usuario => usuario.rol === 'proveedor' || usuario.rol === 'admin');
});

const showModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);
const imageInput = ref(null);
const imagePreview = ref(null);

const form = ref({
    id: null,
    nombre: '',
    descripcion: '',
    precio: '',
    cantidad: '',
    categoria_id: '',
    proveedor_id: '',
    imagen: null,
    imagen_url: null,
});
const errors = ref({});

const formatearPrecio = (precio) => {
    if (!precio) return '0.00';
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(precio);
};

const handleImageError = (event) => {
    event.target.src = '/images/producto-placeholder.jpg';
};

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar tamaño del archivo (2MB = 2048KB)
        if (file.size > 2048 * 1024) {
            errors.value.imagen = 'La imagen no puede ser mayor a 2MB';
            return;
        }
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            errors.value.imagen = 'Solo se permiten archivos JPG, JPEG, PNG y GIF';
            return;
        }
        
        form.value.imagen = file;
        errors.value.imagen = null;
        
        // Crear vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        form.value.imagen = null;
        imagePreview.value = null;
    }
};

const showCreateModal = () => {
    form.value = { 
        id: null, 
        nombre: '', 
        descripcion: '', 
        precio: '', 
        cantidad: '', 
        categoria_id: '', 
        proveedor_id: '',
        imagen: null,
        imagen_url: null,
    };
    errors.value = {};
    imagePreview.value = null;
    showModal.value = true;
    isEditing.value = false;
};

const showEditModal = (producto) => {
    form.value = { 
        id: producto.id,
        nombre: producto.nombre,
        descripcion: producto.descripcion,
        precio: producto.precio,
        cantidad: producto.cantidad,
        categoria_id: producto.categoria_id,
        proveedor_id: producto.proveedor_id,
        imagen: null, // Nuevo archivo de imagen
        imagen_url: producto.imagen_url, // URL de imagen actual
    };
    errors.value = {};
    imagePreview.value = null;
    showModal.value = true;
    isEditing.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = { 
        id: null, 
        nombre: '', 
        descripcion: '', 
        precio: '', 
        cantidad: '', 
        categoria_id: '', 
        proveedor_id: '',
        imagen: null,
        imagen_url: null,
    };
    errors.value = {};
    processing.value = false;
    imagePreview.value = null;
    
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const createProducto = () => {
    processing.value = true;
    errors.value = {};
    
    // Para archivos, usar objeto en lugar de FormData con Inertia
    const data = {
        nombre: form.value.nombre,
        descripcion: form.value.descripcion,
        precio: form.value.precio,
        cantidad: form.value.cantidad,
        categoria_id: form.value.categoria_id,
        proveedor_id: form.value.proveedor_id,
        imagen: form.value.imagen, // Inertia maneja automáticamente el File
    };
    
    console.log('Enviando data:', data);
    
    router.post(route('admin.productos.store'), data, {
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            console.log('Error servidor:', error);
            errors.value = error;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
        preserveState: true,
    });
};

const updateProducto = () => {
    processing.value = true;
    errors.value = {};
    
    const formData = new FormData();
    
    // Agregar todos los campos del formulario excepto id e imagen_url
    Object.keys(form.value).forEach(key => {
        if (key !== 'id' && key !== 'imagen_url' && form.value[key] !== null) {
            // Si es la imagen (archivo File), agregarla tal cual
            if (key === 'imagen' && form.value[key] instanceof File) {
                formData.append(key, form.value[key], form.value[key].name);
            } else {
                formData.append(key, form.value[key]);
            }
        }
    });
    
    formData.append('_method', 'PUT');
    
    router.post(route('admin.productos.update', form.value.id), formData, {
        forceFormData: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (error) => {
            errors.value = error;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
        preserveState: true,
    });
};

const deleteProducto = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')) {
        router.delete(route('admin.productos.destroy', id));
    }
};
</script>

<style scoped>
/* Mejoras en el diseño del modal */
.max-h-screen {
    max-height: calc(100vh - 2rem);
}
</style>