<template>
  <div class="erp-login-container">
    <div class="glass-login-card">
      <div class="brand-header">
        <div class="brand-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
          </svg>
        </div>
        <h1 class="brand-title">Heimdall <span class="accent-text">ERP</span></h1>
        <p class="brand-subtitle">Portal Administrativo Corporativo</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="input-group">
          <label for="email">E-mail Corporativo</label>
          <div class="input-wrapper">
            <span class="icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </span>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              placeholder="admin@heimdall.com"
              autocomplete="email"
            />
          </div>
        </div>

        <div class="input-group">
          <label for="password">Senha de Acesso</label>
          <div class="input-wrapper">
            <span class="icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              autocomplete="current-password"
            />
          </div>
        </div>

        <transition name="shake">
          <div v-if="error" class="error-alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ error }}</span>
          </div>
        </transition>

        <button type="submit" :disabled="loading" class="btn-submit">
          <span v-if="loading" class="spinner"></span>
          <span v-else>Entrar no Painel</span>
        </button>
      </form>

      <div class="card-footer">
        <router-link to="/" class="back-link">← Ir para a Loja Pública</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const router = useRouter();

const form = ref({ email: '', password: '' });
const loading = ref(false);
const error = ref(null);

async function handleLogin() {
  loading.value = true;
  error.value = null;
  try {
    await authStore.erpLogin(form.value.email, form.value.password);
    router.push('/erp');
  } catch (e) {
    error.value = e.response?.data?.message || 'Falha na autenticação. Verifique suas credenciais.';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.erp-login-container {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at top right, #1e1b4b 0%, #0f172a 100%);
  padding: 20px;
}

.glass-login-card {
  width: 100%;
  max-width: 440px;
  background: rgba(30, 41, 59, 0.7);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.brand-header {
  text-align: center;
  margin-bottom: 32px;
}

.brand-icon {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
}

.brand-icon svg {
  width: 28px;
  height: 28px;
  stroke: #ffffff;
}

.brand-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #f8fafc;
  margin-bottom: 4px;
}

.accent-text {
  color: #818cf8;
}

.brand-subtitle {
  font-size: 0.875rem;
  color: #94a3b8;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 0.815rem;
  font-weight: 500;
  color: #cbd5e1;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper .icon {
  position: absolute;
  left: 14px;
  color: #64748b;
  display: flex;
  align-items: center;
}

.input-wrapper .icon svg {
  width: 18px;
  height: 18px;
}

.input-wrapper input {
  width: 100%;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 12px 16px 12px 42px;
  color: #f8fafc;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.25s;
}

.input-wrapper input:focus {
  border-color: #6366f1;
  background: rgba(15, 23, 42, 0.8);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.error-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 12px;
  padding: 12px;
  color: #fca5a5;
  font-size: 0.85rem;
}

.error-alert svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.btn-submit {
  background: linear-gradient(90deg, #6366f1, #4f46e5);
  color: #ffffff;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #ffffff;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.card-footer {
  margin-top: 24px;
  text-align: center;
}

.back-link {
  font-size: 0.85rem;
  color: #94a3b8;
  text-decoration: none;
  transition: color 0.2s;
}

.back-link:hover {
  color: #cbd5e1;
}

/* Shake transition for error alerts */
.shake-enter-active {
  animation: shake 0.4s ease;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-6px); }
  75% { transform: translateX(6px); }
}
</style>
