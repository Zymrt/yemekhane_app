import { createApp } from 'vue'
import App from './App.vue'
import router from './router'   // 👈 Bu satır önemli!
import { createPinia } from 'pinia'

// Tailwind CSS
import './assets/main.css'

const app = createApp(App)
app.use(createPinia())
app.use(router) // 👈 Bu satır da önemli!
app.mount('#app')