<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <header class="bg-white shadow rounded-lg p-4 mb-6 flex justify-between items-center">
      <h1 class="text-xl font-bold text-gray-800">Admin Paneli</h1>
      <button
        @click="logout"
        class="text-red-600 hover:text-red-800 font-medium"
      >
        Çıkış Yap
      </button>
    </header>

    <!-- ✅ Menü Ekleme Formu -->
    <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Menü Ekle</h2>
      <form @submit.prevent="addMenu" class="space-y-4">
        <div>
          <label class="block text-gray-700 mb-2">Tarih</label>
          <input
            v-model="menuForm.date"
            type="date"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            required
          />
        </div>

        <div>
          <label class="block text-gray-700 mb-2">Menü Açıklaması</label>
          <textarea
            v-model="menuForm.description"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            rows="4"
            placeholder="Çorba: Mercimek Çorbası, Ana Yemek: Tavuk Şiş, Yanında Pilav, Salata"
            required
          ></textarea>
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition"
          :disabled="loading"
        >
          {{ loading ? 'Ekleniyor...' : 'Menü Ekle' }}
        </button>
      </form>
    </div>

    <!-- ✅ Fiş Takibi -->
    <div class="bg-white rounded-xl shadow p-6 max-w-4xl mx-auto mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Fiş Takibi</h2>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alım Tarihi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="ticket in ticketHistory" :key="ticket.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ ticket.user_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ ticket.menu_date }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ ticket.price }} TL</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ ticket.created_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Onay Bekleyen Kullanıcılar -->
    <div class="bg-white rounded-xl shadow p-6 max-w-4xl mx-auto">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Onay Bekleyen Kullanıcılar</h2>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ad Soyad</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurum</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Belge</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlem</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in pendingUsers" :key="user._id">
            <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ user.phone }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ user.role_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <a :href="'http://localhost:8000' + user.document_path" target="_blank">Görüntüle</a>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <button @click="approveUser(user._id)" class="text-green-600 hover:text-green-900">Onayla</button>
              <button @click="rejectUser(user._id)" class="text-red-600 hover:text-red-900 ml-2">Reddet</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const pendingUsers = ref([])
const ticketHistory = ref([])
const menuForm = ref({ date: '', description: '' })
const loading = ref(false)
const router = useRouter()

onMounted(() => {
  loadPendingUsers()
  loadTicketHistory()
})

const loadPendingUsers = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/admin/pending-users.php', {
      credentials: 'include'
    })
    pendingUsers.value = await res.json()
  } catch (err) {
    alert('Sunucuya bağlanılamadı.')
  }
}

const loadTicketHistory = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/admin/ticket-history.php', {
      credentials: 'include'
    })
    ticketHistory.value = await res.json()
  } catch (err) {
    console.error('Fiş geçmişi yüklenemedi.')
  }
}

const addMenu = async () => {
  loading.value = true

  try {
    const res = await fetch('http://localhost:8000/api/menu.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'add',
        menu_date: menuForm.value.date,
        menu_description: menuForm.value.description
      })
    })
    const data = await res.json()

    if (data.success) {
      alert('Menü başarıyla eklendi.')
      menuForm.value = { date: '', description: '' }
    } else {
      alert(data.error || 'Menü eklenemedi.')
    }
  } catch (err) {
    alert('Sunucuya bağlanılamadı.')
  } finally {
    loading.value = false
  }
}

const approveUser = async (userId) => {
  try {
    const res = await fetch('http://localhost:8000/api/admin/approve-user.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId })
    })
    const data = await res.json()

    if (data.success) {
      alert('Kullanıcı onaylandı.')
      loadPendingUsers()
    } else {
      alert(data.error)
    }
  } catch (err) {
    alert('Sunucu hatası.')
  }
}

const rejectUser = async (userId) => {
  try {
    const res = await fetch('http://localhost:8000/api/admin/reject-user.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId })
    })
    const data = await res.json()

    if (data.success) {
      alert('Kullanıcı reddedildi.')
      loadPendingUsers()
    } else {
      alert(data.error)
    }
  } catch (err) {
    alert('Sunucu hatası.')
  }
}

const logout = () => {
  localStorage.removeItem('user')
  router.push('/login')
}
</script>