/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./public/**/*.{html,js,php}", "./inc/**/*.{html,js,php}"],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        primary: {
          red: "#C6100D",
          blue: "#241E4E",
          50: "#eff6ff",
          100: "#dbeafe",
          500: "#3b82f6",
          600: "#2563eb",
          700: "#1d4ed8",
        },
        accent: {
          red: "#E53E3E",
          blue: "#3182CE",
        },
      },
      screens: {
        xs: "475px",
      },
    },
  },
  plugins: [],
};
