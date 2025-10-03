import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
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

  // --- YENİ EKLENEN BÖLÜM BAŞLANGICI ---
  server: {
    proxy: {
      // Frontend'den gelen '/api' ile başlayan tüm istekleri
      // aşağıdaki adrese yönlendir.
      '/api': {
        // !! DİKKAT: Bu adresi kendi XAMPP/WAMP sunucu adresinize göre doğrulayın !!
        // Proje klasörünüz htdocs/yemekhane_app içindeyse bu adres doğrudur.
        target: 'http://localhost/yemekhane_app/backend', 
        changeOrigin: true,
        // Yönlendirme yaparken /api kısmını istekten kaldırır.
        // Yani frontend'den /api/auth.php'ye yapılan istek,
        // backend'e http://localhost/yemekhane_app/backend/auth.php olarak gider.
        rewrite: (path) => path.replace(/^\/api/, ''),
      },
    }
  }
  // --- YENİ EKLENEN BÖLÜM SONU ---
})