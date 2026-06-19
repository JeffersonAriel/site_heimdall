<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm">
      <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
        <router-link to="/" class="text-xl font-bold text-blue-700">⚡ Heimdall</router-link>
        <button @click="logout" class="text-sm text-gray-400 hover:text-red-500 transition">Sair</button>
      </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 py-10">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Olá, {{ authStore.customer?.name }}! 👋</h1>
        <p class="text-gray-500 text-sm">{{ authStore.customer?.email }}</p>
      </div>

      <h2 class="text-lg font-semibold text-gray-700 mb-4">Meus Pedidos</h2>

      <div v-if="loading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="bg-white rounded-2xl p-4 animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-1/4"></div>
        </div>
      </div>

      <div v-else-if="orders.length === 0" class="text-center py-20 text-gray-400">
        <p class="text-5xl mb-4">📋</p>
        <p>Você ainda não fez nenhum pedido.</p>
        <router-link to="/" class="mt-4 inline-block text-blue-600 hover:underline">Explorar Produtos</router-link>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="order in orders"
          :key="order.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"
        >
          <div class="flex justify-between items-start mb-3">
            <div>
              <p class="font-semibold text-gray-800">Pedido #{{ order.id }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</p>
            </div>
            <span :class="statusClass(order.status)" class="text-xs font-semibold px-3 py-1 rounded-full">
              {{ statusLabel(order.status) }}
            </span>
          </div>

          <div class="border-t pt-3 mt-3 space-y-1">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm text-gray-600">
              <span>{{ item.product?.name || 'Produto' }} × {{ item.quantity }}</span>
              <span>{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>

          <div class="flex justify-end mt-3 border-t pt-3">
            <span class="font-bold text-blue-700">Total: {{ formatPrice(order.total) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const orders = ref([]);
const loading = ref(true);

onMounted(async () => {
  if (!authStore.isLoggedIn) {
    router.push('/login');
    return;
  }
  try {
    const { data } = await axios.get('/api/v1/customer/orders');
    orders.value = data;
  } catch (e) {
    if (e.response?.status === 401) router.push('/login');
  } finally {
    loading.value = false;
  }
});

function logout() {
  authStore.logout();
  router.push('/');
}

function formatPrice(price) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
}

function formatDate(date) {
  return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(date));
}

function statusLabel(status) {
  const labels = { pending: 'Pendente', paid: 'Pago', canceled: 'Cancelado', shipped: 'Enviado' };
  return labels[status] || status;
}

function statusClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-green-100 text-green-700',
    canceled: 'bg-red-100 text-red-700',
    shipped: 'bg-blue-100 text-blue-700',
  };
  return classes[status] || 'bg-gray-100 text-gray-600';
}
</script>
