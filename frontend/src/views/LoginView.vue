<template>
  <div class="text-black min-h-screen min-w-screen bg-gray-50 flex items-center justify-center p-4">
    <div class=" rounded-xl shadow-lg p-8 w-full max-w-md">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Giriş Yap</h2>

      <form @submit.prevent="login" class="space-y-4">
        <div>
          <label class="block text-gray-700 mb-2">Telefon</label>
          <input
            v-model="form.phone"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            placeholder="5551234567"
            required
          />
        </div>

        <div>
          <label class="block text-gray-700 mb-2">Şifre</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            placeholder="••••••••"
            required
          />
        </div>

        <button
          type="submit"
          class=" w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition"
          :disabled="loading"
        >
          {{ loading ? 'Giriş yapılıyor...' : 'Giriş Yap' }}
        </button>
      </form>

      <p class="mt-6 text-center text-gray-600">
        Hesabın yok mu?
        <router-link to="/register" class="text-blue-600 hover:underline">Kayıt ol</router-link>
      </p>

      <div v-if="error" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const form = ref({ phone: '', password: '' })
const error = ref('')
const loading = ref(false)
const router = useRouter()

const login = async () => {
  error.value = ''
  loading.value = true

  try {
    const res = await fetch('http://localhost:8000/api/auth.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'login', ...form.value })
    })
    const data = await res.json()

    if (data.success) {
      localStorage.setItem('user', JSON.stringify(data.user))
      // ✅ Adminse doğrudan admin paneline yönlendir
      if (data.user.role_name === 'Admin') {
        router.push('/admin')
      } else {
        router.push('/menu')
      }
    } else {
      error.value = data.error || 'Giriş başarısız.'
    }
  } catch (err) {
    error.value = 'Sunucuya bağlanılamadı.'
  } finally {
    loading.value = false
  }
}
</script>