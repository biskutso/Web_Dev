/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.js',
    './node_modules/flowbite/**/*.js'
  ],
  safelist: [
    {
      pattern: /bg-(red|green|yellow|purple|pink|gray|indigo|emerald)-(100|200|300|400|500|600|700|800|900)/,
    },
    {
      pattern: /text-(red|green|yellow|purple|pink|gray|indigo|emerald)-(100|200|300|400|500|600|700|800|900)/,
    },
    {
      pattern: /hover:bg-(red|green|yellow|purple|pink|gray|indigo|emerald)-(100|200|300|400|500|600|700|800|900)/,
    }
  ],
  theme: {
    extend: {
      colors: {
        'dark-charcoal': '#1C1C1C',
        'primary-gold': '#C49A41',
        'hover-gold': '#a8813a',
      },
      fontFamily: {
        cinzel: ['Cinzel Decorative', 'serif'],
        montserrat: ['Montserrat', 'sans-serif'],
      },
    }
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
