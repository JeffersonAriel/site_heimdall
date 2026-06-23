import './bootstrap';
import axios from 'axios';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Restore auth token on startup
const authStore = useAuthStore();
authStore.init();

// Intercept 401 responses to logout and redirect
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      authStore.erpLogout();
      authStore.logout();
      router.push('/erp/login');
    }
    return Promise.reject(error);
  }
);

// Axios base config is handled in bootstrap.js
axios.defaults.withCredentials = true;

app.mount('#app');
