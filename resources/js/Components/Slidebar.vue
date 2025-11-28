<!-- resources/js/Components/Sidebar.vue -->
<template>
    <div class="w-64 h-screen text-white bg-gray-800">
      <div class="p-6">
        <h1 class="text-xl font-semibold">Panel Administrativo</h1>
      </div>
      <ul>
        <li>
          <inertia-link href="/admin/dashboard" class="block px-4 py-2 hover:bg-gray-700">Dashboard</inertia-link>
        </li>
        <li>
          <inertia-link href="/admin/usuarios" class="block px-4 py-2 hover:bg-gray-700">Usuarios</inertia-link>
        </li>
        <li>
          <inertia-link href="/admin/roles" class="block px-4 py-2 hover:bg-gray-700">Roles</inertia-link>
        </li>
      </ul>
    </div>
  </template>

  <script>
  export default {};
  </script>
