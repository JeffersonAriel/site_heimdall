<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <router-link to="/" class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">← Voltar</router-link>
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Entrar na sua conta</h1>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">E-mail</label>
          <input v-model="form.email" type="email" required placeholder="seu@email.com" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm" />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Senha</label>
          <input v-model="form.password" type="password" required placeholder="••••••••" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm" />
        </div>

        <p v-if="error" class="text-red-500 text-sm bg-red-50 rounded-xl p-3">{{ error }}</p>

        <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50 transition">
          <span v-if="loading">Entrando...</span>
          <span v-else>Entrar</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const router = useRouter();

const form = ref({ email: '', password: '' });
const loading = ref(false);
const error = ref(null);

async function handleLogin() {
  loading.value = true;
  error.value = null;
  try {
    await authStore.login(form.value.email, form.value.password);
    router.push('/minha-conta');
  } catch (e) {
    error.value = e.response?.data?.message || 'Falha no login. Verifique suas credenciais.';
  } finally {
    loading.value = false;
  }
}
</script>
