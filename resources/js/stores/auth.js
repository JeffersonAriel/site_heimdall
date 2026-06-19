import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    customer: JSON.parse(localStorage.getItem('heimdall_customer') || 'null'),
    token: localStorage.getItem('heimdall_token') || null,
    erpUser: JSON.parse(localStorage.getItem('heimdall_erp_user') || 'null'),
    erpToken: localStorage.getItem('heimdall_erp_token') || null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isErpLoggedIn: (state) => !!state.erpToken,
  },

  actions: {
    async login(email, password) {
      const { data } = await axios.post('/api/v1/auth/customer/login', { email, password });
      this.token = data.token;
      this.customer = data.customer;
      localStorage.setItem('heimdall_token', this.token);
      localStorage.setItem('heimdall_customer', JSON.stringify(this.customer));
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
    },

    logout() {
      axios.post('/api/v1/customer/logout').catch(() => {});
      this.token = null;
      this.customer = null;
      localStorage.removeItem('heimdall_token');
      localStorage.removeItem('heimdall_customer');
      if (this.erpToken) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.erpToken}`;
      } else {
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    async erpLogin(email, password) {
      const { data } = await axios.post('/api/v1/auth/erp/login', { email, password });
      this.erpToken = data.token;
      this.erpUser = data.user;
      localStorage.setItem('heimdall_erp_token', this.erpToken);
      localStorage.setItem('heimdall_erp_user', JSON.stringify(this.erpUser));
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.erpToken}`;
    },

    erpLogout() {
      axios.post('/api/v1/erp/logout').catch(() => {});
      this.erpToken = null;
      this.erpUser = null;
      localStorage.removeItem('heimdall_erp_token');
      localStorage.removeItem('heimdall_erp_user');
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      } else {
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    init() {
      const isErpPath = window.location.pathname.includes('/erp');
      if (isErpPath && this.erpToken) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.erpToken}`;
      } else if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }
    },

    setAuthForPath(path) {
      if (path.includes('/erp')) {
        if (this.erpToken) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.erpToken}`;
        } else {
          delete axios.defaults.headers.common['Authorization'];
        }
      } else {
        if (this.token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        } else {
          delete axios.defaults.headers.common['Authorization'];
        }
      }
    }
  },
});
