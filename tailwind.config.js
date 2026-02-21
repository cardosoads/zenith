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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                background: '#ffffff',
                foreground: '#09090b',
                card: '#ffffff',
                'card-foreground': '#09090b',
                popover: '#ffffff',
                'popover-foreground': '#09090b',
                primary: {
                    DEFAULT: '#18181b',
                    foreground: '#fafafa',
                },
                secondary: {
                    DEFAULT: '#f4f4f5',
                    foreground: '#18181b',
                },
                muted: {
                    DEFAULT: '#f4f4f5',
                    foreground: '#71717a',
                },
                accent: {
                    DEFAULT: '#f4f4f5',
                    foreground: '#18181b',
                },
                destructive: {
                    DEFAULT: '#ef4444',
                    foreground: '#fafafa',
                },
                border: '#e4e4e7',
                input: '#e4e4e7',
                ring: '#18181b',
                success: '#10b981',
            },
        },
    },

    plugins: [forms],
};
