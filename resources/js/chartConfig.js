import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

// Registra los componentes necesarios de Chart.js
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

export default {
  name: 'Estadisticas',
  components: {
    BarChart: Bar, // Asegúrate de usar BarChart o el tipo de gráfico que necesites
  },
  data() {
    return {
      barChartData: {
        labels: ['Total de Ventas'], // Ajusta tus etiquetas
        datasets: [
          {
            label: 'Ventas Totales',
            data: [0], // Asegúrate de que este valor se actualice
            backgroundColor: '#42A5F5',
          },
        ],
      },
      // Otros datos de gráficos (líneas, pasteles, etc.)
    };
  },
  async mounted() {
    try {
      const response = await fetch('/estadisticas');
      const data = await response.json();

      // Asignar los datos a tus gráficos
      this.barChartData.datasets[0].data = [data.ventasTotales]; // Ajusta los datos según la respuesta
      this.lineChartData.labels = data.ventasPorPeriodo.map(item => `Mes ${item.mes}`);
      this.lineChartData.datasets[0].data = data.ventasPorPeriodo.map(item => item.total_vendido);
      this.pieChartData.labels = data.ventasPorProducto.map(item => item.nombre);
      this.pieChartData.datasets[0].data = data.ventasPorProducto.map(item => item.cantidad_vendida);
      this.pieChartUserData.labels = data.ventasPorUsuario.map(item => `${item.nombre} ${item.apellido}`);
      this.pieChartUserData.datasets[0].data = data.ventasPorUsuario.map(item => item.total_vendido);

    } catch (error) {
      console.error('Error al obtener las estadísticas:', error);
    }
  },
};
