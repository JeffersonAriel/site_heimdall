<template>
  <div class="p-8 bg-gray-900 min-h-screen text-gray-100 font-sans">
    <div class="max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
      
      <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">HEIMDALL AI CORE</h1>
          <p class="text-gray-400 mt-1 text-sm">Painel de controle de inteligência operacional e auditoria.</p>
        </div>
        <router-link to="/erp" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
          Voltar ao ERP
        </router-link>
      </div>

      <!-- Feature Flag Config -->
      <div class="bg-gray-850 p-6 rounded-xl border border-gray-700 shadow-lg mb-8 flex items-center justify-between">
        <div>
          <h3 class="text-lg font-bold text-gray-150">Habilitar Inteligência Artificial</h3>
          <p class="text-xs text-gray-400 mt-1">Quando desabilitado, os listeners e gatilhos de IA ficam inativos.</p>
        </div>
        <div>
          <button @click="toggleAi" class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-md"
            :class="aiEnabled ? 'bg-emerald-600 hover:bg-emerald-500 text-white' : 'bg-rose-900/40 text-rose-300 border border-rose-800'">
            {{ aiEnabled ? 'ATIVADA (ON)' : 'DESATIVADA (OFF)' }}
          </button>
        </div>
      </div>

      <!-- AI Logs / Auditoria -->
      <div>
        <h2 class="text-xl font-bold text-gray-100 mb-6 flex items-center gap-2">
          <span class="w-3 h-6 bg-purple-500 rounded-full"></span>
          Histórico e Logs de Execução (Auditoria)
        </h2>

        <div class="bg-gray-850 border border-gray-700 rounded-xl overflow-hidden shadow-lg">
          <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-800 text-gray-400 uppercase text-[11px] tracking-wider border-b border-gray-700">
              <tr>
                <th class="px-6 py-4">Módulo</th>
                <th class="px-6 py-4">Prompt</th>
                <th class="px-6 py-4">Resposta Gerada</th>
                <th class="px-6 py-4">Tokens</th>
                <th class="px-6 py-4">Executado em</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-800/30 transition-all">
                <td class="px-6 py-4 font-bold text-purple-400">{{ log.module }}</td>
                <td class="px-6 py-4 text-xs max-w-xs truncate">{{ log.prompt }}</td>
                <td class="px-6 py-4 text-xs max-w-sm whitespace-pre-line">{{ log.response }}</td>
                <td class="px-6 py-4 font-mono text-xs text-center">{{ log.tokens_used }}</td>
                <td class="px-6 py-4 font-mono text-[11px] text-gray-400">{{ formatDate(log.created_at) }}</td>
              </tr>
              <tr v-if="logs.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">
                  Nenhum log de IA registrado.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const aiEnabled = ref(false);
const logs = ref([]);

const loadConfig = async () => {
  try {
    const res = await axios.get('/api/v1/erp/ai/config');
    aiEnabled.value = res.data && typeof res.data === 'object' ? !!res.data.enabled : false;
  } catch (err) {
    console.error("Erro ao carregar configurações de IA", err);
  }
};

const loadLogs = async () => {
  try {
    const res = await axios.get('/api/v1/erp/ai/logs');
    logs.value = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    console.error("Erro ao carregar logs de IA", err);
  }
};

const toggleAi = async () => {
  try {
    const res = await axios.post('/api/v1/erp/ai/config', {
      enabled: !aiEnabled.value
    });
    aiEnabled.value = res.data && typeof res.data === 'object' ? !!res.data.enabled : false;
  } catch (err) {
    console.error("Erro ao atualizar configuração de IA", err);
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('pt-BR');
};

onMounted(() => {
  loadConfig();
  loadLogs();
});
</script>

<style scoped>
.bg-gray-850 {
  background-color: #1F2937;
}
</style>
