<template>
  <div class="p-8 bg-gray-900 min-h-screen text-gray-100 font-sans">
    <div class="max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
      
      <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-teal-400 to-cyan-500 bg-clip-text text-transparent">HEIMDALL BUSINESS INTELLIGENCE</h1>
          <p class="text-gray-400 mt-1 text-sm">Painel analítico alimentado por eventos transacionais.</p>
        </div>
        <router-link to="/erp" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
          Voltar ao ERP
        </router-link>
      </div>

      <!-- KPI Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700 shadow-lg flex flex-col justify-between">
          <span class="text-xs text-gray-400 uppercase tracking-wider font-bold">Faturamento Total</span>
          <h3 class="text-3xl font-bold text-teal-400 mt-2 font-mono">R$ {{ kpis.faturamento_total?.toFixed(2) }}</h3>
          <p class="text-[10px] text-gray-500 mt-4 italic">Origem: Métricas de Vendas</p>
        </div>

        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700 shadow-lg flex flex-col justify-between">
          <span class="text-xs text-gray-400 uppercase tracking-wider font-bold">Ticket Médio</span>
          <h3 class="text-3xl font-bold text-cyan-400 mt-2 font-mono">R$ {{ kpis.ticket_medio?.toFixed(2) }}</h3>
          <p class="text-[10px] text-gray-500 mt-4 italic">Calculado por Pedido ativo</p>
        </div>

        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700 shadow-lg flex flex-col justify-between">
          <span class="text-xs text-gray-400 uppercase tracking-wider font-bold">Taxa de Conversão</span>
          <h3 class="text-3xl font-bold text-purple-400 mt-2 font-mono">{{ kpis.taxa_conversao?.toFixed(1) }}%</h3>
          <p class="text-[10px] text-gray-500 mt-4 italic">Pedidos / Clientes Ativos</p>
        </div>

        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700 shadow-lg flex flex-col justify-between">
          <span class="text-xs text-gray-400 uppercase tracking-wider font-bold">Alertas de Estoque Crítico</span>
          <h3 class="text-3xl font-bold text-rose-500 mt-2 font-mono">{{ kpis.estoque_critico }}</h3>
          <p class="text-[10px] text-gray-500 mt-4 italic">Produtos abaixo do limite de segurança</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Products Sold -->
        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700 shadow-lg">
          <h2 class="text-lg font-bold text-gray-100 mb-4 flex items-center gap-2">
            <span class="w-2.5 h-5 bg-teal-500 rounded-full"></span>
            Produtos Mais Vendidos
          </h2>
          <div class="space-y-4">
            <div v-for="item in topProducts" :key="item.product_id" class="flex items-center justify-between p-3 bg-gray-800 rounded-lg border border-gray-700/50">
              <div>
                <span class="font-bold text-gray-200 text-sm">{{ item.product?.name || `Produto ID ${item.product_id}` }}</span>
                <p class="text-xs text-gray-400">Total Unidades Vendidas: <strong class="text-gray-300 font-mono">{{ item.total_qty }}</strong></p>
              </div>
              <div class="text-right">
                <span class="text-sm font-bold text-teal-400 font-mono">R$ {{ parseFloat(item.total_revenue).toFixed(2) }}</span>
              </div>
            </div>
            <div v-if="topProducts.length === 0" class="text-center py-8 text-gray-500 text-sm italic">
              Sem dados disponíveis.
            </div>
          </div>
        </div>

        <!-- Revenue Progression -->
        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700 shadow-lg">
          <h2 class="text-lg font-bold text-gray-100 mb-4 flex items-center gap-2">
            <span class="w-2.5 h-5 bg-cyan-500 rounded-full"></span>
            Faturamento Recente (Histórico por Período)
          </h2>
          <div class="space-y-4">
            <div v-for="day in revenueHistory" :key="day.date" class="flex items-center justify-between p-3 bg-gray-800 rounded-lg border border-gray-700/50">
              <span class="text-xs text-gray-300 font-mono">{{ day.date }}</span>
              <div class="flex items-center gap-4">
                <div class="w-32 bg-gray-700 rounded-full h-1.5 overflow-hidden">
                  <div class="bg-cyan-500 h-1.5" :style="{ width: `${Math.min((day.total / (kpis.faturamento_total || 1)) * 100, 100)}%` }"></div>
                </div>
                <span class="text-sm font-bold text-cyan-400 font-mono">R$ {{ parseFloat(day.total).toFixed(2) }}</span>
              </div>
            </div>
            <div v-if="revenueHistory.length === 0" class="text-center py-8 text-gray-500 text-sm italic">
              Sem dados disponíveis para o período.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const kpis = ref({
  faturamento_total: 0,
  ticket_medio: 0,
  taxa_conversao: 0,
  estoque_critico: 0
});
const topProducts = ref([]);
const revenueHistory = ref([]);

const loadData = async () => {
  try {
    const kpiRes = await axios.get('/api/v1/erp/bi/kpis');
    kpis.value = kpiRes.data;

    const topRes = await axios.get('/api/v1/erp/bi/top-products');
    topProducts.value = topRes.data;

    const revRes = await axios.get('/api/v1/erp/bi/revenue-period');
    revenueHistory.value = revRes.data;
  } catch (err) {
    console.error("Erro ao carregar dados do BI", err);
  }
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.bg-gray-850 {
  background-color: #1F2937;
}
</style>
