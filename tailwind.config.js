/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./vendor/filament/**/*.blade.php", 
    "./app/Livewire/**/*.php",
    "./resources/views/livewire/**/*.blade.php"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Outfit', 'Inter', 'sans-serif'],
        serif: ['Merriweather', 'serif'],
      },
      colors: {
        slate: {
          850: '#151e32', // Кастомный цвет для фона
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('@tailwindcss/forms'),
  ],
}
