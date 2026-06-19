import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    customer: JSON.parse(localStorage.getItem('heimdall_customer') || 'null'),
    token: localStorage.getItem('heimdall_token') || null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
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
      delete axios.defaults.headers.common['Authorization'];
    },

    init() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }
    },
  },
});
