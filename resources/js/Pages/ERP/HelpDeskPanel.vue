<template>
  <div class="p-8 bg-gray-900 min-h-screen text-gray-100 font-sans">
    <div class="max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
      <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-orange-400 to-amber-500 bg-clip-text text-transparent">Help Desk Interno</h1>
          <p class="text-gray-400 mt-1 text-sm">Painel de suporte ao cliente e chamados técnicos.</p>
        </div>
        <router-link to="/erp" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
          Voltar ao ERP
        </router-link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Ticket list -->
        <div class="lg:col-span-1 bg-gray-850 p-6 rounded-xl border border-gray-700">
          <h2 class="text-lg font-bold text-gray-100 mb-4 flex items-center gap-2">
            <span class="w-2 h-4 bg-orange-500 rounded-full"></span>
            Tickets Recentes
          </h2>

          <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
            <div v-for="ticket in tickets" :key="ticket.id" 
              @click="selectTicket(ticket.id)"
              class="p-4 rounded-lg border cursor-pointer transition-all"
              :class="selectedTicketId === ticket.id ? 'bg-gray-800 border-orange-500 shadow-md' : 'bg-gray-800/50 border-gray-700 hover:border-gray-600'">
              <div class="flex justify-between items-start mb-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="getPriorityClass(ticket.priority)">
                  {{ ticket.priority }}
                </span>
                <span class="text-xs text-gray-400 font-mono">#T{{ ticket.id }}</span>
              </div>
              <h3 class="font-bold text-sm text-gray-200 truncate">{{ ticket.subject }}</h3>
              <p class="text-xs text-gray-400 mt-1">Cliente: {{ ticket.customer?.name || 'Geral' }}</p>
              
              <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-700/50">
                <span class="text-[10px] text-gray-500 capitalize">{{ ticket.category }}</span>
                <span class="text-[10px] font-bold" :class="getStatusClass(ticket.status)">
                  {{ ticket.status }}
                </span>
              </div>
            </div>

            <div v-if="tickets.length === 0" class="text-center py-12 text-gray-500 italic">
              Nenhum ticket encontrado.
            </div>
          </div>
        </div>

        <!-- Conversation detail -->
        <div class="lg:col-span-2 bg-gray-850 p-6 rounded-xl border border-gray-700 flex flex-col justify-between min-h-[600px]">
          <div v-if="selectedTicket">
            <!-- Ticket Header -->
            <div class="pb-4 border-b border-gray-700 mb-6 flex justify-between items-start">
              <div>
                <h2 class="text-xl font-bold text-gray-100">{{ selectedTicket.subject }}</h2>
                <p class="text-xs text-gray-400 mt-1">
                  Aberto por <span class="text-gray-300 font-semibold">{{ selectedTicket.customer?.name }}</span> • {{ formatDate(selectedTicket.created_at) }}
                </p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase border" :class="getStatusClass(selectedTicket.status)">
                {{ selectedTicket.status }}
              </span>
            </div>

            <!-- Messages Log -->
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 mb-6">
              <!-- Customer Initial Description -->
              <div class="bg-gray-800 p-4 rounded-lg border border-gray-700/80">
                <div class="flex justify-between items-center mb-2">
                  <span class="font-bold text-xs text-orange-400">{{ selectedTicket.customer?.name }} (Descrição Inicial)</span>
                  <span class="text-[10px] text-gray-400">{{ formatDate(selectedTicket.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-300 leading-relaxed">{{ selectedTicket.description }}</p>
              </div>

              <!-- Responses list -->
              <div v-for="msg in selectedTicket.messages" :key="msg.id" 
                class="p-4 rounded-lg border"
                :class="msg.user_id ? 'bg-orange-950/20 border-orange-900/60 ml-8' : 'bg-gray-800 border-gray-700/80 mr-8'">
                <div class="flex justify-between items-center mb-2">
                  <span class="font-bold text-xs" :class="msg.user_id ? 'text-orange-300' : 'text-gray-300'">
                    {{ msg.user_id ? 'Suporte (Atendente)' : (selectedTicket.customer?.name || 'Cliente') }}
                  </span>
                  <span class="text-[10px] text-gray-400">{{ formatDate(msg.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-300 leading-relaxed">{{ msg.message }}</p>
              </div>
            </div>

            <!-- Reply Box -->
            <form @submit.prevent="sendReply" class="border-t border-gray-700 pt-4">
              <div class="flex gap-4">
                <input v-model="replyMessage" required type="text" placeholder="Digite sua resposta de suporte..." class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-200 focus:outline-none focus:border-orange-500" />
                <button type="submit" class="bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs px-6 py-2.5 rounded-lg transition-all">
                  Responder
                </button>
              </div>
            </form>
          </div>

          <div v-else class="flex-1 flex flex-col items-center justify-center text-center text-gray-500 py-12">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-600 mb-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.083.185.125.39.125.61v8.127c0 1.726-1.4 3.125-3.125 3.125H6.75A3.125 3.125 0 0 1 3.625 17.25V9.12c0-.22.042-.426.125-.611M20.25 8.511a3.124 3.124 0 0 0-2.43-1.87L17.25 6.57m3 1.94-2.18-1.57a3.124 3.124 0 0 0-3.64 0L12 8.51m8.25 0V6.75m0 1.75H12M3.75 8.511a3.124 3.124 0 0 1 2.43-1.87L6.75 6.57m-3 1.94 2.18-1.57a3.124 3.124 0 0 1 3.64 0L12 8.51m-8.25 0V6.75m0 1.75H12M3 17.25a3.125 3.125 0 0 0 3.125 3.125h11.75c.829 0 1.579-.323 2.13-1.07M12 8.51V17.25m0 0v-8.74" />
            </svg>
            <p>Selecione um chamado na lista lateral para visualizar a conversa e responder.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const tickets = ref([]);
const selectedTicket = ref(null);
const selectedTicketId = ref(null);
const replyMessage = ref('');

const loadTickets = async () => {
  try {
    const res = await axios.get('/api/v1/erp/helpdesk/tickets');
    tickets.value = Array.isArray(res.data) ? res.data : [];
    if (selectedTicketId.value) {
      selectTicket(selectedTicketId.value);
    }
  } catch (err) {
    console.error("Erro ao carregar tickets", err);
  }
};

const selectTicket = async (id) => {
  try {
    selectedTicketId.value = id;
    const res = await axios.get(`/api/v1/erp/helpdesk/tickets/${id}`);
    selectedTicket.value = res.data && typeof res.data === 'object' ? res.data : null;
  } catch (err) {
    console.error("Erro ao carregar conversa do ticket", err);
  }
};

const sendReply = async () => {
  try {
    await axios.post(`/api/v1/erp/helpdesk/tickets/${selectedTicket.value.id}/reply`, {
      message: replyMessage.value
    });
    replyMessage.value = '';
    await selectTicket(selectedTicket.value.id);
    await loadTickets();
  } catch (err) {
    console.error("Erro ao responder ticket", err);
  }
};

const getPriorityClass = (priority) => {
  switch (priority) {
    case 'high': return 'bg-red-950/50 text-red-400 border border-red-800';
    case 'medium': return 'bg-yellow-950/50 text-yellow-400 border border-yellow-800';
    default: return 'bg-blue-950/50 text-blue-400 border border-blue-800';
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'open': return 'text-orange-400 bg-orange-950/30 border-orange-900';
    case 'answered': return 'text-emerald-400 bg-emerald-950/30 border-emerald-900';
    default: return 'text-gray-400 bg-gray-800 border-gray-700';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('pt-BR');
};

onMounted(() => {
  loadTickets();
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
