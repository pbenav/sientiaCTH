const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    // Asegurar que las clases usadas por SweetAlert2 no sean purgadas
    safelist: [
        'swal2-popup', 'swal2-modal', 'swal2-title', 'swal2-content', 'swal2-actions',
        'swal2-confirm', 'swal2-cancel', 'swal2-icon', 'swal2-html-container'
    ],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        fontSize: {
            sm: ['14px', '20px'],
            base: ['16px', '24px'],
            lg: ['20px', '28px'],
            xl: ['24px', '32px'],
        },
        fontSize: {
            'xs': '.75rem',
            'sm': '.875rem',
            'tiny': '.875rem',
            'base': '1rem',
            'lg': '1.125rem',
            'xl': '1.25rem',
            '2xl': '1.5rem',
            '3xl': '1.875rem',
            '4xl': '2.25rem',
            '5xl': '3rem',
            '6xl': '4rem',
            '7xl': '5rem',
        },
        extend: {
            screens: {
                'xs': '480px',
            },
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};

