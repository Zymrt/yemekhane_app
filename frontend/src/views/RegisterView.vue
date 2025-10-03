<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Kayıt Ol</h2>
      
      <!-- v-on:submit.prevent formun sayfa yenilemesini engeller -->
      <form @submit.prevent="handleRegister">
        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
          <input v-model="name" type="text" id="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div class="mb-4">
          <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
          <input v-model="phone" type="tel" id="phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
          <input v-model="password" type="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div class="mb-4">
          <label for="institution" class="block text-sm font-medium text-gray-700">Kurumunuz</label>
          <input v-model="institution" type="text" id="institution" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div class="mb-6">
          <label for="document" class="block text-sm font-medium text-gray-700">Kurum Belgesi (PDF/Resim)</label>
          <!-- Dosya seçildiğinde handleFileChange fonksiyonunu çağır -->
          <input @change="handleFileChange" type="file" id="document" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-300" :disabled="isLoading">
          {{ isLoading ? 'Kaydediliyor...' : 'Kayıt Ol' }}
        </button>

        <p v-if="errorMessage" class="mt-4 text-sm text-center text-red-600 bg-red-100 p-3 rounded-md">
          {{ errorMessage }}
        </p>

        <p class="mt-6 text-sm text-center text-gray-600">
          Zaten hesabın var mı? 
          <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500">Giriş yap</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const name = ref('');
const phone = ref('');
const password = ref('');
const institution = ref('');
const documentFile = ref(null); // Seçilen dosyayı tutmak için
const isLoading = ref(false);
const errorMessage = ref('');
const router = useRouter();

// Dosya input'u değiştiğinde bu fonksiyon çalışır
const handleFileChange = (event) => {
  documentFile.value = event.target.files[0];
};

// Kayıt Ol butonuna basıldığında bu fonksiyon çalışır
const handleRegister = async () => {
  if (!documentFile.value) {
    errorMessage.value = 'Lütfen kurum belgenizi seçin.';
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';

  // Önce dosyayı sunucuya yükleyeceğiz
  const formData = new FormData();
  formData.append('document', documentFile.value);

  try {
    // 1. ADIM: Dosyayı Yükle
    let documentPath = '';
    const uploadRes = await fetch('/api/upload.php', {
      method: 'POST',
      body: formData,
    });

    const uploadData = await uploadRes.json();
    if (!uploadData.success) {
      throw new Error(uploadData.error || 'Dosya yüklenemedi.');
    }
    documentPath = uploadData.filePath; // Yüklenen dosyanın yolunu al

    // 2. ADIM: Tüm form verilerini ve dosya yolunu kaydet
    const registerRes = await fetch('/api/auth.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        // --- EKLENEN EN ÖNEMLİ SATIR ---
        action: 'register', // Backend'e ne yapmak istediğimizi söylüyoruz
        // --- BİTTİ ---
        name: name.value,
        phone: phone.value,
        password: password.value,
        institution: institution.value,
        document_path: documentPath, // Sunucudaki dosya yolunu gönderiyoruz
      }),
    });

    const registerData = await registerRes.json();

    if (!registerRes.ok) {
        // Hata durumunda backend'den gelen mesajı göster
        throw new Error(registerData.error || `Bir hata oluştu: ${registerRes.status}`);
    }

    // Kayıt başarılıysa kullanıcıyı bilgilendir ve giriş sayfasına yönlendir
    alert('Kayıt başarılı! Yönetici onayından sonra giriş yapabilirsiniz.');
    router.push('/login');

  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isLoading.value = false;
  }
};
</script>