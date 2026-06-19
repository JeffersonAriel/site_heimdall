<template>
  <div class="helpdesk-wrap">
    <!-- Hero Header -->
    <header class="hd-header">
      <div class="hd-header-inner">
        <div class="hd-hero">
          <div class="hd-hero-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <div>
            <h1>Central de Suporte</h1>
            <p class="hd-subtitle">Abra chamados, acompanhe o status e converse com nossa equipe.</p>
          </div>
        </div>
        <button class="btn-new" @click="showNewTicket = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Novo Chamado
        </button>
      </div>

      <!-- Stats -->
      <div class="hd-stats">
        <div class="stat-pill" v-for="s in stats" :key="s.label" :style="{ borderColor: s.color + '40' }">
          <span class="stat-dot" :style="{ background: s.color }"></span>
          <span class="stat-val" :style="{ color: s.color }">{{ s.value }}</span>
          <span class="stat-label">{{ s.label }}</span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="hd-content">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-tabs">
          <button
            v-for="f in filters"
            :key="f.key"
            class="filter-tab"
            :class="{ active: activeFilter === f.key }"
            @click="activeFilter = f.key"
          >
            {{ f.label }}
            <span class="tab-count">{{ countByStatus(f.key) }}</span>
          </button>
        </div>
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="searchQ" placeholder="Buscar chamados..." />
        </div>
      </div>

      <!-- Ticket List -->
      <div class="ticket-list">
        <TransitionGroup name="ticket-anim">
          <div
            v-for="ticket in filteredTickets"
            :key="ticket.id"
            class="ticket-card"
            :class="{ selected: selectedTicket?.id === ticket.id }"
            @click="selectTicket(ticket)"
          >
            <div class="ticket-top">
              <span class="ticket-id">#{{ ticket.id }}</span>
              <span class="ticket-status" :class="ticket.status">{{ statusLabel(ticket.status) }}</span>
            </div>
            <h3 class="ticket-title">{{ ticket.subject }}</h3>
            <p class="ticket-excerpt">{{ ticket.excerpt }}</p>
            <div class="ticket-meta">
              <span class="ticket-cat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 9a3 3 0 0 1 5.12 2.1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                {{ ticket.category }}
              </span>
              <span class="ticket-date">{{ ticket.date }}</span>
              <span class="ticket-replies" v-if="ticket.replies.length">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                {{ ticket.replies.length }}
              </span>
            </div>
          </div>
        </TransitionGroup>
        <p v-if="!filteredTickets.length" class="empty-tickets">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
          Nenhum chamado encontrado.
        </p>
      </div>
    </div>

    <!-- Ticket Detail Drawer -->
    <transition name="drawer-slide">
      <div v-if="selectedTicket" class="drawer-overlay" @click.self="selectedTicket = null">
        <div class="drawer">
          <!-- Drawer Header -->
          <div class="drawer-header">
            <div>
              <div class="drawer-id">#{{ selectedTicket.id }}</div>
              <h2 class="drawer-title">{{ selectedTicket.subject }}</h2>
              <div class="drawer-meta">
                <span class="ticket-status" :class="selectedTicket.status">{{ statusLabel(selectedTicket.status) }}</span>
                <span>{{ selectedTicket.category }}</span>
                <span>{{ selectedTicket.date }}</span>
              </div>
            </div>
            <button class="drawer-close" @click="selectedTicket = null">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <!-- Original Message -->
          <div class="drawer-body">
            <div class="msg original">
              <div class="msg-avatar customer">V</div>
              <div class="msg-bubble">
                <div class="msg-author">Você</div>
                <p>{{ selectedTicket.excerpt }}</p>
                <span class="msg-time">{{ selectedTicket.date }}</span>
              </div>
            </div>

            <!-- Replies -->
            <div
              v-for="reply in selectedTicket.replies"
              :key="reply.id"
              class="msg"
              :class="reply.isSupport ? 'support-msg' : ''"
            >
              <div class="msg-avatar" :class="reply.isSupport ? 'support' : 'customer'">
                {{ reply.isSupport ? 'S' : 'V' }}
              </div>
              <div class="msg-bubble">
                <div class="msg-author">{{ reply.isSupport ? 'Suporte Heimdall' : 'Você' }}</div>
                <p>{{ reply.text }}</p>
                <span class="msg-time">{{ reply.time }}</span>
              </div>
            </div>
          </div>

          <!-- Reply Input -->
          <div class="drawer-reply" v-if="selectedTicket.status !== 'closed'">
            <textarea
              v-model="replyText"
              placeholder="Escreva sua resposta..."
              rows="3"
              @keydown.ctrl.enter="sendReply"
            ></textarea>
            <div class="reply-actions">
              <small class="reply-hint">Ctrl + Enter para enviar</small>
              <button class="btn-send" @click="sendReply" :disabled="!replyText.trim()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Enviar
              </button>
            </div>
          </div>
          <div v-else class="closed-notice">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Chamado encerrado. <a href="#" @click.prevent="reopenTicket">Reabrir</a>
          </div>
        </div>
      </div>
    </transition>

    <!-- New Ticket Modal -->
    <div v-if="showNewTicket" class="modal-overlay" @click.self="showNewTicket = false">
      <div class="modal">
        <h3>Abrir Novo Chamado</h3>
        <div class="form-group">
          <label>Assunto *</label>
          <input v-model="newTicket.subject" placeholder="Descreva resumidamente o problema" />
        </div>
        <div class="form-group">
          <label>Categoria *</label>
          <select v-model="newTicket.category">
            <option value="">Selecione...</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Prioridade</label>
          <div class="priority-select">
            <button
              v-for="p in priorities"
              :key="p.key"
              class="prio-btn"
              :class="{ active: newTicket.priority === p.key, [p.key]: true }"
              @click="newTicket.priority = p.key"
            >{{ p.label }}</button>
          </div>
        </div>
        <div class="form-group">
          <label>Descrição Detalhada *</label>
          <textarea v-model="newTicket.description" rows="5" placeholder="Descreva o problema em detalhes, com passos para reproduzir se aplicável..."></textarea>
        </div>
        <div class="modal-actions">
          <button class="btn-modal-cancel" @click="showNewTicket = false">Cancelar</button>
          <button class="btn-modal-submit" @click="submitTicket" :disabled="!newTicket.subject || !newTicket.category || !newTicket.description">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Abrir Chamado
          </button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <transition name="toast-slide">
      <div v-if="toast.show" class="toast" :class="toast.type">{{ toast.msg }}</div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/* ─── Stats ─── */
