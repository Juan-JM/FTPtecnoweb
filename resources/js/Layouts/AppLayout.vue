<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSelector from '@/Components/ThemeSelector.vue';
import SearchBar from '@/Components/SearchBar.vue';
import Menu from '@/Components/Menu.vue';
import Footer from '@/Components/Footer.vue';
import { themeStore } from '@/store/themeStore.js';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

// Usar la URL base configurada globalmente desde Ziggy
const logoUrl = computed(() => {
    const baseUrl = window.appBaseUrl || window.location.origin;
    return `${baseUrl}/images/Logo3.png`;
});

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

onMounted(() => {
    themeStore.init();
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <Head :title="title" />

        <!-- Barra de temas como navegación superior -->
        <ThemeSelector />

        <div class="flex-grow">
            <nav class="bg-white border-b border-gray-100 shadow-sm">
                <!-- Primary Navigation Menu -->
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex items-center shrink-0">
                                <Link :href="route('dashboard')">
                                    <img 
                                        :src="logoUrl" 
                                        alt="TeamCell Logo" 
                                        class="h-20 w-auto"
                                        @error="handleImageError"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:flex sm:space-x-8 sm:-my-px sm:ms-10">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Inicio
                                </NavLink>
                            </div>
                        </div>

                        <!-- Búsqueda centrada -->
                        <div class="flex items-center justify-center flex-1 max-w-lg mx-4">
                            <SearchBar />
                        </div>

                        <!-- Menú y usuario -->
                        <div class="flex items-center space-x-4">
                            <!-- Menú dinámico -->
                            <Menu />

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream && $page.props.jetstream.managesProfilePhotos"
                                            class="flex text-sm transition border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300">
                                            <img class="object-cover rounded-full size-8"
                                                :src="$page.props.auth.user.profile_photo_url"
                                                :alt="$page.props.auth.user.nombre">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50">
                                                {{ $page.props.auth && $page.props.auth.user ? $page.props.auth.user.nombre : 'Usuario' }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Gestionar Cuenta
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Perfil
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Cerrar Sesión
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="flex items-center -me-2 sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500"
                                @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Inicio
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream && $page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="object-cover rounded-full size-10"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.nombre">
                            </div>

                            <div>
                                <div class="text-base font-medium text-gray-800">
                                    {{ $page.props.auth && $page.props.auth.user ? $page.props.auth.user.nombre : 'Usuario' }}
                                </div>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $page.props.auth && $page.props.auth.user ? $page.props.auth.user.email : '' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Perfil
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Cerrar Sesión
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow">
                <slot />
            </main>
        </div>
        
        <!-- Footer con contador de visitas -->
        <Footer />
    </div>
</template>

<script>
export default {
    methods: {
        handleImageError(event) {
            // Si la imagen falla, usar un placeholder o texto
            event.target.style.display = 'none';
            const textLogo = document.createElement('span');
            textLogo.textContent = 'TeamCell';
            textLogo.className = 'text-xl font-bold text-blue-600';
            event.target.parentNode.appendChild(textLogo);
        }
    }
}
</script>