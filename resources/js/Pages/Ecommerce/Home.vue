<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
      <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <router-link to="/" class="text-2xl font-bold text-blue-700 tracking-tight">⚡ Heimdall</router-link>
        <nav class="flex items-center gap-6 text-sm font-medium text-gray-600">
          <router-link to="/" class="hover:text-blue-600">Produtos</router-link>
          <router-link to="/minha-conta" class="hover:text-blue-600" v-if="authStore.isLoggedIn">Minha Conta</router-link>
          <router-link to="/login" class="hover:text-blue-600" v-else>Entrar</router-link>
          <router-link to="/carrinho" class="relative hover:text-blue-600">
            🛒
            <span v-if="cartStore.totalItems > 0" class="absolute -top-2 -right-3 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
              {{ cartStore.totalItems }}
            </span>
          </router-link>
        </nav>
      </div>
    </header>

    <!-- Hero -->
    <section class="bg-gradient-to-br from-blue-700 to-indigo-800 text-white py-20 px-4 text-center">
      <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Qualidade que você pode confiar</h1>
      <p class="text-blue-200 text-lg mb-8">Descubra nosso catálogo de produtos selecionados</p>
      <a href="#produtos" class="bg-white text-blue-700 font-semibold px-8 py-3 rounded-full hover:bg-blue-50 transition">
        Ver Produtos
      </a>
    </section>

    <!-- Products -->
    <section id="produtos" class="max-w-6xl mx-auto px-4 py-14">
      <h2 class="text-2xl font-bold text-gray-800 mb-8">Nossos Produtos</h2>

      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div v-for="n in 8" :key="n" class="bg-white rounded-2xl p-4 animate-pulse">
          <div class="bg-gray-200 rounded-xl h-40 mb-4"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        </div>
      </div>

      <div v-else-if="products.length === 0" class="text-center py-20 text-gray-400">
        <p class="text-5xl mb-4">📦</p>
        <p class="text-lg">Nenhum produto disponível no momento.</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
          v-for="product in products"
          :key="product.id"
          class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden group"
        >
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 h-44 flex items-center justify-center text-5xl">
            📦
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-800 mb-1 truncate">{{ product.name }}</h3>
            <p class="text-xs text-gray-400 mb-3">SKU: {{ product.sku }}</p>
            <div class="flex items-center justify-between">
              <span class="text-blue-700 font-bold text-lg">{{ formatPrice(product.price) }}</span>
              <button
                @click="addToCart(product)"
                class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 active:scale-95 transition-all"
              >
                + Carrinho
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Toast notification -->
    <transition name="fade">
      <div v-if="toast" class="fixed bottom-6 right-6 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium z-50">
        ✅ {{ toast }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';

const cartStore = useCartStore();
const authStore = useAuthStore();

const products = ref([]);
const loading = ref(true);
const toast = ref(null);

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/v1/products');
    products.value = data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});

function addToCart(product) {
  cartStore.addItem(product);
  showToast(`"${product.name}" adicionado ao carrinho!`);
}

function formatPrice(price) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
}

function showToast(msg) {
  toast.value = msg;
  setTimeout(() => (toast.value = null), 2500);
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
