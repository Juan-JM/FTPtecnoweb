import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig(({ command, mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    // Define la ruta base para la compilación
    const build_base = env.VITE_BUILD_BASE_PATH ? `/${env.VITE_BUILD_BASE_PATH}/` : '/build/';
    const base = command === 'build' ? build_base : '/';

    return {
        base: base,
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: {
              '@': resolve(__dirname, 'resources/js'),
            },
        },
    }
});
