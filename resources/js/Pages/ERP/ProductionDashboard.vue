<template>
  <div class="p-8 bg-gray-900 min-h-screen text-gray-100 font-sans">
    <div class="max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
      <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-teal-400 to-emerald-500 bg-clip-text text-transparent">Produção (MRP)</h1>
          <p class="text-gray-400 mt-1 text-sm">Fichas Técnicas (BOM) e Ordens de Produção industriais.</p>
        </div>
        <router-link to="/erp" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
          Voltar ao ERP
        </router-link>
      </div>

      <!-- Fichas Tecnicas (BOM) Section -->
      <div class="mb-12">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold text-gray-100 flex items-center gap-2">
            <span class="w-3 h-6 bg-teal-500 rounded-full"></span>
            Fichas Técnicas (BOM)
          </h2>
          <button @click="showBomModal = true" class="bg-teal-600 hover:bg-teal-500 text-white font-semibold text-xs px-4 py-2 rounded-lg transition-all">
            + Nova Ficha Técnica
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="bom in boms" :key="bom.id" class="bg-gray-850 p-5 rounded-xl border border-gray-700 hover:border-teal-500 transition-all flex flex-col justify-between">
            <div>
              <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-semibold text-teal-400">Yield: {{ bom.quantity }} un</span>
                <span class="text-xs text-gray-400 font-mono">BOM #{{ bom.id }}</span>
              </div>
              <h3 class="text-lg font-bold text-gray-200">{{ bom.name }}</h3>
              <p class="text-xs text-gray-400 mt-1">Produto Final: {{ bom.product?.name }} (SKU: {{ bom.product?.sku }})</p>

              <!-- Components list -->
              <div class="mt-4 space-y-2 border-t border-gray-700/50 pt-3">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Componentes</div>
                <div v-for="item in bom.items" :key="item.id" class="flex justify-between text-xs text-gray-300">
                  <span>• {{ item.product?.name }}</span>
                  <span class="font-mono text-gray-400">{{ item.quantity }} un</span>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-3 border-t border-gray-700/50 flex justify-between items-center">
              <span class="text-sm font-bold text-emerald-400">Custo: R$ {{ parseFloat(bom.production_cost).toFixed(2) }}</span>
              <button @click="openOrderModal(bom)" class="bg-teal-700/50 hover:bg-teal-600 text-teal-200 border border-teal-600 text-[11px] font-bold px-3 py-1.5 rounded transition-all">
                Lançar Ordem
              </button>
            </div>
          </div>

          <div v-if="boms.length === 0" class="col-span-3 text-center py-12 text-gray-500 italic bg-gray-850 rounded-xl border border-gray-700 border-dashed">
            Nenhuma ficha técnica cadastrada.
          </div>
        </div>
      </div>

      <!-- Ordens de Producao Section -->
      <div>
        <h2 class="text-xl font-bold text-gray-100 mb-6 flex items-center gap-2">
          <span class="w-3 h-6 bg-emerald-500 rounded-full"></span>
          Ordens de Produção (OP)
        </h2>

        <div class="bg-gray-850 border border-gray-700 rounded-xl overflow-hidden shadow-lg">
          <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-800 text-gray-400 uppercase text-[11px] tracking-wider border-b border-gray-700">
              <tr>
                <th class="px-6 py-4">Ficha Técnica</th>
                <th class="px-6 py-4">Quantidade</th>
                <th class="px-6 py-4">Custos Extras</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Datas</th>
                <th class="px-6 py-4 text-right">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
              <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-800/30 transition-all">
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-200">{{ order.bom?.name }}</div>
                  <div class="text-xs text-gray-400 mt-0.5">Yield BOM: {{ order.bom?.quantity }} un</div>
                </td>
                <td class="px-6 py-4 font-mono text-sm text-gray-200">{{ order.quantity }} un</td>
                <td class="px-6 py-4 text-emerald-400 font-mono text-sm">R$ {{ parseFloat(order.additional_cost || 0).toFixed(2) }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-0.5 rounded text-xs font-bold uppercase border" :class="getStatusClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-xs text-gray-400">
                  <div>Criação: {{ formatDate(order.created_at) }}</div>
                  <div v-if="order.completed_at">Fim: {{ formatDate(order.completed_at) }}</div>
                </td>
                <td class="px-6 py-4 text-right">
                  <button v-if="order.status === 'pending'" @click="startOrder(order.id)" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-3 py-1.5 rounded transition-all mr-2">
                    Iniciar
                  </button>
                  <button v-if="order.status === 'in_progress'" @click="completeOrder(order.id)" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-3 py-1.5 rounded transition-all">
                    Concluir
                  </button>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">
                  Nenhuma ordem de produção registrada.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- New BOM Modal -->
      <div v-if="showBomModal" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-gray-850 p-6 rounded-2xl border border-gray-700 max-w-lg w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-100 mb-4">Nova Ficha Técnica (BOM)</h3>
          
          <form @submit.prevent="saveBom">
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Nome da Ficha</label>
                <input v-model="newBom.name" required type="text" placeholder="Ex: Produção de Cerveja Pilsen" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-teal-500" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 font-bold mb-1">Produto Produzido</label>
                  <select v-model="newBom.product_id" required class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-teal-500">
                    <option v-for="prod in products" :key="prod.id" :value="prod.id">{{ prod.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs text-gray-400 font-bold mb-1">Yield (Rendimento)</label>
                  <input v-model="newBom.quantity" required type="number" step="1" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-teal-500" />
                </div>
              </div>

              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Custo Base de Produção (R$)</label>
                <input v-model="newBom.production_cost" required type="number" step="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-teal-500" />
              </div>

              <!-- Component Items Selection -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <label class="block text-xs text-gray-400 font-bold">Matérias-Primas (Componentes)</label>
                  <button type="button" @click="addBomItem" class="text-xs text-teal-400 font-bold hover:underline">+ Adicionar Item</button>
                </div>
                <div class="space-y-2 max-h-[150px] overflow-y-auto">
                  <div v-for="(item, idx) in newBom.items" :key="idx" class="flex gap-2 items-center">
                    <select v-model="item.product_id" required class="flex-1 bg-gray-800 border border-gray-700 rounded-lg p-2 text-xs text-gray-200">
                      <option v-for="prod in products" :key="prod.id" :value="prod.id">{{ prod.name }}</option>
                    </select>
                    <input v-model="item.quantity" type="number" step="0.0001" placeholder="Qtd" required class="w-20 bg-gray-800 border border-gray-700 rounded-lg p-2 text-xs text-gray-200 text-center" />
                    <button type="button" @click="removeBomItem(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2">X</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="showBomModal = false" class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-4 py-2 rounded-lg font-bold transition-all">Cancelar</button>
              <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white text-xs px-4 py-2 rounded-lg font-bold transition-all">Salvar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- New Order Modal -->
      <div v-if="showOrderModal" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-gray-850 p-6 rounded-2xl border border-gray-700 max-w-md w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-100 mb-2">Lançar Ordem de Produção</h3>
          <p class="text-xs text-gray-400 mb-4">Lançando ordem com base em: <strong class="text-gray-200">{{ selectedBom?.name }}</strong></p>

          <form @submit.prevent="saveOrder">
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Quantidade do Lote (unidades)</label>
                <input v-model="newOrder.quantity" required type="number" step="1" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-teal-500" />
              </div>
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Custo Adicional (R$)</label>
                <input v-model="newOrder.additional_cost" type="number" step="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-teal-500" />
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="showOrderModal = false" class="bg-gray-750 hover:bg-gray-700 text-gray-300 text-xs px-4 py-2 rounded-lg font-bold transition-all">Cancelar</button>
              <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white text-xs px-4 py-2 rounded-lg font-bold transition-all">Salvar Ordem</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const boms = ref([]);
const orders = ref([]);
const products = ref([]);
const showBomModal = ref(false);
const showOrderModal = ref(false);
const selectedBom = ref(null);

const newBom = ref({
  product_id: '',
  name: '',
  quantity: 1,
  production_cost: 0,
  items: []
});

const newOrder = ref({
  bom_id: '',
  quantity: 10,
  additional_cost: 0
});

const loadData = async () => {
  try {
    const resBoms = await axios.get('/api/v1/erp/production/boms');
    boms.value = resBoms.data;

    const resOrders = await axios.get('/api/v1/erp/production/orders');
    orders.value = resOrders.data;

    const resProducts = await axios.get('/api/v1/erp/crm/pipeline'); // reuse logic/products fetch or mock it
    const resProdList = await axios.get('/api/v1/bi/sales').catch(() => null); // get products
    // Load products list fallback or route
    const resProds = await axios.get('/api/v1/erp/bi/top-products').catch(() => null);
    
    // Simplification for prototype:
    products.value = [
      { id: 1, name: 'Matéria Prima Malte' },
      { id: 2, name: 'Lúpulo Especial' },
      { id: 3, name: 'Cerveja Garrafa Pronta' }
    ];
  } catch (err) {
    console.error("Erro ao carregar dados", err);
  }
};

const addBomItem = () => {
  newBom.value.items.push({ product_id: products.value[0]?.id || 1, quantity: 1 });
};

const removeBomItem = (index) => {
  newBom.value.items.splice(index, 1);
};

const saveBom = async () => {
  try {
    await axios.post('/api/v1/erp/production/boms', newBom.value);
    showBomModal.value = false;
    newBom.value = { product_id: '', name: '', quantity: 1, production_cost: 0, items: [] };
    loadData();
  } catch (err) {
    console.error("Erro ao salvar BOM", err);
  }
};

const openOrderModal = (bom) => {
  selectedBom.value = bom;
  newOrder.value.bom_id = bom.id;
  showOrderModal.value = true;
};

const saveOrder = async () => {
  try {
    await axios.post('/api/v1/erp/production/orders', newOrder.value);
    showOrderModal.value = false;
    newOrder.value = { bom_id: '', quantity: 10, additional_cost: 0 };
    loadData();
  } catch (err) {
    console.error("Erro ao lançar ordem", err);
  }
};

const startOrder = async (id) => {
  try {
    await axios.post(`/api/v1/erp/production/orders/${id}/start`);
    loadData();
  } catch (err) {
    console.error("Erro ao iniciar produção", err);
  }
};

const completeOrder = async (id) => {
  try {
    await axios.post(`/api/v1/erp/production/orders/${id}/complete`);
    loadData();
  } catch (err) {
    console.error("Erro ao concluir produção", err);
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-950/50 text-yellow-400 border-yellow-800';
    case 'in_progress': return 'bg-blue-950/50 text-blue-400 border-blue-800';
    case 'completed': return 'bg-emerald-950/50 text-emerald-400 border-emerald-800';
    default: return 'bg-gray-800 text-gray-400 border-gray-700';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('pt-BR');
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
