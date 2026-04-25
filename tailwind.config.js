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
            colors: {
                parchment: '#f4ecd8',
                ink: '#1a2332',
                'ink-soft': '#3d4a5f',
                brass: '#b08d3c',
                teal: {
                    DEFAULT: '#03617A',
                    dark: '#024357',
                    light: '#e6f0f3',
                },
                cream: '#faf6ec',
                line: 'rgba(26, 35, 50, 0.15)',
                // Department palette from the mockup
                dept: {
                    deacons: '#6b2737',
                    music: '#8a5a3b',
                    sabschool: '#03617A',
                    youth: '#5c6b4d',
                    health: '#7a5c8a',
                    pathfinders: '#8a3a1a',
                },
            },
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
                serif: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            fontSize: {
                eyebrow: ['10px', { letterSpacing: '0.3em', lineHeight: '1' }],
            },
            maxWidth: {
                'reading': '42rem', // 672px — comfortable reading column
                'page': '72rem',    // 1152px — for dashboards / admin screens
            },
            boxShadow: {
                card: '0 1px 2px 0 rgba(26, 35, 50, 0.04), 0 1px 3px 0 rgba(26, 35, 50, 0.06)',
            },
        },
    },

    plugins: [forms],
};
