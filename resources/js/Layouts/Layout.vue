<template>
    <div :class="themeClass">
      <header>
        <!-- Aquí iría tu menú de navegación u otros elementos comunes -->
      </header>

      <main>
        <slot></slot> <!-- Aquí se renderizan las páginas específicas -->
      </main>
    </div>
  </template>

  <script>
  export default {
    computed: {
      themeClass() {
        return localStorage.getItem('theme') || 'dia';  // Cambia el tema con el almacenamiento local
      }
    },
    mounted() {
      // Asegura que al cargar la página, el tema correcto esté aplicado
      const savedTheme = localStorage.getItem('theme') || 'dia';
      document.documentElement.className = savedTheme;
    }
  };
  </script>

  <style scoped>
  /* Estilos globales comunes para todos los temas */
  </style>
