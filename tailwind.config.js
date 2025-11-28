import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        "./resources/js/**/*.{vue,js}",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'nino': '#f1c40f',  // amarillo brillante
                'joven': '#3498db',  // azul
                'adulto': '#2ecc71',  // verde
                'dia': '#ffffff',     // blanco para el modo día
                'noche': '#2c3e50',    // gris oscuro para el modo noche
              },
              fontSize: {
                'small': '0.875rem',  // Tamaño pequeño
                'medium': '1rem',      // Tamaño mediano
                'large': '1.25rem',    // Tamaño grande
              }
        },
    },

    plugins: [forms, typography],
};
