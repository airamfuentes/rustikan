import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#FFF7ED',
                    100: '#FFEDD5',
                    200: '#FED7AA',
                    300: '#FDBA74',
                    400: '#FB923C',
                    500: '#F97316',
                    600: '#EA580C',
                    700: '#C2410C',
                    800: '#9A3412',
                    900: '#7C2D12',
                },
                tierra: {
                    50:  '#FDF6EE',
                    100: '#FAE9D5',
                    200: '#F3CFA3',
                    300: '#EAAD6A',
                    400: '#E0903A',
                    500: '#C97420',
                    600: '#A85D18',
                    700: '#874915',
                    800: '#6B3911',
                    900: '#4E280C',
                },
            },
        },
    },

    plugins: [forms],
};
