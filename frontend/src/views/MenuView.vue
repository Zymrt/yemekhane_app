<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <header class="bg-white shadow rounded-lg p-4 mb-6 flex justify-between items-center">
      <h1 class="text-xl font-bold text-gray-800">Yemekhane Menüsü</h1>
      <button
        @click="logout"
        class="text-red-600 hover:text-red-800 font-medium"
      >
        Çıkış Yap
      </button>
    </header>

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
      <div class="mb-4">
        <span class="text-sm text-gray-500">Bugünün Menüsü</span>
        <h2 class="text-xl font-semibold text-gray-800">{{ menu.date }}</h2>
      </div>
      <p class="text-gray-700 whitespace-pre-line">{{ menu.description }}</p>

      <div class="mt-6 flex justify-between items-center">
        <div>
          <p class="text-sm text-gray-600">Bakiye:</p>
          <p class="text-lg font-bold text-green-600">{{ user.balance }} TL</p>
        </div>
        <button
          @click="buyTicket"
          class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition"
          :disabled="loading"
        >
          {{ loading ? 'Fiş Alınıyor...' : 'Fiş Al (₺' + mealPrice + ')' }}
        </button>
      </div>

      <!-- ✅ Bakiye Yükleme Formu -->
      <div class="mt-8 p-4 bg-gray-100 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Bakiye Yükle</h3>
        <div class="flex gap-3">
          <input
            v-model="amount"
            type="number"
            placeholder="Tutar (TL)"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
          />
          <button
            @click="loadBalance"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition"
          >
            Yükle
          </button>
        </div>
      </div>

      <!-- ✅ Fiş Geçmişi -->
      <div class="mt-8 p-4 bg-gray-100 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Fiş Geçmişi</h3>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alım Tarihi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="ticket in ticketHistory" :key="ticket.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ ticket.menu_date }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ ticket.price }} TL</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ ticket.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const menu = ref({ date: 'Yükleniyor...', description: '' })
const user = ref(JSON.parse(localStorage.getItem('user')))
const mealPrice = ref(0)
const loading = ref(false)
const amount = ref(0) // ✅ Yeni: bakiye miktarı
const ticketHistory = ref([]) // ✅ Yeni: fiş geçmişi
const router = useRouter()

// Menüyü yükle
const loadMenu = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/menu.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get' })
    })
    menu.value = await res.json()
  } catch (err) {
    menu.value = { date: 'Hata', description: 'Menü yüklenemedi.' }
  }
}

// Makam fiyatını al
const loadRolePrice = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/roles.php', {
      credentials: 'include'
    })
    const roles = await res.json()
    const userRole = roles.find(r => r.id === user.value.role_id)
    mealPrice.value = userRole ? userRole.meal_price : 0
  } catch (err) {
    console.error('Makam fiyatı alınamadı')
  }
}

// Fiş al
const buyTicket = async () => {
  if (user.value.balance < mealPrice.value) {
    alert('Yetersiz bakiye!')
    return
  }

  if (mealPrice.value <= 0) {
    alert('Fiş fiyatı 0 TL olamaz.')
    return
  }

  loading.value = true
  try {
    const res = await fetch('http://localhost:8000/api/ticket.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'buy',
        menu_date: menu.value.date
      })
    })
    const data = await res.json()

    if (data.success) {
      alert('Fiş başarıyla alındı!')
      user.value.balance = data.new_balance
      localStorage.setItem('user', JSON.stringify(user.value))
      loadTicketHistory() // ✅ Fiş geçmişi yenilenir
    } else {
      alert(data.error || 'Fiş alınamadı.')
    }
  } catch (err) {
    alert('Sunucu hatası.')
  } finally {
    loading.value = false
  }
}

// ✅ Fiş Geçmişi Yükle
const loadTicketHistory = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/ticket.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get_history' })
    })
    ticketHistory.value = await res.json()
  } catch (err) {
    console.error('Fiş geçmişi yüklenemedi.')
  }
}

// ✅ Bakiye Yükle
const loadBalance = async () => {
  if (!amount.value || amount.value <= 0) {
    alert('Geçerli bir tutar girin.')
    return
  }

  try {
    const res = await fetch('http://localhost:8000/api/balance.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount: parseFloat(amount.value) })
    })
    const data = await res.json()

    if (data.success) {
      alert('Bakiye yüklendi!')
      user.value.balance = data.new_balance
      localStorage.setItem('user', JSON.stringify(user.value))
    } else {
      alert(data.error || 'Bakiye yüklenemedi.')
    }
  } catch (err) {
    alert('Sunucu hatası.')
  }
}

const logout = () => {
  localStorage.removeItem('user')
  router.push('/login')
}

onMounted(() => {
  if (!user.value) {
    router.push('/login')
    return
  }
  loadMenu()
  loadRolePrice()
  loadTicketHistory() // ✅ Sayfa yüklendiğinde fiş geçmişi gelir
})
</script>