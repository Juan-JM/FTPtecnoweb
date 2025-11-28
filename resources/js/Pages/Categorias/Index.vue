<template>
    <div>
      <h1>Categorías</h1>
      <ul>
        <li v-for="categoria in categorias" :key="categoria.id">
          {{ categoria.nombre }}
        </li>
      </ul>
    </div>
  </template>

  <script>
  export default {
    props: {
      categorias: Array,
    },
  };
  </script>
