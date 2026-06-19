<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-lg">
      <router-link to="/carrinho" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">← Voltar ao Carrinho</router-link>

      <div v-if="success" class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
        <p class="text-5xl mb-4">🎉</p>
        <h2 class="text-2xl font-bold text-green-700 mb-2">Pedido Confirmado!</h2>
        <p class="text-gray-600 mb-2">Pedido #{{ orderId }} criado com sucesso.</p>
        <p class="text-sm text-gray-500 mb-6">Você receberá atualizações em breve.</p>
        <router-link to="/" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition inline-block">Continuar Comprando</router-link>
      </div>

      <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Finalizar Pedido</h1>

        <div class="mb-6 bg-gray-50 rounded-xl p-4">
          <p class="text-sm font-medium text-gray-600 mb-2">Resumo</p>
          <div v-for="item in cartStore.items" :key="item.product_id" class="flex justify-between text-sm py-1">
            <span class="text-gray-700">{{ item.name }} × {{ item.quantity }}</span>
            <span class="font-medium">{{ formatPrice(item.price * item.quantity) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-800 border-t pt-2 mt-2">
            <span>Total</span>
            <span class="text-blue-700">{{ formatPrice(cartStore.total) }}</span>
          </div>
        </div>

        <form @submit.prevent="placeOrder" class="space-y-4">
          <div>
            <label class="text-sm font-medium text-gray-700 block mb-1">Nome completo</label>
            <input v-model="form.name" type="text" required placeholder="Seu nome" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm" />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700 block mb-1">E-mail</label>
            <input v-model="form.email" type="email" required placeholder="seu@email.com" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm" />
          </div>

          <p v-if="error" class="text-red-500 text-sm bg-red-50 rounded-xl p-3">{{ error }}</p>

          <button
            type="submit"
            :disabled="loading || cartStore.items.length === 0"
            class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50 transition"
          >
            <span v-if="loading">Processando...</span>
            <span v-else>Confirmar Pedido</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useCartStore } from '../../stores/cart';

const cartStore = useCartStore();
const form = ref({ name: '', email: '' });
const loading = ref(false);
const error = ref(null);
const success = ref(false);
const orderId = ref(null);

function formatPrice(price) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
}

async function placeOrder() {
  if (cartStore.items.length === 0) return;
  loading.value = true;
  error.value = null;

  try {
    const payload = {
      name: form.value.name,
      email: form.value.email,
      items: cartStore.items.map((i) => ({
        product_id: i.product_id,
        quantity: i.quantity,
      })),
    };

    const { data } = await axios.post('/api/v1/checkout', payload);
    orderId.value = data.order.id;
    success.value = true;
    cartStore.clear();
  } catch (e) {
    error.value = e.response?.data?.message || 'Erro ao criar pedido. Tente novamente.';
  } finally {
    loading.value = false;
  }
}
</script>
