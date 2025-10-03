import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    tailwindcss(),
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    proxy: {
      // Frontend'den gelen '/api' ile başlayan tüm istekleri yakala
      '/api': {
        // Doğrudan XAMPP'deki ana dizine yönlendir.
        // Bu adres, sizin XAMPP sunucunuzun tam olarak nereye baktığını gösterir.
        target: 'http://localhost/yemekhane_app',
        changeOrigin: true,
        // Gelen isteğin yolunu olduğu gibi koru.
        // Yani /api/upload.php isteği, backend/api/upload.php'ye gidecek.
        rewrite: (path) => path.replace(/^\/api/, '/backend/api'),
      },
    }
  }
})