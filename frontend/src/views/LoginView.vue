<template>
  <!-- HTML KISMINDA BİR DEĞİŞİKLİK YOK -->
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Giriş Yap</h2>
      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
          <input v-model="phone" type="tel" id="phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
          <input v-model="password" type="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300" :disabled="isLoading">
          {{ isLoading ? 'Giriş Yapılıyor...' : 'Giriş Yap' }}
        </button>
        <p v-if="errorMessage" class="mt-4 text-sm text-center text-red-600 bg-red-100 p-3 rounded-md">
          {{ errorMessage }}
        </p>
        <p class="mt-6 text-sm text-center text-gray-600">
          Hesabın yok mu? 
          <router-link to="/register" class="font-medium text-blue-600 hover:text-blue-500">Kayıt ol</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const phone = ref('');
const password = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const router = useRouter();

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    // ADRES DÜZELTİLDİ:
    const res = await fetch('/api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'login',
        phone: phone.value,
        password: password.value,
      }),
    });
    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.error || 'Giriş yapılamadı.');
    }
    
    // Kullanıcı bilgilerini tarayıcı hafızasında sakla
    localStorage.setItem('user', JSON.stringify(data.user));
    
    // Menü sayfasına yönlendir
    router.push('/');
    
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isLoading.value = false;
  }
};
</script>