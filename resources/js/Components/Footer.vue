<template>
    <footer class="w-full px-4 py-4 mt-auto text-center bg-gray-800 text-white">
        <div class="flex flex-col items-center justify-between space-y-2 md:flex-row md:space-y-0">
            <div>
                <p>&copy; 2025 Tienda Dame Todos los derechos reservados.</p>
            </div>
            <div class="text-sm">
                <p>Visitas en esta página: <span class="font-bold text-blue-300">{{ contadorPagina }}</span></p>
                <p>Visitas totales del sitio: <span class="font-bold text-green-300">{{ contadorTotal }}</span></p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const contadorPagina = ref(0)
const contadorTotal = ref(0)

const obtenerContadores = async () => {
    try {
        // Usar la URL base configurada globalmente desde Ziggy
        const baseUrl = window.appBaseUrl || window.location.origin;
        const apiUrl = `${baseUrl}/api/contador-visitas`;
            
        const response = await fetch(apiUrl)
        const data = await response.json()
        contadorPagina.value = data.contadorPagina
        contadorTotal.value = data.contadorTotal
    } catch (error) {
        console.error('Error obteniendo contadores:', error)
    }
}

onMounted(() => {
    obtenerContadores()
})
</script>