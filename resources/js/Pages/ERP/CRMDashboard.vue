<template>
  <div class="p-8 bg-gray-900 min-h-screen text-gray-100 font-sans">
    <div class="max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700">
      <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-blue-400 to-indigo-500 bg-clip-text text-transparent">HEIMDALL CRM</h1>
          <p class="text-gray-400 mt-1 text-sm">Gerenciamento de Pipeline, Leads e Atividades.</p>
        </div>
        <router-link to="/erp" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-4 py-2 rounded-lg text-sm font-semibold transition-all">
          Voltar ao ERP
        </router-link>
      </div>

      <!-- Pipeline Kanban Board -->
      <div class="mb-12">
        <h2 class="text-xl font-bold text-gray-100 mb-6 flex items-center gap-2">
          <span class="w-3 h-6 bg-indigo-500 rounded-full"></span>
          Pipeline de Vendas (Kanban)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 overflow-x-auto pb-4">
          <div v-for="stage in pipeline?.stages" :key="stage.id" class="bg-gray-850 p-4 rounded-xl border border-gray-700 min-w-[220px] flex flex-col">
            <!-- Stage Header -->
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-700">
              <span class="font-bold text-sm tracking-wide" :style="{ color: stage.color || '#94A3B8' }">
                {{ stage.name }}
              </span>
              <span class="bg-gray-700 text-gray-300 text-xs px-2 py-0.5 rounded-full font-bold">
                {{ (stage.leads?.length || 0) + (stage.deals?.length || 0) }}
              </span>
            </div>

            <!-- Leads / Deals List -->
            <div class="space-y-3 flex-1 overflow-y-auto max-h-[400px]">
              <!-- Lead Cards -->
              <div v-for="lead in stage.leads" :key="lead.id" class="bg-gray-800 p-3 rounded-lg border border-gray-700 hover:border-blue-500 transition-all cursor-pointer shadow-md">
                <div class="flex justify-between items-start mb-1">
                  <span class="bg-blue-900/50 text-blue-300 border border-blue-800/80 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded">Lead</span>
                  <span class="text-xs text-gray-400 font-mono">#L{{ lead.id }}</span>
                </div>
                <h4 class="font-bold text-sm text-gray-200">{{ lead.name }}</h4>
                <p class="text-[11px] text-gray-400 mt-1 truncate">{{ lead.email }}</p>
                <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-700/50">
                  <span class="text-[10px] text-gray-400">{{ lead.source }}</span>
                  <button @click="openMoveModal(lead, 'lead')" class="text-indigo-400 hover:text-indigo-300 text-xs font-semibold">Mover</button>
                </div>
              </div>

              <!-- Deal Cards -->
              <div v-for="deal in stage.deals" :key="deal.id" class="bg-gray-800 p-3 rounded-lg border border-gray-700 hover:border-emerald-500 transition-all cursor-pointer shadow-md">
                <div class="flex justify-between items-start mb-1">
                  <span class="bg-emerald-900/50 text-emerald-300 border border-emerald-800/80 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded">Negócio</span>
                  <span class="text-xs text-gray-400 font-mono">#D{{ deal.id }}</span>
                </div>
                <h4 class="font-bold text-sm text-gray-200">{{ deal.title }}</h4>
                <p class="text-xs text-emerald-400 font-bold mt-1">R$ {{ parseFloat(deal.value).toFixed(2) }}</p>
                <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-700/50">
                  <span class="text-[10px] text-gray-400">Pedido #{{ deal.order_id }}</span>
                  <button @click="openMoveModal(deal, 'deal')" class="text-indigo-400 hover:text-indigo-300 text-xs font-semibold">Mover</button>
                </div>
              </div>

              <!-- Empty state inside stage -->
              <div v-if="(!stage.leads || stage.leads.length === 0) && (!stage.deals || stage.deals.length === 0)" class="text-center py-6 text-gray-500 text-xs italic">
                Nenhum item
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Activities Section -->
      <div>
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold text-gray-100 flex items-center gap-2">
            <span class="w-3 h-6 bg-amber-500 rounded-full"></span>
            Tarefas & Follow-up
          </h2>
          <button @click="showActivityModal = true" class="bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs px-4 py-2 rounded-lg transition-all flex items-center gap-1">
            + Agendar Atividade
          </button>
        </div>

        <div class="bg-gray-850 border border-gray-700 rounded-xl overflow-hidden shadow-lg">
          <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-800 text-gray-400 uppercase text-[11px] tracking-wider border-b border-gray-700">
              <tr>
                <th class="px-6 py-4">Tarefa</th>
                <th class="px-6 py-4">Lead</th>
                <th class="px-6 py-4">Tipo</th>
                <th class="px-6 py-4">Vencimento</th>
                <th class="px-6 py-4">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
              <tr v-for="activity in activities" :key="activity.id" class="hover:bg-gray-800/30 transition-all">
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-200">{{ activity.title }}</div>
                  <div class="text-xs text-gray-400 mt-0.5">{{ activity.description }}</div>
                </td>
                <td class="px-6 py-4">{{ activity.lead?.name || 'Lead sem nome' }}</td>
                <td class="px-6 py-4">
                  <span class="capitalize px-2 py-0.5 rounded text-xs font-semibold" :class="getTypeClass(activity.type)">
                    {{ activity.type }}
                  </span>
                </td>
                <td class="px-6 py-4 font-mono text-xs">{{ formatDate(activity.due_at) }}</td>
                <td class="px-6 py-4">
                  <span class="text-xs font-semibold" :class="activity.completed_at ? 'text-emerald-400' : 'text-amber-400'">
                    {{ activity.completed_at ? 'Concluída' : 'Pendente' }}
                  </span>
                </td>
              </tr>
              <tr v-if="activities.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">
                  Nenhuma atividade agendada.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Move Item Modal -->
      <div v-if="moveModal.open" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-gray-850 p-6 rounded-2xl border border-gray-700 max-w-md w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-100 mb-4">Mover para Estágio</h3>
          <p class="text-xs text-gray-400 mb-4">Selecione o novo estágio para: <strong class="text-gray-200">{{ moveModal.item?.name || moveModal.item?.title }}</strong></p>
          
          <div class="space-y-2 mb-6">
            <button v-for="stage in pipeline?.stages" :key="stage.id"
              @click="moveItemToStage(stage.id)"
              class="w-full text-left bg-gray-800 hover:bg-gray-700/80 p-3 rounded-lg border border-gray-700 text-sm font-semibold transition-all flex items-center justify-between">
              <span :style="{ color: stage.color || '#FFF' }">{{ stage.name }}</span>
              <span class="text-xs text-gray-500">Selecionar →</span>
            </button>
          </div>

          <div class="flex justify-end">
            <button @click="moveModal.open = false" class="bg-gray-750 hover:bg-gray-700 text-gray-300 text-xs px-4 py-2 rounded-lg font-bold transition-all">Cancelar</button>
          </div>
        </div>
      </div>

      <!-- New Activity Modal -->
      <div v-if="showActivityModal" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-gray-850 p-6 rounded-2xl border border-gray-700 max-w-md w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-100 mb-4">Agendar Atividade</h3>
          
          <form @submit.prevent="saveActivity">
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Lead Relacionado</label>
                <select v-model="newActivity.lead_id" required class="w-full bg-gray-850 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-indigo-500">
                  <option v-for="lead in allLeads" :key="lead.id" :value="lead.id">
                    {{ lead.name }} (L#{{ lead.id }})
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Título da Tarefa</label>
                <input v-model="newActivity.title" required type="text" placeholder="Ex: Ligar para apresentar proposta" class="w-full bg-gray-850 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-indigo-500" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 font-bold mb-1">Tipo</label>
                  <select v-model="newActivity.type" class="w-full bg-gray-850 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-indigo-500">
                    <option value="call">Chamada</option>
                    <option value="email">Email</option>
                    <option value="meeting">Reunião</option>
                    <option value="task">Tarefa</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs text-gray-400 font-bold mb-1">Prazo</label>
                  <input v-model="newActivity.due_at" required type="datetime-local" class="w-full bg-gray-850 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-indigo-500" />
                </div>
              </div>

              <div>
                <label class="block text-xs text-gray-400 font-bold mb-1">Observações</label>
                <textarea v-model="newActivity.description" rows="3" placeholder="Detalhes ou metas do contato..." class="w-full bg-gray-850 border border-gray-700 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-indigo-500"></textarea>
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="showActivityModal = false" class="bg-gray-750 hover:bg-gray-700 text-gray-300 text-xs px-4 py-2 rounded-lg font-bold transition-all">Cancelar</button>
              <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white text-xs px-4 py-2 rounded-lg font-bold transition-all">Salvar</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const pipeline = ref(null);
const activities = ref([]);
const showActivityModal = ref(false);
const moveModal = ref({ open: false, item: null, type: null });

const newActivity = ref({
  lead_id: '',
  title: '',
  type: 'call',
  due_at: '',
  description: ''
});

const allLeads = computed(() => {
  if (!pipeline.value) return [];
  const leads = [];
  pipeline.value.stages.forEach(stage => {
    if (stage.leads) leads.push(...stage.leads);
  });
  return leads;
});

const loadPipeline = async () => {
  try {
    const res = await axios.get('/api/v1/erp/crm/pipeline');
    pipeline.value = res.data && typeof res.data === 'object' ? res.data : { stages: [] };
    if (allLeads.value.length > 0 && !newActivity.value.lead_id) {
      newActivity.value.lead_id = allLeads.value[0].id;
    }
  } catch (err) {
    console.error("Erro ao carregar pipeline", err);
  }
};

const loadActivities = async () => {
  try {
    const res = await axios.get('/api/v1/erp/crm/activities');
    activities.value = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    console.error("Erro ao carregar atividades", err);
  }
};

const openMoveModal = (item, type) => {
  moveModal.value = { open: true, item, type };
};

const moveItemToStage = async (stageId) => {
  try {
    const item = moveModal.value.item;
    const type = moveModal.value.type;
    await axios.post(`/api/v1/erp/crm/pipeline/move/${item.id}`, {
      type: type,
      pipeline_stage_id: stageId
    });
    moveModal.value.open = false;
    await loadPipeline();
    await loadActivities();
  } catch (err) {
    console.error("Erro ao mover item", err);
  }
};

const saveActivity = async () => {
  try {
    await axios.post('/api/v1/erp/crm/activities', newActivity.value);
    showActivityModal.value = false;
    newActivity.value = { lead_id: allLeads.value[0]?.id || '', title: '', type: 'call', due_at: '', description: '' };
    await loadActivities();
  } catch (err) {
    console.error("Erro ao salvar atividade", err);
  }
};

const getTypeClass = (type) => {
  switch(type) {
    case 'call': return 'bg-blue-900/40 text-blue-300 border border-blue-800';
    case 'email': return 'bg-purple-900/40 text-purple-300 border border-purple-800';
    case 'meeting': return 'bg-emerald-900/40 text-emerald-300 border border-emerald-800';
    default: return 'bg-gray-800 text-gray-300 border border-gray-700';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('pt-BR');
};

onMounted(() => {
  loadPipeline();
  loadActivities();
});
</script>

<style scoped>
.bg-gray-850 {
  background-color: #1F2937;
}
</style>
