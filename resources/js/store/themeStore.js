import { reactive } from 'vue';

export const themeStore = reactive({
    currentTheme: localStorage.getItem('theme') || 'ninos',
    fontSize: localStorage.getItem('fontSize') || 'base',
    contrast: localStorage.getItem('contrast') === 'true' || false,
    dayNightMode: localStorage.getItem('dayNightMode') === 'true' || false,
    autoMode: localStorage.getItem('autoMode') === 'true' || false,

    setTheme(theme) {
        this.currentTheme = theme;
        localStorage.setItem('theme', theme);
        this.updateAllClasses();
    },

    setFontSize(size) {
        this.fontSize = size;
        localStorage.setItem('fontSize', size);
        this.updateAllClasses();
    },

    toggleContrast() {
        this.contrast = !this.contrast;
        localStorage.setItem('contrast', this.contrast);
        this.updateAllClasses();
    },

    toggleDayNightMode() {
        this.dayNightMode = !this.dayNightMode;
        localStorage.setItem('dayNightMode', this.dayNightMode);
        this.updateAllClasses();
    },

    toggleAutoMode() {
        this.autoMode = !this.autoMode;
        localStorage.setItem('autoMode', this.autoMode);
        if (this.autoMode) {
            this.setAutomaticDayNight();
        }
        this.updateAllClasses();
    },

    setAutomaticDayNight() {
        const now = new Date();
        const hour = now.getHours();
        // Modo día: 6:00 AM - 6:00 PM (6-18)
        // Modo noche: 6:00 PM - 6:00 AM (18-6)
        this.dayNightMode = hour >= 18 || hour < 6;
        localStorage.setItem('dayNightMode', this.dayNightMode);
    },

    getThemeClasses() {
        const classes = [];
        
        // Aplicar modo automático si está activado
        if (this.autoMode) {
            this.setAutomaticDayNight();
        }

        // Clases base del tema
        if (this.dayNightMode) {
            // Modo noche
            switch (this.currentTheme) {
                case 'ninos':
                    classes.push('theme-ninos-night');
                    break;
                case 'jovenes':
                    classes.push('theme-jovenes-night');
                    break;
                case 'adultos':
                    classes.push('theme-adultos-night');
                    break;
            }
        } else {
            // Modo día
            switch (this.currentTheme) {
                case 'ninos':
                    classes.push('theme-ninos-day');
                    break;
                case 'jovenes':
                    classes.push('theme-jovenes-day');
                    break;
                case 'adultos':
                    classes.push('theme-adultos-day');
                    break;
            }
        }

        // Tamaño de fuente
        classes.push(`font-size-${this.fontSize}`);

        // Contraste
        if (this.contrast) {
            classes.push('high-contrast');
        }

        return classes;
    },

    updateAllClasses() {
        const root = document.documentElement;
        
        // Limpiar clases previas
        root.classList.remove(
            'theme-ninos-day', 'theme-ninos-night',
            'theme-jovenes-day', 'theme-jovenes-night', 
            'theme-adultos-day', 'theme-adultos-night',
            'font-size-small', 'font-size-base', 'font-size-large', 'font-size-xlarge',
            'high-contrast'
        );

        // Aplicar nuevas clases
        const newClasses = this.getThemeClasses();
        root.classList.add(...newClasses);

        // Aplicar variables CSS customizadas
        this.updateCSSVariables();
    },

    updateCSSVariables() {
        const root = document.documentElement;
        
        // Variables para temas
        const themeVars = this.getThemeVariables();
        Object.keys(themeVars).forEach(key => {
            root.style.setProperty(key, themeVars[key]);
        });

        // Variables para tamaño de fuente
        const fontSizes = {
            small: '14px',
            base: '16px', 
            large: '18px',
            xlarge: '20px'
        };
        root.style.setProperty('--font-size-base', fontSizes[this.fontSize]);

        // Variables para contraste
        if (this.contrast) {
            root.style.setProperty('--contrast-filter', 'contrast(200%) saturate(150%)');
        } else {
            root.style.setProperty('--contrast-filter', 'none');
        }
    },

    getThemeVariables() {
        const isNight = this.dayNightMode;
        
        switch (this.currentTheme) {
            case 'ninos':
                return isNight ? {
                    '--primary-bg': '#7c3aed', // violet-600
                    '--secondary-bg': '#db2777', // pink-600
                    '--text-primary': '#fbbf24', // yellow-400
                    '--text-secondary': '#f3e8ff', // violet-50
                    '--accent-color': '#34d399', // emerald-400
                    '--card-bg': '#1e1b4b', // indigo-900
                    '--border-color': '#8b5cf6', // violet-500
                } : {
                    '--primary-bg': '#fbbf24', // yellow-400
                    '--secondary-bg': '#f472b6', // pink-400
                    '--text-primary': '#374151', // gray-700
                    '--text-secondary': '#6b7280', // gray-500
                    '--accent-color': '#34d399', // emerald-400
                    '--card-bg': '#fffbeb', // amber-50
                    '--border-color': '#fbbf24', // yellow-400
                };
            
            case 'jovenes':
                return isNight ? {
                    '--primary-bg': '#1e40af', // blue-800
                    '--secondary-bg': '#7c2d12', // orange-800
                    '--text-primary': '#60a5fa', // blue-400
                    '--text-secondary': '#dbeafe', // blue-50
                    '--accent-color': '#06b6d4', // cyan-500
                    '--card-bg': '#0f172a', // slate-900
                    '--border-color': '#3b82f6', // blue-500
                } : {
                    '--primary-bg': '#3b82f6', // blue-500
                    '--secondary-bg': '#8b5cf6', // violet-500
                    '--text-primary': '#1f2937', // gray-800
                    '--text-secondary': '#4b5563', // gray-600
                    '--accent-color': '#06b6d4', // cyan-500
                    '--card-bg': '#eff6ff', // blue-50
                    '--border-color': '#3b82f6', // blue-500
                };
            
            case 'adultos':
                return isNight ? {
                    '--primary-bg': '#1f2937', // gray-800
                    '--secondary-bg': '#374151', // gray-700
                    '--text-primary': '#d1d5db', // gray-300
                    '--text-secondary': '#9ca3af', // gray-400
                    '--accent-color': '#4f46e5', // indigo-600
                    '--card-bg': '#111827', // gray-900
                    '--border-color': '#6b7280', // gray-500
                } : {
                    '--primary-bg': '#6b7280', // gray-500
                    '--secondary-bg': '#374151', // gray-700
                    '--text-primary': '#111827', // gray-900
                    '--text-secondary': '#4b5563', // gray-600
                    '--accent-color': '#4f46e5', // indigo-600
                    '--card-bg': '#f9fafb', // gray-50
                    '--border-color': '#d1d5db', // gray-300
                };
            
            default:
                return {};
        }
    },

    // Inicializar el store
    init() {
        // Aplicar clases iniciales
        this.updateAllClasses();
        
        // Configurar intervalo para modo automático
        if (this.autoMode) {
            setInterval(() => {
                if (this.autoMode) {
                    const previousMode = this.dayNightMode;
                    this.setAutomaticDayNight();
                    if (previousMode !== this.dayNightMode) {
                        this.updateAllClasses();
                    }
                }
            }, 60000); // Revisar cada minuto
        }
    }
});