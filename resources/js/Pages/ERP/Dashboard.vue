<template>
  <div class="erp-home">
    <!-- Top Bar -->
    <header class="top-bar">
      <div class="top-bar-inner">
        <div class="brand">
          <div class="brand-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
            </svg>
          </div>
          <span class="brand-name">Heimdall <span class="brand-suffix">ERP</span></span>
        </div>
        <nav class="top-nav">
          <router-link to="/" class="nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Loja
          </router-link>

          <div class="user-profile-menu" v-if="authStore.erpUser">
            <span class="user-name">{{ authStore.erpUser.name }} ({{ authStore.erpUser.role }})</span>
            <button @click="handleLogout" class="btn-logout" title="Sair do ERP">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
          </div>

          <div class="notif-btn" @click="showNotifs = !showNotifs">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="notif-dot">{{ notifications.filter(n => !n.read).length }}</span>
          </div>
        </nav>
      </div>

      <!-- Notification Dropdown -->
      <transition name="fade">
        <div v-if="showNotifs" class="notif-dropdown" @click.self="showNotifs = false">
          <div class="notif-panel">
            <div class="notif-header">
              <span>Notificações</span>
              <button @click="markAllRead">Marcar todas como lidas</button>
            </div>
            <div class="notif-list">
              <div v-for="n in notifications" :key="n.id" class="notif-item" :class="{ unread: !n.read }" @click="n.read = true">
                <span class="notif-type" :class="n.type"></span>
                <div>
                  <p class="notif-title">{{ n.title }}</p>
                  <small class="notif-time">{{ n.time }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </header>

    <!-- Welcome Section -->
    <section class="welcome-section">
      <div class="welcome-inner">
        <div class="welcome-text">
          <h1>Painel de Controle</h1>
          <p>Bem-vindo ao Heimdall ERP. Todos os módulos do sistema em um só lugar.</p>
        </div>
        <div class="welcome-stats">
          <div class="ws-stat" v-for="s in welcomeStats" :key="s.label">
            <span class="ws-val" :style="{ color: s.color }">{{ s.value }}</span>
            <span class="ws-label">{{ s.label }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Modules Grid -->
    <main class="modules-section">
      <div class="section-label">Módulos Principais</div>
      <div class="modules-grid">
        <router-link
          v-for="mod in modules"
          :key="mod.name"
          :to="mod.path"
          class="mod-card"
          :style="{ '--accent': mod.accent, '--accent2': mod.accent2 }"
        >
          <div class="mod-icon-wrap">
            <div class="mod-icon" v-html="mod.icon"></div>
          </div>
          <div class="mod-body">
            <h2 class="mod-name">{{ mod.name }}</h2>
            <p class="mod-desc">{{ mod.desc }}</p>
          </div>
          <div class="mod-footer">
            <span class="mod-stat" v-if="mod.stat">{{ mod.stat }}</span>
            <span class="mod-alert" v-if="mod.alert">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 20h20L12 2zm0 3.5L19.5 20h-15L12 5.5zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z"/></svg>
              {{ mod.alert }}
            </span>
            <div class="mod-arrow">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Quick Actions -->
      <div class="section-label mt-sec">Ações Rápidas</div>
      <div class="quick-actions">
        <button v-for="qa in quickActions" :key="qa.label" class="qa-btn" @click="$router.push(qa.path)">
          <span v-html="qa.icon"></span>
          {{ qa.label }}
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const router = useRouter();

function handleLogout() {
  authStore.erpLogout();
  router.push('/erp/login');
}

/* ─── Notifications ─── */
const showNotifs = ref(false);
const notifications = ref([
  { id:1, title:'⚠️ Estoque crítico: Cabo HDMI 2.1 2m (8 un)', type:'warning', time:'Há 5 min', read: false },
  { id:2, title:'🛒 Novo pedido #9905 recebido — R$ 2.480,00', type:'info',    time:'Há 12 min', read: false },
  { id:3, title:'🎫 Novo ticket aberto: "Nota fiscal não recebida"', type:'info', time:'Há 30 min', read: false },
  { id:4, title:'✅ NF-e #4410 emitida com sucesso', type:'success', time:'Há 1h', read: true },
  { id:5, title:'💰 Conta a pagar vencendo em 2 dias: Fornecedor ABC', type:'warning', time:'Há 2h', read: true },
]);
const markAllRead = () => notifications.value.forEach(n => n.read = true);

/* ─── Welcome Stats ─── */
const welcomeStats = ref([
  { label: 'Pedidos Hoje',       value: '34',       color: '#6366f1' },
  { label: 'Faturamento Mês',    value: 'R$ 142K',  color: '#10b981' },
  { label: 'Tickets Abertos',    value: '2',         color: '#f59e0b' },
  { label: 'Alertas de Estoque', value: '12',        color: '#ef4444' },
]);

/* ─── Modules ─── */
const modules = ref([
  {
    name: 'CRM & Leads',
    desc: 'Funil Kanban, gestão de clientes e oportunidades de vendas.',
    path: '/erp/crm',
    accent: '#6366f1', accent2: '#8b5cf6',
    stat: '3 leads quentes',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
  },
  {
    name: 'BI & Métricas',
    desc: 'Dashboards de faturamento, KPIs e relatórios analíticos em tempo real.',
    path: '/erp/bi',
    accent: '#0ea5e9', accent2: '#38bdf8',
    stat: 'Atualizado há 5min',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`,
  },
  {
    name: 'Financeiro',
    desc: 'Contas a pagar/receber, fluxo de caixa e DRE consolidado.',
    path: '/erp/financeiro',
    accent: '#10b981', accent2: '#34d399',
    alert: '1 vencendo hoje',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`,
  },
  {
    name: 'Estoque',
    desc: 'Depósitos, lotes, curva ABC e alertas de nível mínimo.',
    path: '/erp/estoque',
    accent: '#f59e0b', accent2: '#fbbf24',
    alert: '12 críticos',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>`,
  },
  {
    name: 'Produção',
    desc: 'Fichas técnicas, ordens de produção e controle de BOM.',
    path: '/erp/producao',
    accent: '#ec4899', accent2: '#f472b6',
    stat: '2 ordens abertas',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>`,
  },
  {
    name: 'Suporte Interno',
    desc: 'HelpDesk interno para atendimento de chamados de clientes.',
    path: '/erp/suporte',
    accent: '#a855f7', accent2: '#c084fc',
    alert: '2 aguardando',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
  },
  {
    name: 'Fiscal / NF-e',
    desc: 'Emissão, consulta e cancelamento de Notas Fiscais Eletrônicas.',
    path: '/erp/fiscal',
    accent: '#14b8a6', accent2: '#2dd4bf',
    stat: '48 NFs emitidas',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
  },
  {
    name: 'Core AI',
    desc: 'Auditoria inteligente, detecção de anomalias e recomendações automatizadas.',
    path: '/erp/ai',
    accent: '#6366f1', accent2: '#4f46e5',
    stat: '3 anomalias detectadas',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
  },
]);

/* ─── Quick Actions ─── */
const quickActions = ref([
  { label: 'Novo Pedido', path: '/erp/crm', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>` },
  { label: 'Emitir NF-e', path: '/erp/fiscal', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>` },
  { label: 'Ver Estoque',  path: '/erp/estoque', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>` },
  { label: 'Fluxo de Caixa', path: '/erp/financeiro', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>` },
  { label: 'Suporte',    path: '/erp/suporte', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>` },
  { label: 'Relatórios', path: '/erp/bi', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>` },
]);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.erp-home {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  background: #0f172a;
  color: #e2e8f0;
}

/* ─── Top Bar ─── */
.top-bar {
  background: #0f172a;
  border-bottom: 1px solid #1e293b;
  position: sticky; top: 0; z-index: 50;
  backdrop-filter: blur(12px);
}
.top-bar-inner { max-width: 1400px; margin: 0 auto; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; }
.brand { display: flex; align-items: center; gap: 12px; }
.brand-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex; align-items: center; justify-content: center;
}
.brand-icon svg { width: 20px; height: 20px; stroke: #fff; }
.brand-name { font-size: 1.1rem; font-weight: 700; color: #f8fafc; }
.brand-suffix { color: #818cf8; }
.top-nav { display: flex; align-items: center; gap: 12px; }
.nav-link { display: flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; background: #1e293b; border: 1px solid #334155; color: #94a3b8; font-size: .85rem; text-decoration: none; transition: all .2s; }
.nav-link svg { width: 15px; height: 15px; }
.nav-link:hover { color: #e2e8f0; border-color: #475569; }
.user-profile-menu { display: flex; align-items: center; gap: 10px; background: #1e293b; border: 1px solid #334155; padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; }
.user-name { color: #cbd5e1; font-weight: 500; }
.btn-logout { background: none; border: none; color: #f87171; cursor: pointer; display: flex; align-items: center; transition: color 0.2s; padding: 2px; }
.btn-logout:hover { color: #f87171; opacity: 0.8; }
.btn-logout svg { width: 16px; height: 16px; }
.notif-btn { position: relative; width: 38px; height: 38px; border-radius: 10px; background: #1e293b; border: 1px solid #334155; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .2s; }
.notif-btn svg { width: 18px; height: 18px; color: #94a3b8; }
.notif-btn:hover { border-color: #6366f1; }
.notif-dot { position: absolute; top: 5px; right: 5px; width: 16px; height: 16px; background: #ef4444; border-radius: 50%; font-size: .6rem; font-weight: 700; color: #fff; display: flex; align-items: center; justify-content: center; border: 2px solid #0f172a; }

/* Notification dropdown */
.notif-dropdown { position: absolute; top: 70px; right: 32px; z-index: 200; }
.notif-panel { background: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 340px; box-shadow: 0 20px 50px rgba(0,0,0,.4); overflow: hidden; }
.notif-header { padding: 14px 18px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; font-size: .875rem; font-weight: 600; color: #f8fafc; }
.notif-header button { background: none; border: none; color: #6366f1; font-size: .78rem; cursor: pointer; }
.notif-list { max-height: 340px; overflow-y: auto; }
.notif-item { display: flex; align-items: flex-start; gap: 10px; padding: 12px 18px; cursor: pointer; transition: background .15s; border-bottom: 1px solid #1e293b; }
.notif-item:hover { background: #0f172a30; }
.notif-item.unread { background: #6366f108; }
.notif-type { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
.notif-type.info    { background: #6366f1; }
.notif-type.warning { background: #f59e0b; }
.notif-type.success { background: #10b981; }
.notif-title { font-size: .83rem; color: #e2e8f0; line-height: 1.4; }
.notif-time { font-size: .73rem; color: #475569; }

/* ─── Welcome ─── */
.welcome-section {
  background: linear-gradient(135deg, #1a103c 0%, #0f172a 60%, #0f1f2c 100%);
  border-bottom: 1px solid #1e293b;
  padding: 40px 32px;
}
.welcome-inner { max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
.welcome-text h1 { font-size: 2rem; font-weight: 800; color: #f8fafc; }
.welcome-text p { font-size: .9rem; color: #64748b; margin-top: 6px; }
.welcome-stats { display: flex; gap: 28px; flex-wrap: wrap; }
.ws-stat { display: flex; flex-direction: column; align-items: center; }
.ws-val { font-size: 1.6rem; font-weight: 700; }
.ws-label { font-size: .75rem; color: #475569; margin-top: 2px; }

/* ─── Modules ─── */
.modules-section { max-width: 1400px; margin: 0 auto; padding: 32px; }
.section-label { font-size: .78rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 16px; }
.mt-sec { margin-top: 36px; }

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 18px;
}

.mod-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 18px;
  padding: 22px;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  gap: 14px;
  position: relative;
  overflow: hidden;
  transition: all .25s;
}
.mod-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--accent), var(--accent2));
  opacity: 0;
  transition: opacity .25s;
}
.mod-card:hover {
  border-color: var(--accent);
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0,0,0,.25);
}
.mod-card:hover::before { opacity: 1; }

.mod-icon-wrap { display: flex; align-items: center; }
.mod-icon {
  width: 44px; height: 44px;
  background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  opacity: .9;
}
.mod-icon :deep(svg) { width: 22px; height: 22px; stroke: #fff; }

.mod-body { flex: 1; }
.mod-name { font-size: 1rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px; }
.mod-desc { font-size: .815rem; color: #64748b; line-height: 1.5; }

.mod-footer { display: flex; align-items: center; gap: 8px; }
.mod-stat { font-size: .775rem; color: #94a3b8; background: #0f172a; padding: 3px 10px; border-radius: 20px; border: 1px solid #334155; }
.mod-alert { display: flex; align-items: center; gap: 4px; font-size: .775rem; color: #f87171; background: #ef444415; padding: 3px 10px; border-radius: 20px; border: 1px solid #ef444430; font-weight: 600; }
.mod-alert svg { width: 11px; height: 11px; }
.mod-arrow { margin-left: auto; color: #334155; transition: all .25s; }
.mod-arrow svg { width: 16px; height: 16px; }
.mod-card:hover .mod-arrow { color: var(--accent); transform: translateX(3px); }

/* ─── Quick Actions ─── */
.quick-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.qa-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 18px; border-radius: 10px;
  background: #1e293b; border: 1px solid #334155;
  color: #94a3b8; font-size: .85rem; font-weight: 500; cursor: pointer;
  transition: all .2s;
}
.qa-btn :deep(svg) { width: 15px; height: 15px; stroke: currentColor; }
.qa-btn:hover { background: #6366f1; border-color: #6366f1; color: #fff; transform: translateY(-1px); }

/* ─── Transitions ─── */
.fade-enter-active, .fade-leave-active { transition: opacity .2s, transform .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }

@media (max-width: 768px) {
  .welcome-inner { flex-direction: column; }
  .modules-grid { grid-template-columns: 1fr; }
  .welcome-section { padding: 24px 16px; }
  .modules-section { padding: 20px 16px; }
  .top-bar-inner { padding: 14px 16px; }
  .brand-name { font-size: .95rem; }
}
</style>
