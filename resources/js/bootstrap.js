import axios from 'axios';
window.axios = axios;

// Default header so Laravel knows it's AJAX
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Allow cookies/session to be sent
window.axios.defaults.withCredentials = true;
