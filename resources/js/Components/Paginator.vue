<template>
    <div class="pagination-container">
      <button
        class="pagination-button"
        :disabled="paginator.current_page === 1"
        @click="goToPreviousPage"
      >
        Anterior
      </button>

      <span class="pagination-info">
        Página {{ paginator.current_page }} de {{ paginator.total_pages }}
      </span>

      <button
        class="pagination-button"
        :disabled="paginator.current_page === paginator.total_pages"
        @click="goToNextPage"
      >
        Siguiente
      </button>
    </div>
  </template>

  <script setup>
  import { defineProps, defineEmits } from 'vue'

  // Definir las propiedades que el componente recibe
  const props = defineProps({
    paginator: {
      type: Object,
      required: true,
    },
  })

  // Definir los eventos que el componente emite
  const emit = defineEmits()

  // Métodos de navegación
  const goToPreviousPage = () => {
    if (props.paginator.current_page > 1) {
      emit('page-changed', props.paginator.current_page - 1)
    }
  }

  const goToNextPage = () => {
    if (props.paginator.current_page < props.paginator.total_pages) {
      emit('page-changed', props.paginator.current_page + 1)
    }
  }
  </script>

  <style scoped>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
  }

  .pagination-button {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .pagination-button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
  }

  .pagination-button:not(:disabled):hover {
    background-color: #0056b3;
  }

  .pagination-info {
    font-size: 16px;
    color: #333;
  }
  </style>
