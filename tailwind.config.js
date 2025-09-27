/** Tailwind theme generated from design-tokens.tokens.json */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            container: { center: true, padding: "16px" },
            colors: {
                brand: {
                    DEFAULT: "#19599b",
                    dark: "#003769",
                    500: "#19599b",
                    600: "#003769",
                },
                accent: "#ff7d00",
            },
            backgroundImage: {
                "brand-gradient":
                    "linear-gradient(108.1752338699356deg, #ffcd9d 0%, #c4c4c4 100%)",
            },
            fontFamily: {
                sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
                display: ["Poppins", "Inter", "ui-sans-serif"],
            },
            borderRadius: { "2xl": "1rem" },
        },
    },
    plugins: [],
};
