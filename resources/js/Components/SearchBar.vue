<!-- src/components/SearchBar.vue -->
<template>
    <header class="p-1 bg-white">
      <div class="flex items-center justify-center gap-2 mb-2">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar información del negocio..."
          @input="handleSearch"
          @keyup.enter="performSearch"
          class="px-3 py-1 border border-gray-300 rounded-md w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <button
          @click="performSearch"
          class="px-4 py-1 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" 
        >
          Buscar
        </button>
      </div>

      <!-- Mostrar resultados de la búsqueda -->
      <div v-if="searchResults.length > 0" class="mx-auto w-72">
        <ul class="space-y-2">
          <li
            v-for="(result, index) in searchResults"
            :key="index"
            class="px-4 py-2 bg-white border border-gray-200 rounded-md hover:bg-gray-100"
          >
            {{ result }}
          </li>
        </ul>
      </div>

      <!-- Mensaje si no hay resultados -->
      <div v-if="searchResults.length === 0 && searchQuery" class="mt-4 text-center text-gray-500">
        No se encontraron resultados para "<strong>{{ searchQuery }}</strong>".
      </div>
    </header>
  </template>

  <script setup>
  import { ref } from 'vue'

  const searchQuery = ref('')  // Valor de la búsqueda
  const searchResults = ref([])  // Resultados de la búsqueda

  // Simulación de datos del negocio para búsqueda
  const businessData = [
    'Información sobre productos',
    'Horarios de atención',
    'Servicios disponibles',
    'Precios y promociones',
    'Ubicación y contacto',
  ]

  // Función que maneja la búsqueda
  const handleSearch = () => {
    if (searchQuery.value.trim() === '') {
      searchResults.value = []  // Limpiar resultados si la búsqueda está vacía
    } else {
      performSearch()
    }
  }

  // Función para realizar la búsqueda
  const performSearch = () => {
    searchResults.value = businessData.filter(item =>
      item.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }
  </script>

  <style scoped>
  /* Puedes agregar más personalizaciones aquí si lo necesitas */
  </style>