const stats = ref([
  { label: 'Abertos',      value: 2, color: '#f59e0b' },
  { label: 'Em Andamento', value: 1, color: '#6366f1' },
  { label: 'Resolvidos',   value: 5, color: '#10b981' },
  { label: 'Encerrados',   value: 3, color: '#64748b' },
]);

/* ─── Filters ─── */
const filters = [
  { key: 'all',         label: 'Todos' },
  { key: 'open',        label: 'Abertos' },
  { key: 'in_progress', label: 'Em Andamento' },
  { key: 'resolved',    label: 'Resolvidos' },
  { key: 'closed',      label: 'Encerrados' },
];
const activeFilter = ref('all');
const searchQ = ref('');

/* ─── Tickets ─── */
const tickets = ref([
  {
    id: 1042, subject: 'Pedido #9871 não chegou no prazo', status: 'open',
    category: 'Logística & Entrega', date: '18/06/2025', excerpt: 'Meu pedido estava previsto para chegar dia 15 mas ainda não recebi nada. O código de rastreio não está atualizando há 3 dias.',
    replies: [
      { id: 1, isSupport: true, text: 'Olá! Recebemos seu contato. Já estamos verificando a situação com a transportadora. Em até 24h daremos um retorno completo.', time: '18/06 14:32' }
    ]
  },
  {
    id: 1038, subject: 'Produto chegou com defeito', status: 'in_progress',
    category: 'Trocas & Devoluções', date: '15/06/2025', excerpt: 'O Monitor 27" IPS chegou com a tela trincada na parte inferior direita. Preciso de orientações para troca.',
    replies: [
      { id: 1, isSupport: true, text: 'Lamentamos o ocorrido! Para darmos continuidade, precisamos de fotos do produto e da embalagem. Pode enviar pelo e-mail suporte@heimdall.com?', time: '15/06 10:15' },
      { id: 2, isSupport: false, text: 'Já enviei as fotos por e-mail. Aguardo retorno.', time: '16/06 09:45' },
      { id: 3, isSupport: true, text: 'Imagens recebidas! Aprovamos a troca. Em até 2 dias úteis você receberá a etiqueta de devolução e o produto substituto será enviado na sequência.', time: '17/06 11:00' },
    ]
  },
  {
    id: 1031, subject: 'Cupom de desconto não está funcionando', status: 'resolved',
    category: 'Pagamentos & Cobranças', date: '10/06/2025', excerpt: 'Tentei usar o cupom WELCOME10 mas o site apresenta erro de "cupom inválido" mesmo com o prazo de validade correto.',
    replies: [
      { id: 1, isSupport: true, text: 'Identificamos o problema! O cupom estava com uma restrição de categoria que não se aplicava aos seus itens. Corrigimos e aplicamos um cupom especial de 15% para compensar: SUPORTE15. Válido por 7 dias.', time: '11/06 16:20' },
    ]
  },
  {
    id: 1025, subject: 'Dúvida sobre garantia estendida', status: 'closed',
    category: 'Dúvidas Gerais', date: '02/06/2025', excerpt: 'Gostaria de saber como funciona a garantia estendida para os produtos da categoria eletrônicos.',
    replies: [
      { id: 1, isSupport: true, text: 'A garantia estendida Heimdall cobre defeitos de fabricação por 24 meses adicionais. Para ativar, acesse sua conta em Meus Pedidos > Garantia Estendida dentro de 30 dias após a compra.', time: '02/06 12:00' },
    ]
  },
  {
    id: 1019, subject: 'Nota fiscal não recebida por e-mail', status: 'open',
    category: 'Fiscal & NF-e', date: '18/06/2025', excerpt: 'Realizei compra no dia 17/06 (pedido #9905) e não recebi a nota fiscal por e-mail. Preciso para reembolso corporativo.',
    replies: []
  },
]);

