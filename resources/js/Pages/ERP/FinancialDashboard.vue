<template>
  <div class="p-8 bg-gray-900 min-h-screen text-gray-100 font-sans">
    <div class="max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
      <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-400 to-teal-500 bg-clip-text text-transparent">Painel Financeiro</h1>
          <p class="text-gray-400 mt-1 text-sm">Fluxo de Caixa, DRE, Contas a Pagar e Contas a Receber corporativas.</p>
        </div>
        <router-link to="/erp" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
          Voltar ao ERP
        </router-link>
      </div>

      <!-- Financial Accounts Widget -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div v-for="acc in accounts" :key="acc.id" class="bg-gray-850 p-5 rounded-xl border border-gray-700 flex flex-col justify-between">
          <div>
            <span class="text-xs text-gray-400 uppercase tracking-wider capitalize">{{ acc.type }}</span>
            <h3 class="text-lg font-bold text-gray-200 mt-1">{{ acc.name }}</h3>
          </div>
          <span class="text-2xl font-extrabold mt-4 text-emerald-400 font-mono">
            R$ {{ parseFloat(acc.balance).toFixed(2) }}
          </span>
        </div>

        <div class="bg-gray-850 p-5 rounded-xl border border-gray-700 border-dashed flex items-center justify-center cursor-pointer hover:border-emerald-500 transition-all" @click="showAccountModal = true">
          <span class="text-sm font-bold text-gray-400">+ Adicionar Conta</span>
        </div>
      </div>

      <!-- Cash Flow & DRE Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <!-- Cash flow list -->
        <div class="lg:col-span-2 bg-gray-850 p-6 rounded-xl border border-gray-700">
          <h2 class="text-lg font-bold text-gray-100 mb-4 flex items-center gap-2">
            <span class="w-2.5 h-5 bg-teal-500 rounded-full"></span>
            Histórico do Fluxo de Caixa (Lançamentos)
          </h2>
          <div class="space-y-3 max-h-[350px] overflow-y-auto pr-2">
            <div v-for="cf in cashflow" :key="cf.date" class="bg-gray-800 p-4 rounded-lg border border-gray-700 flex justify-between items-center">
              <div>
                <span class="text-xs text-gray-400 font-mono">{{ formatDateOnly(cf.date) }}</span>
                <div class="flex gap-4 mt-1">
                  <span class="text-xs text-emerald-400 font-semibold">Entradas: R$ {{ parseFloat(cf.income).toFixed(2) }}</span>
                  <span class="text-xs text-red-400 font-semibold">Saídas: R$ {{ parseFloat(cf.expense).toFixed(2) }}</span>
                </div>
              </div>
              <span class="text-sm font-bold font-mono" :class="parseFloat(cf.income) - parseFloat(cf.expense) >= 0 ? 'text-emerald-400' : 'text-red-400'">
                Saldo: R$ {{ (parseFloat(cf.income) - parseFloat(cf.expense)).toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <!-- DRE summary -->
        <div class="lg:col-span-1 bg-gray-850 p-6 rounded-xl border border-gray-700">
          <h2 class="text-lg font-bold text-gray-100 mb-4 flex items-center gap-2">
            <span class="w-2.5 h-5 bg-emerald-500 rounded-full"></span>
            DRE Simplificado
          </h2>
          <div class="space-y-4 font-mono text-sm">
            <div class="flex justify-between border-b border-gray-700 pb-2">
              <span class="text-gray-400">Receita Bruta</span>
              <span class="text-emerald-400 font-bold">R$ {{ parseFloat(dreData.receita_bruta || 0).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
              <span class="text-gray-400">(-) Deduções/Impostos</span>
              <span class="text-red-400 font-bold">- R$ {{ parseFloat(dreData.deducoes || 0).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2 font-bold text-gray-200">
              <span>(=) Receita Líquida</span>
              <span>R$ {{ parseFloat(dreData.receita_liquida || 0).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
              <span class="text-gray-400">(-) CMV (Custos)</span>
              <span class="text-red-400 font-bold">- R$ {{ parseFloat(dreData.custos_produtos || 0).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2 font-bold text-gray-200">
              <span>(=) Lucro Bruto</span>
              <span>R$ {{ parseFloat(dreData.lucro_bruto || 0).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
              <span class="text-gray-400">(-) Despesas Op.</span>
              <span class="text-red-400 font-bold">- R$ {{ parseFloat(dreData.despesas_operacionais || 0).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between font-extrabold text-lg pt-2 border-t border-gray-600" :class="dreData.resultado_liquido >= 0 ? 'text-emerald-400' : 'text-red-400'">
              <span>Resultado Líquido</span>
              <span>R$ {{ parseFloat(dreData.resultado_liquido || 0).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payables and Receivables Lists -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Receivables -->
        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-gray-100 flex items-center gap-2">
              <span class="w-2 h-4 bg-emerald-500 rounded-full"></span>
              Contas a Receber
            </h2>
            <button @click="showReceivableModal = true" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs px-3 py-1.5 rounded transition-all">
              + Recebível
            </button>
          </div>

          <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2">
            <div v-for="rec in receivables" :key="rec.id" class="bg-gray-800 p-4 rounded-lg border border-gray-700 flex justify-between items-center">
              <div>
                <h4 class="font-bold text-sm text-gray-200">Recebimento #{{ rec.id }}</h4>
                <p class="text-xs text-gray-400 mt-1">Cliente: {{ rec.customer?.name || 'Cliente Geral' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Vence em: {{ formatDateOnly(rec.due_date) }}</p>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span class="font-bold font-mono text-emerald-400">R$ {{ parseFloat(rec.amount).toFixed(2) }}</span>
                <button v-if="rec.status === 'pending'" @click="openReceiveModal(rec)" class="bg-emerald-700/50 hover:bg-emerald-600 text-emerald-200 border border-emerald-600 text-[10px] font-bold px-2.5 py-1 rounded transition-all">
                  Baixar
                </button>
                <span v-else class="text-xs font-bold text-emerald-500 uppercase">Recebido</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Payables -->
        <div class="bg-gray-850 p-6 rounded-xl border border-gray-700">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-gray-100 flex items-center gap-2">
              <span class="w-2 h-4 bg-red-500 rounded-full"></span>
              Contas a Pagar
            </h2>
            <button @click="showPayableModal = true" class="bg-red-600 hover:bg-red-500 text-white font-semibold text-xs px-3 py-1.5 rounded transition-all">
              + Despesa
            </button>
          </div>

          <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2">
            <div v-for="pay in payables" :key="pay.id" class="bg-gray-800 p-4 rounded-lg border border-gray-700 flex justify-between items-center">
              <div>
                <h4 class="font-bold text-sm text-gray-200">{{ pay.description }}</h4>
                <p class="text-xs text-gray-400 mt-1">Vence em: {{ formatDateOnly(pay.due_date) }}</p>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span class="font-bold font-mono text-red-400">R$ {{ parseFloat(pay.amount).toFixed(2) }}</span>
                <button v-if="pay.status === 'pending'" @click="openPayModal(pay)" class="bg-red-700/50 hover:bg-red-600 text-red-200 border border-red-600 text-[10px] font-bold px-2.5 py-1 rounded transition-all">
                  Pagar
                </button>
                <span v-else class="text-xs font-bold text-red-500 uppercase">Pago</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- New Account Modal -->
      <div v-if="showAccountModal" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-gray-850 p-6 rounded-2xl border border-gray-700 max-w-sm w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-100 mb-4">Nova Conta Financeira</h3>
          <form @submit.prevent="saveAccount">
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Nome da Conta</label>
                <input v-model="newAccount.name" required type="text" placeholder="Ex: Banco Itaú" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200" />
              </div>
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Tipo</label>
                <select v-model="newAccount.type" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200">
                  <option value="bank">Banco / Digital</option>
                  <option value="cash">Dinheiro Físico / Caixa</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Saldo Inicial</label>
                <input v-model="newAccount.balance" required type="number" step="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200" />
              </div>
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" @click="showAccountModal = false" class="bg-gray-700 text-gray-300 text-xs px-4 py-2 rounded-lg font-bold">Cancelar</button>
              <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs px-4 py-2 rounded-lg font-bold">Salvar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Baixa de Recebível Modal -->
      <div v-if="showReceiveActionModal" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-gray-850 p-6 rounded-2xl border border-gray-700 max-w-sm w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-100 mb-4">Confirmar Recebimento</h3>
          <p class="text-xs text-gray-400 mb-4">Escolha a conta destino para receber o valor de <strong class="text-emerald-400">R$ {{ parseFloat(selectedPayment?.amount || 0).toFixed(2) }}</strong></p>
          <form @submit.prevent="confirmReceive">
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Conta Financeira Destino</label>
                <select v-model="receiptDetails.financial_account_id" required class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200">
                  <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" @click="showReceiveActionModal = false" class="bg-gray-750 text-gray-300 text-xs px-4 py-2 rounded-lg font-bold">Cancelar</button>
              <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs px-4 py-2 rounded-lg font-bold">Confirmar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Baixa de Pagável Modal -->
      <div v-if="showPayActionModal" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-gray-850 p-6 rounded-2xl border border-gray-700 max-w-sm w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-100 mb-4">Confirmar Pagamento</h3>
          <p class="text-xs text-gray-400 mb-4">Escolha a conta origem para debitar o valor de <strong class="text-red-400">R$ {{ parseFloat(selectedPayment?.amount || 0).toFixed(2) }}</strong></p>
          <form @submit.prevent="confirmPay">
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Conta Financeira Origem</label>
                <select v-model="paymentDetails.financial_account_id" required class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200">
                  <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" @click="showPayActionModal = false" class="bg-gray-750 text-gray-300 text-xs px-4 py-2 rounded-lg font-bold">Cancelar</button>
              <button type="submit" class="bg-red-600 hover:bg-red-500 text-white text-xs px-4 py-2 rounded-lg font-bold">Confirmar</button>
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

const accounts = ref([]);
const cashflow = ref([]);
const dreData = ref({});
const receivables = ref([]);
const payables = ref([]);

const showAccountModal = ref(false);
const showReceiveActionModal = ref(false);
const showPayActionModal = ref(false);

const selectedPayment = ref(null);

const newAccount = ref({ name: '', type: 'bank', balance: 0 });
const receiptDetails = ref({ financial_account_id: '' });
const paymentDetails = ref({ financial_account_id: '' });

const loadData = async () => {
  try {
    const resAccs = await axios.get('/api/v1/erp/financial/accounts');
    accounts.value = resAccs.data;
    if (accounts.value.length > 0) {
      receiptDetails.value.financial_account_id = accounts.value[0].id;
      paymentDetails.value.financial_account_id = accounts.value[0].id;
    }

    const resCf = await axios.get('/api/v1/erp/financial/cashflow');
    cashflow.value = resCf.data;

    const resDre = await axios.get('/api/v1/erp/financial/dre');
    dreData.value = resDre.data;

    const resRecs = await axios.get('/api/v1/erp/financial/receivables');
    receivables.value = resRecs.data;

    const resPays = await axios.get('/api/v1/erp/financial/payables');
    payables.value = resPays.data;
  } catch (err) {
    console.error("Erro ao carregar dados financeiros", err);
  }
};

const saveAccount = async () => {
  try {
    await axios.post('/api/v1/erp/financial/accounts', newAccount.value);
    showAccountModal.value = false;
    newAccount.value = { name: '', type: 'bank', balance: 0 };
    loadData();
  } catch (err) {
    console.error("Erro ao salvar conta", err);
  }
};

const openReceiveModal = (rec) => {
  selectedPayment.value = rec;
  showReceiveActionModal.value = true;
};

const confirmReceive = async () => {
  try {
    await axios.post(`/api/v1/erp/financial/receivables/${selectedPayment.value.id}/receive`, receiptDetails.value);
    showReceiveActionModal.value = false;
    loadData();
  } catch (err) {
    console.error("Erro ao receber", err);
  }
};

const openPayModal = (pay) => {
  selectedPayment.value = pay;
  showPayActionModal.value = true;
};

const confirmPay = async () => {
  try {
    await axios.post(`/api/v1/erp/financial/payables/${selectedPayment.value.id}/pay`, paymentDetails.value);
    showPayActionModal.value = false;
    loadData();
  } catch (err) {
    console.error("Erro ao pagar", err);
  }
};

const formatDateOnly = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('pt-BR');
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
