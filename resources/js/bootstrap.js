import axios from 'axios';

window.axios = axios;

// Detect dynamic base path depending on deployment (e.g. subpath /~jeff2892)
const base = window.location.pathname.startsWith('/~jeff2892') ? '/~jeff2892' : '';
window.axios.defaults.baseURL = base;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
