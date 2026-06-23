<template>
  <div class="p-8 bg-gray-900 min-h-screen text-gray-100 font-sans">
    <div class="max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
      <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-red-400 to-rose-500 bg-clip-text text-transparent">Módulo Fiscal (NF-e)</h1>
          <p class="text-gray-400 mt-1 text-sm">Controle de emissão e cancelamento de Notas Fiscais Eletrônicas.</p>
        </div>
        <router-link to="/erp" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
          Voltar ao ERP
        </router-link>
      </div>

      <!-- Quick Issue Widget -->
      <div class="mb-10 bg-gray-850 p-6 rounded-xl border border-gray-700">
        <h2 class="text-lg font-bold text-gray-100 mb-4 flex items-center gap-2">
          <span class="w-2.5 h-5 bg-rose-500 rounded-full"></span>
          Emissão Rápida de NF-e
        </h2>
        <form @submit.prevent="issueInvoice" class="flex gap-4 items-end max-w-lg">
          <div class="flex-1">
            <label class="block text-xs text-gray-400 font-bold mb-1">ID do Pedido (Order ID)</label>
            <input v-model="orderIdToIssue" required type="number" placeholder="Ex: 5" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-rose-500" />
          </div>
          <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs px-6 py-3 rounded-lg transition-all">
            Emitir NF-e
          </button>
        </form>
      </div>

      <!-- Invoices Listing -->
      <div>
        <h2 class="text-xl font-bold text-gray-100 mb-6 flex items-center gap-2">
          <span class="w-3 h-6 bg-rose-500 rounded-full"></span>
          Notas Fiscais Emitidas
        </h2>

        <div class="bg-gray-850 border border-gray-700 rounded-xl overflow-hidden shadow-lg">
          <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-800 text-gray-400 uppercase text-[11px] tracking-wider border-b border-gray-700">
              <tr>
                <th class="px-6 py-4">Número da Nota</th>
                <th class="px-6 py-4">Chave de Acesso (Access Key)</th>
                <th class="px-6 py-4">Pedido Relacionado</th>
                <th class="px-6 py-4">Status Fiscal</th>
                <th class="px-6 py-4">Data de Emissão</th>
                <th class="px-6 py-4 text-right">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
              <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-800/30 transition-all">
                <td class="px-6 py-4 font-bold text-gray-200">{{ inv.invoice_number || 'Rascunho' }}</td>
                <td class="px-6 py-4 font-mono text-xs text-gray-400">{{ inv.key || '-' }}</td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-200 font-semibold">Pedido #{{ inv.order_id }}</div>
                  <div class="text-xs text-gray-400">Cliente: {{ inv.order?.customer?.name || 'Geral' }}</div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase border" :class="getStatusClass(inv.status)">
                    {{ inv.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-xs text-gray-400">{{ formatDate(inv.created_at) }}</td>
                <td class="px-6 py-4 text-right">
                  <button v-if="inv.status === 'issued'" @click="cancelInvoice(inv.id)" class="bg-red-950/40 hover:bg-red-900/40 text-red-400 border border-red-900/60 font-bold text-xs px-3 py-1.5 rounded transition-all">
                    Cancelar Nota
                  </button>
                </td>
              </tr>
              <tr v-if="invoices.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">
                  Nenhuma nota fiscal emitida.
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

const invoices = ref([]);
const orderIdToIssue = ref('');

const loadInvoices = async () => {
  try {
    const res = await axios.get('/api/v1/erp/fiscal/invoices');
    invoices.value = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    console.error("Erro ao carregar notas", err);
  }
};

const issueInvoice = async () => {
  try {
    await axios.post('/api/v1/erp/fiscal/invoices/issue', {
      order_id: orderIdToIssue.value
    });
    orderIdToIssue.value = '';
    loadInvoices();
  } catch (err) {
    console.error("Erro ao emitir NF-e", err);
    alert(err.response?.data?.message || "Erro ao emitir");
  }
};

const cancelInvoice = async (id) => {
  if (!confirm("Tem certeza que deseja cancelar esta nota fiscal?")) return;
  try {
    await axios.post(`/api/v1/erp/fiscal/invoices/${id}/cancel`);
    loadInvoices();
  } catch (err) {
    console.error("Erro ao cancelar nota", err);
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'issued': return 'bg-emerald-950/50 text-emerald-400 border-emerald-800';
    case 'canceled': return 'bg-red-950/50 text-red-400 border-red-800';
    case 'error': return 'bg-yellow-950/50 text-yellow-400 border-yellow-800';
    default: return 'bg-gray-800 text-gray-400 border-gray-700';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('pt-BR');
};

onMounted(() => {
  loadInvoices();
});
</script>

<style scoped>
.bg-gray-855 {
  background-color: #1A222F;
}
.bg-gray-850 {
  background-color: #1F2937;
}
</style>
