import axios from 'axios';

window.axios = axios;

// Use Vite's BASE_URL so axios works both in root and in subpath deployments (e.g. /~jeff2892/)
const base = import.meta.env.BASE_URL.replace(/\/$/, '');
window.axios.defaults.baseURL = base;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
