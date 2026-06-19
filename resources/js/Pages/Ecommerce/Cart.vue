<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm sticky top-0 z-50">
      <div class="max-w-4xl mx-auto px-4 py-4 flex items-center gap-4">
        <router-link to="/" class="text-gray-400 hover:text-gray-600">← Voltar</router-link>
        <h1 class="text-xl font-bold text-gray-800">Carrinho</h1>
        <span class="ml-auto text-sm text-gray-400">{{ cartStore.totalItems }} item(s)</span>
      </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 py-10">
      <div v-if="cartStore.items.length === 0" class="text-center py-24 text-gray-400">
        <p class="text-6xl mb-4">🛒</p>
        <p class="text-xl mb-6">Seu carrinho está vazio.</p>
        <router-link to="/" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition">Ver Produtos</router-link>
      </div>

      <div v-else class="flex flex-col gap-6 lg:flex-row">
        <!-- Items -->
        <div class="flex-1 space-y-4">
          <div
            v-for="item in cartStore.items"
            :key="item.product_id"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4"
          >
            <div class="bg-blue-50 rounded-xl w-16 h-16 flex items-center justify-center text-2xl flex-shrink-0">📦</div>
            <div class="flex-1">
              <p class="font-semibold text-gray-800">{{ item.name }}</p>
              <p class="text-blue-600 font-bold text-sm">{{ formatPrice(item.price) }}</p>
            </div>
            <div class="flex items-center gap-2">
              <button @click="cartStore.updateQuantity(item.product_id, item.quantity - 1)" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center font-bold">-</button>
              <span class="w-6 text-center font-semibold">{{ item.quantity }}</span>
              <button @click="cartStore.updateQuantity(item.product_id, item.quantity + 1)" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center font-bold">+</button>
            </div>
            <button @click="cartStore.removeItem(item.product_id)" class="text-red-400 hover:text-red-600 ml-2 text-lg">🗑</button>
          </div>
        </div>

        <!-- Summary -->
        <div class="w-full lg:w-80">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Resumo do Pedido</h2>
            <div class="flex justify-between text-gray-600 mb-2">
              <span>Subtotal ({{ cartStore.totalItems }} items)</span>
              <span>{{ formatPrice(cartStore.total) }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-800 text-lg border-t pt-4 mt-4">
              <span>Total</span>
              <span class="text-blue-700">{{ formatPrice(cartStore.total) }}</span>
            </div>
            <router-link
              to="/checkout"
              class="block mt-6 bg-blue-600 text-white text-center py-3 rounded-xl font-semibold hover:bg-blue-700 transition"
            >
              Finalizar Pedido
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCartStore } from '../../stores/cart';

const cartStore = useCartStore();

function formatPrice(price) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
}
</script>
