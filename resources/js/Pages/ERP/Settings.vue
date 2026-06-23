<template>
  <div class="settings-page">
    <!-- Top Bar -->
    <header class="top-bar">
      <div class="top-bar-inner">
        <div class="brand" @click="$router.push('/erp')" style="cursor: pointer;">
          <div class="brand-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
            </svg>
          </div>
          <span class="brand-name">Heimdall <span class="brand-suffix">ERP</span></span>
        </div>
        <nav class="top-nav">
          <router-link to="/erp" class="nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Voltar ao Painel
          </router-link>
        </nav>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="settings-container">
      <div class="header-section">
        <h1>Configurações de Usuários e Permissões</h1>
        <p>Cadastre novos operadores, vendedores e gerentes, e visualize a matriz de privilégios de acesso do sistema.</p>
      </div>

      <div class="settings-grid">
        <!-- Users List Card -->
        <section class="card users-card">
          <div class="card-header">
            <h2>👥 Usuários Administrativos</h2>
            <button @click="openCreateModal" class="btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Adicionar Usuário
            </button>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>E-mail</th>
                  <th>Cargo / Perfil</th>
                  <th class="actions-column">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td class="font-bold">{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>
                    <span class="badge" :class="user.role">
                      {{ getRoleLabel(user.role) }}
                    </span>
                  </td>
                  <td class="actions">
                    <button @click="openEditModal(user)" class="btn-icon edit" title="Editar Usuário">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button @click="deleteUser(user.id)" class="btn-icon delete" title="Excluir Usuário" :disabled="authStore.erpUser && authStore.erpUser.id === user.id">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Permissions Policy Matrix -->
        <section class="card matrix-card">
          <div class="card-header">
            <h2>🛡️ Matriz de Permissões (Políticas de Acesso)</h2>
          </div>
          <div class="matrix-info">
            <p>Selecione um cargo/perfil abaixo para visualizar a quais ações e módulos do ERP/CRM ele possui permissão concedida:</p>
          </div>

          <div class="role-selector">
            <button 
              v-for="(label, role) in roleLabels" 
              :key="role" 
              class="role-tab"
              :class="{ active: selectedRole === role }"
              @click="selectedRole = role"
            >
              {{ label }}
            </button>
          </div>

          <div class="permissions-list">
            <div v-for="(perms, moduleName) in permissionsData.modules" :key="moduleName" class="module-group">
              <h3>{{ moduleName }}</h3>
              <div class="perms-grid">
                <div v-for="perm in perms" :key="perm.key" class="perm-item" :class="{ enabled: hasPermission(selectedRole, perm.key) }">
                  <div class="status-indicator">
                    <svg v-if="hasPermission(selectedRole, perm.key)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </div>
                  <span class="perm-label">{{ perm.label }}</span>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ isEditMode ? 'Editar Usuário' : 'Novo Usuário ERP' }}</h3>
          <button @click="closeModal" class="btn-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <form @submit.prevent="saveUser" class="modal-form">
          <div class="form-group">
            <label for="userName">Nome Completo</label>
            <input id="userName" v-model="form.name" type="text" required placeholder="Ex: Carlos Eduardo" />
          </div>

          <div class="form-group">
            <label for="userEmail">E-mail</label>
            <input id="userEmail" v-model="form.email" type="email" required placeholder="Ex: carlos@heimdall.com" />
          </div>

          <div class="form-group">
            <label for="userPassword">Senha {{ isEditMode ? '(Deixe em branco para não alterar)' : '' }}</label>
            <input id="userPassword" v-model="form.password" type="password" :required="!isEditMode" placeholder="Senha com mínimo de 6 caracteres" />
          </div>

          <div class="form-group">
            <label for="userRole">Cargo / Perfil</label>
            <select id="userRole" v-model="form.role" required>
              <option v-for="(label, role) in roleLabels" :key="role" :value="role">
                {{ label }}
              </option>
            </select>
          </div>

          <div v-if="modalError" class="modal-error">
            {{ modalError }}
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Gravando...' : 'Salvar Alterações' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const users = ref([]);
const permissionsData = ref({ roles: {}, modules: {} });
const selectedRole = ref('admin');

