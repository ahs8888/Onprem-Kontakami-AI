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
            spacing: {
                sidebar: '250px',
            },
            colors: {
                body: '#F4F6FA',
                red: '#FF0000',
                yellow: '#3943B7',
                alert : '#3943b72e',
                kuning: '#FEB500',
                dark: '#25213B',
                online : '#38A363',
                offline : '#FE4C4C',
                success : '#029E2D',
                blue : '#3AA0FF',
                label : '#404040'
            },
            fontFamily: {
                "krub-bold": "Krub-Bold",
                "krub-light": "Krub-light",
                "krub-medium": "Krub-Medium",
                "krub-regular": "Krub-Regular",
                "krub-semibold": "Krub-SemiBold",
            },
        },
    },

    plugins: [
        forms
    ],
};