const countByStatus = (key) => {
  if (key === 'all') return tickets.value.length;
  return tickets.value.filter(t => t.status === key).length;
};

const filteredTickets = computed(() => {
  let list = tickets.value;
  if (activeFilter.value !== 'all') list = list.filter(t => t.status === activeFilter.value);
  if (searchQ.value.trim()) {
    const q = searchQ.value.toLowerCase();
    list = list.filter(t => t.subject.toLowerCase().includes(q) || t.excerpt.toLowerCase().includes(q));
  }
  return list;
});

const statusLabel = (s) => ({
  open: '🟡 Aberto', in_progress: '🔵 Em Andamento', resolved: '🟢 Resolvido', closed: '⚫ Encerrado'
})[s] || s;

/* ─── Ticket Detail ─── */
const selectedTicket = ref(null);
const replyText = ref('');

const selectTicket = (t) => {
  selectedTicket.value = t;
  replyText.value = '';
};

const sendReply = () => {
  if (!replyText.value.trim()) return;
  selectedTicket.value.replies.push({
    id: Date.now(), isSupport: false, text: replyText.value, time: 'Agora'
  });
  replyText.value = '';
  showToast('Resposta enviada com sucesso!', 'success');
  if (selectedTicket.value.status === 'open') selectedTicket.value.status = 'in_progress';
};

const reopenTicket = () => {
  if (selectedTicket.value) {
    selectedTicket.value.status = 'open';
    showToast('Chamado reaberto!', 'info');
  }
};