const roleLabels = {
  admin: 'Super Admin',
  manager: 'Gerente Geral',
  operator: 'Operador de Estoque',
  seller: 'Vendedor / CRM',
  financial: 'Financeiro',
  support: 'Atendente de Suporte'
};

function getRoleLabel(role) {
  return roleLabels[role] || role;
}

// Modal management
const showModal = ref(false);
const isEditMode = ref(false);
const saving = ref(false);
const modalError = ref(null);
const editUserId = ref(null);

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'operator'
});

async function loadData() {
  try {
    const [usersRes, permRes] = await Promise.all([
      axios.get('/api/v1/erp/users'),
      axios.get('/api/v1/erp/permissions')
    ]);
    users.value = usersRes.data;
    permissionsData.value = permRes.data;
  } catch (error) {
    console.error("Erro ao carregar dados do painel:", error);
  }
}

function hasPermission(role, key) {
  const rolePermissions = permissionsData.value.roles[role] || [];
  return rolePermissions.includes(key);
}

function openCreateModal() {
  isEditMode.value = false;
  modalError.value = null;
  form.value = { name: '', email: '', password: '', role: 'operator' };
  showModal.value = true;
}

function openEditModal(user) {
  isEditMode.value = true;
  modalError.value = null;
  editUserId.value = user.id;
  form.value = { name: user.name, email: user.email, password: '', role: user.role };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function saveUser() {
  saving.value = true;
  modalError.value = null;
  try {
    if (isEditMode.value) {
      await axios.put(`/api/v1/erp/users/${editUserId.value}`, form.value);
    } else {
      await axios.post('/api/v1/erp/users', form.value);
    }
    await loadData();
    showModal.value = false;
  } catch (e) {
    modalError.value = e.response?.data?.message || 'Falha ao salvar usuário.';
  } finally {
    saving.value = false;
  }
}

async function deleteUser(id) {
  if (!confirm('Deseja realmente remover este usuário administrativo?')) return;
  try {
    await axios.delete(`/api/v1/erp/users/${id}`);
    await loadData();
  } catch (e) {
    alert(e.response?.data?.message || 'Erro ao remover usuário.');
  }
}

onMounted(() => {
  loadData();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

.settings-page {
  font-family: 'Outfit', sans-serif;
  min-height: 100vh;
  background: #090d16;
  color: #cbd5e1;
}

/* ─── Top Bar ─── */
.top-bar {
  background: rgba(15, 23, 42, 0.6);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
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

.nav-link {
  display: flex; align-items: center; gap: 8px;
  color: #94a3b8; font-size: 0.9rem; font-weight: 500; text-decoration: none;
  padding: 8px 16px; border-radius: 10px; transition: all 0.2s;
  border: 1px solid rgba(255, 255, 255, 0.05);
  background: rgba(255, 255, 255, 0.02);
}
.nav-link:hover { color: #fff; background: rgba(99, 102, 241, 0.1); border-color: rgba(99, 102, 241, 0.2); }
.nav-link svg { width: 16px; height: 16px; }

/* ─── Layout Container ─── */
.settings-container { max-width: 1400px; margin: 0 auto; padding: 40px 32px; }

.header-section { margin-bottom: 32px; }
.header-section h1 { font-size: 1.8rem; font-weight: 700; color: #f8fafc; margin-bottom: 8px; }
.header-section p { font-size: 1rem; color: #64748b; }

.settings-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 32px; }

@media (max-width: 1024px) {
  .settings-grid { grid-template-columns: 1fr; }
}

/* ─── Cards Design ─── */
.card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  padding: 24px;
  backdrop-filter: blur(8px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.card-header h2 { font-size: 1.25rem; font-weight: 600; color: #f8fafc; }

/* ─── Table Design ─── */
.table-wrapper { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; text-align: left; }
th { padding: 14px 16px; font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
td { padding: 16px; font-size: 0.9rem; color: #cbd5e1; border-bottom: 1px solid rgba(255, 255, 255, 0.03); }
.font-bold { font-weight: 600; color: #f8fafc; }

/* ─── Badges ─── */
.badge {
  display: inline-block; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600;
  text-transform: capitalize;
}
.badge.admin { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }
.badge.manager { background: rgba(139, 92, 246, 0.15); color: #d8b4fe; }
.badge.operator { background: rgba(245, 158, 11, 0.15); color: #fde047; }
.badge.seller { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; }
.badge.financial { background: rgba(14, 165, 233, 0.15); color: #7dd3fc; }
.badge.support { background: rgba(236, 72, 153, 0.15); color: #fbcfe8; }

/* ─── Buttons ─── */
.btn-primary {
  background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff;
  border: none; border-radius: 12px; padding: 10px 18px;
  font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
  display: flex; align-items: center; gap: 8px;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35); }
.btn-primary svg { width: 14px; height: 14px; }

.btn-secondary {
  background: rgba(255, 255, 255, 0.05); color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 10px 18px;
  font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }

.actions-column { width: 100px; text-align: center; }
.actions { display: flex; gap: 8px; justify-content: center; }
.btn-icon {
  width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.03);
  color: #64748b; transition: all 0.2s;
}
.btn-icon svg { width: 15px; height: 15px; }
.btn-icon.edit:hover { background: rgba(99, 102, 241, 0.15); color: #818cf8; }
.btn-icon.delete:hover:not(:disabled) { background: rgba(239, 68, 68, 0.15); color: #f87171; }
.btn-icon:disabled { opacity: 0.3; cursor: not-allowed; }

/* ─── Matrix Policy Design ─── */
.matrix-info { font-size: 0.9rem; color: #64748b; margin-bottom: 20px; }
.role-selector { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
.role-tab {
  background: rgba(255, 255, 255, 0.02); color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 10px;
  padding: 8px 14px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: all 0.2s;
}
.role-tab:hover { background: rgba(255, 255, 255, 0.06); color: #fff; }
.role-tab.active { background: rgba(99, 102, 241, 0.15); color: #818cf8; border-color: rgba(99, 102, 241, 0.3); }

.permissions-list { display: flex; flex-direction: column; gap: 24px; }
.module-group h3 { font-size: 0.95rem; font-weight: 600; color: #818cf8; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
.perms-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

@media (max-width: 640px) {
  .perms-grid { grid-template-columns: 1fr; }
}

.perm-item {
  display: flex; align-items: center; gap: 12px; padding: 10px 14px;
  background: rgba(255, 255, 255, 0.01); border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 10px; transition: all 0.2s; opacity: 0.4;
}
.perm-item.enabled { opacity: 1; background: rgba(16, 185, 129, 0.03); border-color: rgba(16, 185, 129, 0.15); }
.perm-label { font-size: 0.85rem; color: #cbd5e1; }
.perm-item.enabled .perm-label { color: #f8fafc; font-weight: 500; }

.status-indicator {
  width: 18px; height: 18px; border-radius: 5px;
  display: flex; align-items: center; justify-content: center;
  background: rgba(255, 255, 255, 0.03); color: #64748b;
}
.perm-item.enabled .status-indicator { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.status-indicator svg { width: 11px; height: 11px; }

/* ─── Modal Overlay ─── */
.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(9, 13, 22, 0.8); z-index: 100;
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(10px);
}
.modal-card {
  width: 100%; max-width: 480px; background: #111827;
  border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px;
  padding: 32px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
}
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.modal-header h3 { font-size: 1.25rem; font-weight: 600; color: #f8fafc; }
.btn-close {
  background: none; border: none; color: #64748b; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}
.btn-close svg { width: 20px; height: 20px; }

.modal-form { display: flex; flex-direction: column; gap: 18px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 0.85rem; font-weight: 500; color: #cbd5e1; }
.form-group input, .form-group select {
  background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px; padding: 12px 16px; color: #f8fafc; font-size: 0.9rem; outline: none;
  transition: all 0.2s; font-family: inherit;
}
.form-group input:focus, .form-group select:focus {
  border-color: #6366f1; background: rgba(15, 23, 42, 0.8);
}

.modal-error {
  padding: 10px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 10px; color: #fca5a5; font-size: 0.8rem; text-align: center;
}

.modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px; }
</style>
