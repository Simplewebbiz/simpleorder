/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.vue',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#e85d04',
                    dark: '#c44d03',
                    light: '#f97316',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
