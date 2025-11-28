<template>
    <nav class="w-full px-4 py-3 bg-white border-b border-gray-200 shadow-sm">
        <div class="flex flex-wrap items-center justify-between max-w-7xl mx-auto gap-4">
            <!-- Temas -->
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700">Temas:</span>
                <div class="flex gap-2">
                    <button 
                        @click="setTheme('ninos')" 
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
                            currentTheme === 'ninos' 
                                ? 'bg-yellow-500 text-white shadow-md ring-2 ring-yellow-300' 
                                : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
                        ]"
                    >
                        👶 Niños
                    </button>
                    <button 
                        @click="setTheme('jovenes')" 
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
                            currentTheme === 'jovenes' 
                                ? 'bg-blue-500 text-white shadow-md ring-2 ring-blue-300' 
                                : 'bg-blue-100 text-blue-800 hover:bg-blue-200'
                        ]"
                    >
                        🧑 Jóvenes
                    </button>
                    <button 
                        @click="setTheme('adultos')" 
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
                            currentTheme === 'adultos' 
                                ? 'bg-gray-700 text-white shadow-md ring-2 ring-gray-400' 
                                : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                        ]"
                    >
                        👔 Adultos
                    </button>
                </div>
            </div>

            <!-- Separador vertical -->
            <div class="hidden md:block w-px h-6 bg-gray-300"></div>

            <!-- Modo Día/Noche -->
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700">Modo:</span>
                <div class="flex gap-2">
                    <button 
                        @click="toggleDayNightMode" 
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
                            dayNightMode 
                                ? 'bg-indigo-600 text-white shadow-md' 
                                : 'bg-amber-400 text-gray-900 shadow-md'
                        ]"
                    >
                        {{ dayNightMode ? '🌙 Noche' : '☀️ Día' }}
                    </button>
                    <button 
                        @click="toggleAutoMode" 
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
                            autoMode 
                                ? 'bg-green-600 text-white shadow-md ring-2 ring-green-300' 
                                : 'bg-green-100 text-green-800 hover:bg-green-200'
                        ]"
                    >
                        🕐 Auto
                    </button>
                </div>
            </div>

            <!-- Separador vertical -->
            <div class="hidden lg:block w-px h-6 bg-gray-300"></div>

            <!-- Tamaño de Fuente -->
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700">Fuente:</span>
                <div class="flex gap-1">
                    <button 
                        @click="setFontSize('small')" 
                        :class="[
                            'px-2 py-1 text-xs font-medium rounded transition-all duration-200',
                            fontSize === 'small' 
                                ? 'bg-purple-500 text-white ring-2 ring-purple-300' 
                                : 'bg-purple-100 text-purple-800 hover:bg-purple-200'
                        ]"
                    >
                        A
                    </button>
                    <button 
                        @click="setFontSize('base')" 
                        :class="[
                            'px-2 py-1 text-sm font-medium rounded transition-all duration-200',
                            fontSize === 'base' 
                                ? 'bg-purple-500 text-white ring-2 ring-purple-300' 
                                : 'bg-purple-100 text-purple-800 hover:bg-purple-200'
                        ]"
                    >
                        A
                    </button>
                    <button 
                        @click="setFontSize('large')" 
                        :class="[
                            'px-2 py-1 text-base font-medium rounded transition-all duration-200',
                            fontSize === 'large' 
                                ? 'bg-purple-500 text-white ring-2 ring-purple-300' 
                                : 'bg-purple-100 text-purple-800 hover:bg-purple-200'
                        ]"
                    >
                        A
                    </button>
                    <button 
                        @click="setFontSize('xlarge')" 
                        :class="[
                            'px-2 py-1 text-lg font-medium rounded transition-all duration-200',
                            fontSize === 'xlarge' 
                                ? 'bg-purple-500 text-white ring-2 ring-purple-300' 
                                : 'bg-purple-100 text-purple-800 hover:bg-purple-200'
                        ]"
                    >
                        A
                    </button>
                </div>
            </div>

            <!-- Contraste -->
            <div class="flex items-center gap-3">
                <button 
                    @click="toggleContrast" 
                    :class="[
                        'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
                        contrast 
                            ? 'bg-red-600 text-white shadow-md ring-2 ring-red-300' 
                            : 'bg-red-100 text-red-800 hover:bg-red-200'
                    ]"
                >
                    {{ contrast ? '🔆 Contraste ON' : '🔅 Contraste OFF' }}
                </button>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { computed } from 'vue';
import { themeStore } from '@/store/themeStore.js';

const currentTheme = computed(() => themeStore.currentTheme);
const fontSize = computed(() => themeStore.fontSize);
const contrast = computed(() => themeStore.contrast);
const dayNightMode = computed(() => themeStore.dayNightMode);
const autoMode = computed(() => themeStore.autoMode);

const setTheme = (theme) => themeStore.setTheme(theme);
const setFontSize = (size) => themeStore.setFontSize(size);
const toggleContrast = () => themeStore.toggleContrast();
const toggleDayNightMode = () => themeStore.toggleDayNightMode();
const toggleAutoMode = () => themeStore.toggleAutoMode();
</script>