/* ─── New Ticket ─── */
const showNewTicket = ref(false);
const categories = ['Logística & Entrega', 'Trocas & Devoluções', 'Pagamentos & Cobranças', 'Fiscal & NF-e', 'Dúvidas Gerais', 'Produto com Defeito', 'Outros'];
const priorities = [
  { key: 'low',    label: 'Baixa' },
  { key: 'normal', label: 'Normal' },
  { key: 'high',   label: 'Alta' },
];
const newTicket = ref({ subject: '', category: '', priority: 'normal', description: '' });

const submitTicket = () => {
  if (!newTicket.value.subject || !newTicket.value.category || !newTicket.value.description) return;
  const id = Math.max(...tickets.value.map(t => t.id)) + 1;
  tickets.value.unshift({
    id, subject: newTicket.value.subject, status: 'open',
    category: newTicket.value.category, date: new Date().toLocaleDateString('pt-BR'),
    excerpt: newTicket.value.description, replies: []
  });
  showToast(`Chamado #${id} aberto com sucesso!`, 'success');
  newTicket.value = { subject: '', category: '', priority: 'normal', description: '' };
  showNewTicket.value = false;
};

/* ─── Toast ─── */
const toast = ref({ show: false, msg: '', type: 'success' });
const showToast = (msg, type = 'success') => {
  toast.value = { show: true, msg, type };
  setTimeout(() => { toast.value.show = false; }, 3500);
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.helpdesk-wrap {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  background: #0f172a;
  color: #e2e8f0;
}

/* Header */
.hd-header {
  background: linear-gradient(135deg, #1a103c 0%, #0f172a 100%);
  border-bottom: 1px solid #1e293b;
  padding: 28px 32px 0;
}
.hd-header-inner { display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; padding-bottom: 20px; }
.hd-hero { display: flex; align-items: center; gap: 20px; }
.hd-hero-icon {
  width: 56px; height: 56px; border-radius: 16px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  box-shadow: 0 8px 24px rgba(99,102,241,.35);
}
.hd-hero-icon svg { width: 28px; height: 28px; stroke: #fff; }
h1 { font-size: 1.6rem; font-weight: 700; color: #f8fafc; }
.hd-subtitle { font-size: .875rem; color: #64748b; margin-top: 4px; max-width: 400px; }

.btn-new {
  display: flex; align-items: center; gap: 8px;
  padding: 12px 22px; border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; font-size: .9rem; font-weight: 600; border: none; cursor: pointer;
  box-shadow: 0 4px 16px rgba(99,102,241,.35); transition: all .2s;
}
.btn-new:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,.45); }
.btn-new svg { width: 16px; height: 16px; }

/* Stats */
.hd-stats { display: flex; gap: 12px; max-width: 1200px; margin: 0 auto; padding-bottom: 20px; flex-wrap: wrap; }
.stat-pill {
  display: flex; align-items: center; gap: 8px; padding: 8px 16px;
  background: #1e293b; border: 1px solid; border-radius: 24px;
  font-size: .8rem;
}
.stat-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.stat-val { font-weight: 700; font-size: .9rem; }
.stat-label { color: #64748b; }

/* Content */
.hd-content { max-width: 1200px; margin: 28px auto; padding: 0 32px; }

/* Filter Bar */
.filter-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.filter-tabs { display: flex; gap: 4px; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 4px; }
.filter-tab { padding: 8px 16px; border-radius: 8px; border: none; background: none; color: #64748b; font-size: .85rem; font-weight: 500; cursor: pointer; transition: all .2s; display: flex; align-items: center; gap: 8px; }
.filter-tab:hover { color: #e2e8f0; }
.filter-tab.active { background: #6366f1; color: #fff; }
.tab-count { font-size: .72rem; background: #ffffff20; padding: 1px 6px; border-radius: 10px; }
.filter-tab.active .tab-count { background: #ffffff30; }
.search-wrap { display: flex; align-items: center; gap: 8px; background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 10px 14px; }
.search-wrap svg { width: 16px; height: 16px; color: #475569; flex-shrink: 0; }
.search-wrap input { background: none; border: none; outline: none; color: #e2e8f0; font-size: .875rem; width: 200px; }

/* Ticket List */
.ticket-list { display: flex; flex-direction: column; gap: 12px; }
.ticket-card {
  background: #1e293b; border: 1px solid #334155; border-radius: 14px;
  padding: 20px; cursor: pointer; transition: all .2s;
}
.ticket-card:hover { border-color: #6366f1; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.2); }
.ticket-card.selected { border-color: #6366f1; background: #1e1f3a; }
.ticket-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.ticket-id { font-size: .78rem; font-family: monospace; color: #475569; }
.ticket-title { font-size: 1rem; font-weight: 600; color: #f8fafc; margin-bottom: 6px; }
.ticket-excerpt { font-size: .845rem; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; }
.ticket-meta { display: flex; align-items: center; gap: 16px; margin-top: 12px; }
.ticket-cat { display: flex; align-items: center; gap: 4px; font-size: .78rem; color: #475569; }
.ticket-cat svg { width: 13px; height: 13px; }
.ticket-date { font-size: .78rem; color: #334155; margin-left: auto; }
.ticket-replies { display: flex; align-items: center; gap: 4px; font-size: .78rem; color: #6366f1; }
.ticket-replies svg { width: 13px; height: 13px; }

/* Status badges */
.ticket-status { font-size: .75rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
.ticket-status.open        { background: #f59e0b20; color: #fbbf24; border: 1px solid #f59e0b30; }
.ticket-status.in_progress { background: #6366f120; color: #a5b4fc; border: 1px solid #6366f130; }
.ticket-status.resolved    { background: #10b98120; color: #34d399; border: 1px solid #10b98130; }
.ticket-status.closed      { background: #64748b20; color: #94a3b8; border: 1px solid #64748b30; }

/* Empty state */
.empty-tickets { text-align: center; padding: 48px 24px; color: #475569; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.empty-tickets svg { width: 40px; height: 40px; }

/* Drawer */
.drawer-overlay { position: fixed; inset: 0; background: #00000080; backdrop-filter: blur(4px); z-index: 200; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 520px; height: 100vh; background: #1e293b; border-left: 1px solid #334155; display: flex; flex-direction: column; overflow: hidden; }
.drawer-header { padding: 24px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; background: #0f172a; }
.drawer-id { font-family: monospace; font-size: .8rem; color: #475569; margin-bottom: 4px; }
.drawer-title { font-size: 1.1rem; font-weight: 700; color: #f8fafc; line-height: 1.3; }
.drawer-meta { display: flex; align-items: center; gap: 10px; margin-top: 10px; flex-wrap: wrap; font-size: .78rem; color: #64748b; }
.drawer-close { background: #334155; border: none; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #94a3b8; flex-shrink: 0; transition: all .2s; }
.drawer-close:hover { background: #475569; color: #fff; }
.drawer-close svg { width: 16px; height: 16px; }
.drawer-body { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 16px; }

/* Messages */
.msg { display: flex; gap: 12px; }
.support-msg { flex-direction: row; }
.msg-avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .85rem; font-weight: 700; flex-shrink: 0; }
.msg-avatar.customer { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
.msg-avatar.support  { background: linear-gradient(135deg, #10b981, #34d399); color: #fff; }
.msg-bubble { flex: 1; }
.msg-author { font-size: .78rem; font-weight: 600; color: #94a3b8; margin-bottom: 6px; }
.msg-bubble p { background: #0f172a; border: 1px solid #334155; border-radius: 0 12px 12px 12px; padding: 12px 16px; font-size: .875rem; color: #cbd5e1; line-height: 1.6; }
.support-msg .msg-bubble p { border-radius: 12px 0 12px 12px; border-color: #10b98130; background: #10b98108; }
.msg-time { font-size: .72rem; color: #334155; margin-top: 4px; display: block; }

/* Drawer Reply */
.drawer-reply { padding: 16px; border-top: 1px solid #334155; }
.drawer-reply textarea { width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 12px; color: #e2e8f0; font-size: .875rem; font-family: inherit; outline: none; resize: none; transition: border-color .2s; }
.drawer-reply textarea:focus { border-color: #6366f1; }
.reply-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
.reply-hint { font-size: .74rem; color: #475569; }
.btn-send { display: flex; align-items: center; gap: 8px; padding: 10px 18px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-send:disabled { opacity: .4; cursor: not-allowed; }
.btn-send:not(:disabled):hover { transform: translateY(-1px); }
.btn-send svg { width: 14px; height: 14px; }
.closed-notice { padding: 16px; border-top: 1px solid #334155; display: flex; align-items: center; gap: 8px; font-size: .875rem; color: #64748b; }
.closed-notice svg { width: 16px; height: 16px; color: #10b981; }
.closed-notice a { color: #a5b4fc; text-decoration: none; }
.closed-notice a:hover { text-decoration: underline; }

/* New Ticket Modal */
.modal-overlay { position: fixed; inset: 0; background: #00000080; backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 300; padding: 20px; }
.modal { background: #1e293b; border: 1px solid #334155; border-radius: 20px; padding: 28px; width: 100%; max-width: 560px; box-shadow: 0 24px 60px rgba(0,0,0,.4); }
.modal h3 { font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 24px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
label { font-size: .8rem; font-weight: 500; color: #94a3b8; }
input, select, textarea { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #e2e8f0; font-size: .875rem; outline: none; font-family: inherit; transition: border-color .2s; width: 100%; }
input:focus, select:focus, textarea:focus { border-color: #6366f1; }

.priority-select { display: flex; gap: 8px; }
.prio-btn { flex: 1; padding: 9px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: #64748b; font-size: .83rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.prio-btn.low.active    { border-color: #10b981; color: #34d399; background: #10b98120; }
.prio-btn.normal.active { border-color: #6366f1; color: #a5b4fc; background: #6366f120; }
.prio-btn.high.active   { border-color: #ef4444; color: #f87171; background: #ef444420; }

.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.btn-modal-cancel { padding: 10px 18px; background: transparent; border: 1px solid #334155; border-radius: 8px; color: #94a3b8; font-size: .875rem; cursor: pointer; transition: all .2s; }
.btn-modal-cancel:hover { border-color: #475569; color: #e2e8f0; }
.btn-modal-submit { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-modal-submit:disabled { opacity: .4; cursor: not-allowed; }
.btn-modal-submit:not(:disabled):hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.3); }
.btn-modal-submit svg { width: 14px; height: 14px; }

/* Toast */
.toast { position: fixed; bottom: 28px; right: 28px; padding: 14px 24px; border-radius: 12px; font-size: .875rem; font-weight: 600; z-index: 999; box-shadow: 0 8px 24px rgba(0,0,0,.3); }
.toast.success { background: #10b981; color: #fff; }
.toast.info    { background: #6366f1; color: #fff; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all .3s; }
.toast-slide-enter-from, .toast-slide-leave-to { transform: translateX(100px); opacity: 0; }

/* Ticket transition */
.ticket-anim-enter-active, .ticket-anim-leave-active { transition: all .3s; }
.ticket-anim-enter-from { opacity: 0; transform: translateY(-10px); }
.ticket-anim-leave-to { opacity: 0; transform: translateX(20px); }

/* Drawer transition */
.drawer-slide-enter-active, .drawer-slide-leave-active { transition: transform .3s ease; }
.drawer-slide-enter-from, .drawer-slide-leave-to { transform: translateX(100%); }

@media (max-width: 700px) {
  .hd-header { padding: 20px 16px 0; }
  .hd-header-inner { flex-direction: column; align-items: flex-start; gap: 16px; }
  .hd-content { padding: 0 16px; }
  .filter-bar { flex-direction: column; align-items: stretch; }
  .filter-tabs { overflow-x: auto; }
  .drawer { max-width: 100%; }
}
</style>
