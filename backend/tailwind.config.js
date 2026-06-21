import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                titleEn: ['"La Raimunda"', ...defaultTheme.fontFamily.sans],
                normalEn: ["Lora", ...defaultTheme.fontFamily.sans],
                titleAr: ["Lalezar", ...defaultTheme.fontFamily.sans],
                normalAr: ["Cairo", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: "#dc2626",
                    dark: "#b91c1c",
                },
            },
        },
    },

    plugins: [forms, typography],
};
