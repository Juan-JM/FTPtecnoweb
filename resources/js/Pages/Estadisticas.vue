<template>
    <AppLayout>
      <div>
        <h2>Estadísticas de Ventas</h2>

        <!-- Mostrar Ventas Totales -->
        <div>
          <h3>Total de Ventas: {{ ventasTotalesNumerico }}</h3>
        </div>

        <!-- Gráficos en una fila -->
        <div class="chart-row">
          <!-- Gráfico de Ventas por Mes -->
          <div class="chart-container">
            <h3>Ventas por Mes</h3>
            <canvas id="ventasPorMesChart" class="chart-small"></canvas>
          </div>

          <!-- Gráfico de Ventas por Producto -->
          <div class="chart-container">
            <h3>Ventas por Producto</h3>
            <canvas id="ventasPorProductoChart" class="chart-small"></canvas>
          </div>

          <!-- Gráfico de Ventas por Usuario -->
          <div class="chart-container">
            <h3>Ventas por Usuario</h3>
            <canvas id="ventasPorUsuarioChart" class="chart-small"></canvas>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>

  <script setup>
  import { ref, onMounted } from 'vue';
  import { Chart, registerables } from 'chart.js';
  import AppLayout from '@/Layouts/AppLayout.vue';

  // Registrar todos los componentes de Chart.js
  Chart.register(...registerables);

  const props = defineProps({
    ventasTotales: String,
    ventasPorPeriodo: Array,
    ventasPorProducto: Array,
    ventasPorUsuario: Array,
  });

  const ventasTotalesNumerico = ref(0);
  const ventasPorPeriodoRef = ref([]);
  const ventasPorProductoRef = ref([]);
  const ventasPorUsuarioRef = ref([]);

  onMounted(() => {
    ventasTotalesNumerico.value = parseFloat(props.ventasTotales);
    ventasPorPeriodoRef.value = props.ventasPorPeriodo;
    ventasPorProductoRef.value = props.ventasPorProducto;
    ventasPorUsuarioRef.value = props.ventasPorUsuario;

    const labelsMes = ventasPorPeriodoRef.value.map(item => `Mes ${item.mes}`);
    const dataMes = ventasPorPeriodoRef.value.map(item => item.total_vendido);
    new Chart(document.getElementById('ventasPorMesChart'), {
      type: 'bar',
      data: {
        labels: labelsMes,
        datasets: [{
          label: 'Ventas por Mes',
          data: dataMes,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1,
        }],
      },
      options: {
        scales: {
          y: { beginAtZero: true },
        },
      },
    });

    const labelsProducto = ventasPorProductoRef.value.map(item => item.nombre);
    const dataProducto = ventasPorProductoRef.value.map(item => item.cantidad_vendida);
    new Chart(document.getElementById('ventasPorProductoChart'), {
      type: 'pie',
      data: {
        labels: labelsProducto,
        datasets: [{
          label: 'Ventas por Producto',
          data: dataProducto,
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
          ],
          borderWidth: 1,
        }],
      },
    });

    const labelsUsuario = ventasPorUsuarioRef.value.map(usuario => `${usuario.nombre} ${usuario.apellido}`);
    const dataUsuario = ventasPorUsuarioRef.value.map(usuario => usuario.total_vendido);
    new Chart(document.getElementById('ventasPorUsuarioChart'), {
      type: 'bar',
      data: {
        labels: labelsUsuario,
        datasets: [{
          label: 'Ventas por Usuario',
          data: dataUsuario,
          backgroundColor: 'rgba(153, 102, 255, 0.2)',
          borderColor: 'rgba(153, 102, 255, 1)',
          borderWidth: 1,
        }],
      },
      options: {
        scales: {
          y: { beginAtZero: true },
        },
      },
    });
  });
  </script>

  <style scoped>
  /* Estilos ajustados para los gráficos */
  .chart-small {
    max-width: 300px; /* Ancho máximo */
    max-height: 300px; /* Alto máximo */
    margin: 10px auto;
  }

  .chart-row {
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
    flex-wrap: wrap; /* Permite ajustar en pantallas pequeñas */
    margin-top: 20px;
  }

  .chart-container {
    text-align: center;
    flex: 1;
    margin: 10px;
    min-width: 250px; /* Asegura que cada gráfico tenga espacio mínimo */
  }
  </style>
