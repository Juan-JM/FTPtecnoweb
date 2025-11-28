<template>
    <AppLayout>
        <AdminLayout>
            <h1 class="mb-6 text-2xl font-bold">Gestión de Usuarios</h1>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b">ID</th>
                        <th class="px-4 py-2 border-b">Nombre</th>
                        <th class="px-4 py-2 border-b">Apellido</th>
                        <th class="px-4 py-2 border-b">Email</th>
                        <th class="px-4 py-2 border-b">Rol</th>
                        <th class="px-4 py-2 border-b">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="usuario in usuarios" :key="usuario.id">
                        <td class="px-4 py-2 border-b">{{ usuario.id }}</td>
                        <td class="px-4 py-2 border-b">{{ usuario.nombre }}</td>
                        <td class="px-4 py-2 border-b">{{ usuario.apellido }}</td>
                        <td class="px-4 py-2 border-b">{{ usuario.email }}</td>
                        <td class="px-4 py-2 border-b">{{ usuario.rol }}</td>
                        <td class="flex px-4 py-2 space-x-2 border-b">
            <!-- Iconos de acciones -->
            <button
              class="text-blue-500 hover:text-blue-700"
              @click="verUsuario(usuario)"
              title="Ver Usuario"
            >
              📄
            </button>
            <button
              class="text-green-500 hover:text-green-700"
              @click="editarUsuario(usuario)"
              title="Editar Usuario"
            >
              ✏️
            </button>
            <button
              class="text-yellow-500 hover:text-yellow-700"
              @click="cambiarRol(usuario)"
              title="Cambiar Rol"
            >
              🔄
            </button>
            <button
              class="text-red-500 hover:text-red-700"
              @click="eliminarUsuario(usuario)"
              title="Eliminar Usuario"
            >
              🗑️
            </button>
          </td>
                    </tr>
                </tbody>
            </table>
        </AdminLayout>
    </AppLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    usuarios: Array, // La lista de usuarios enviada desde el servidor
    roles: Array, // Lista de roles disponibles
});
function verUsuario(usuario) {
  alert(`Ver detalles del usuario: ${usuario.nombre} ${usuario.apellido}`);
}

function editarUsuario(usuario) {
  alert(`Editar usuario: ${usuario.nombre} ${usuario.apellido}`);
}

function cambiarRol(usuario) {
  alert(`Cambiar rol para: ${usuario.nombre} ${usuario.apellido}`);
}

function eliminarUsuario(usuario) {
  if (confirm(`¿Estás seguro de eliminar al usuario: ${usuario.nombre} ${usuario.apellido}?`)) {
    alert(`Usuario eliminado: ${usuario.nombre} ${usuario.apellido}`);
  }
}
</script>
