/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './wp-content/themes/sparsha-wp/**/*.php',
    './wp-content/themes/sparsha-wp/assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        forest: {
          50:  '#f0f7ec',
          100: '#ddefd3',
          200: '#b9dea6',
          300: '#8cc872',
          400: '#63ae44',
          500: '#479228',
          600: '#101403',
          700: '#101403',
          800: '#204712',
          900: '#1a3a10',
          950: '#0d1f08',
        },
        amber: {
          50:  '#fffbeb',
          100: '#fef3c7',
          200: '#fde68a',
          300: '#fcd34d',
          400: '#fbbf24',
          500: '#f59e0b',
          600: '#d97706',
          700: '#b45309',
          800: '#92400e',
          900: '#78350f',
        },
        terra: {
          50:  '#fff7ed',
          100: '#ffedd5',
          200: '#fed7aa',
          300: '#fdba74',
          400: '#fb923c',
          500: '#f97316',
          600: '#ea580c',
          700: '#c2410c',
          800: '#9a3412',
          900: '#7c2d12',
        },
        cream: '#fdf8f0',
        bark:  '#2c1810',
      },
      fontFamily: {
        serif: ['Lora', 'serif'],
        sans:  ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
