import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#f4f6fb',
                    100: '#e7ecf6',
                    200: '#c8d5e9',
                    300: '#9db3d3',
                    400: '#6a87b4',
                    500: '#456395',
                    600: '#324c77',
                    700: '#273c60',
                    800: '#1f2f49',
                    900: '#182135',
                    950: '#0e1524',
                },
                gold: {
                    50: '#fff9e8',
                    100: '#fdefc2',
                    200: '#fbdd84',
                    300: '#f8c73f',
                    400: '#f5b301',
                    500: '#e19d00',
                    600: '#bd7a02',
                    700: '#96590a',
                    800: '#7c470f',
                    900: '#693b12',
                },
            },
            boxShadow: {
                card: '0 1px 3px rgba(24, 33, 53, 0.06), 0 12px 30px -12px rgba(24, 33, 53, 0.12)',
                'card-hover': '0 4px 12px rgba(24, 33, 53, 0.10), 0 24px 48px -16px rgba(24, 33, 53, 0.22)',
                'btn-gold': '0 8px 20px -6px rgba(245, 179, 1, 0.55)',
                'btn-gold-hover': '0 12px 28px -6px rgba(245, 179, 1, 0.65)',
                'btn-dark': '0 8px 20px -6px rgba(24, 33, 53, 0.45)',
                'btn-dark-hover': '0 12px 28px -6px rgba(24, 33, 53, 0.55)',
                'btn-danger': '0 8px 20px -6px rgba(220, 38, 38, 0.4)',
                'btn-danger-hover': '0 12px 28px -6px rgba(220, 38, 38, 0.5)',
            },
        },
    },

    plugins: [forms],
};
