<template>
  <div class="text-black min-h-screen min-w-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="rounded-xl shadow-lg p-8 w-full max-w-md">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Kayıt Ol</h2>

      <form @submit.prevent="register" class="space-y-4">
        <div>
          <label class="block text-gray-700 mb-2">Ad Soyad</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            required
          />
        </div>

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
            required
          />
        </div>

        <div>
          <label class="block text-gray-700 mb-2">Kurumunuz</label>
          <input
            v-model="form.role_name"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            placeholder="Belediye, Kaymakamlık, vs."
            required
          />
        </div>

        <!-- ✅ Belge Yükleme -->
        <div>
          <label class="block text-gray-700 mb-2">Kurum Belgesi (PDF/Resim)</label>
          <input
            @change="onFileChange"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            required
          />
        </div>

        <button
          type="submit"
          class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition"
          :disabled="loading"
        >
          {{ loading ? 'Kayıt olunuyor...' : 'Kayıt Ol' }}
        </button>
      </form>

      <p class="mt-6 text-center text-gray-600">
        Zaten hesabın var mı?
        <router-link to="/login" class="text-blue-600 hover:underline">Giriş yap</router-link>
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

const form = ref({ name: '', phone: '', password: '', role_name: '' })
const file = ref(null)
const error = ref('')
const loading = ref(false)
const router = useRouter()

const onFileChange = (e) => {
  file.value = e.target.files[0]
}

const register = async () => {
  error.value = ''
  loading.value = true

  const formData = new FormData()
  formData.append('action', 'register')
  formData.append('name', form.value.name)
  formData.append('phone', form.value.phone)
  formData.append('password', form.value.password)
  formData.append('role_name', form.value.role_name)
  formData.append('document', file.value) // ✅ Belge eklendi

  try {
    const res = await fetch('http://localhost:8000/api/auth.php', {
      method: 'POST',
      body: formData
    })
    const data = await res.json()

    if (data.success) {
      alert('Kayıt isteğiniz alındı. Admin onayı bekleniyor.')
      router.push('/login')
    } else {
      error.value = data.error || 'Kayıt başarısız.'
    }
  } catch (err) {
    error.value = 'Sunucuya bağlanılamadı.'
  } finally {
    loading.value = false
  }
}
</script>