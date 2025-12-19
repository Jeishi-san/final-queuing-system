import axios from 'axios';
window.axios = axios;

// Default header so Laravel knows it's AJAX
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Allow cookies/session to be sent
window.axios.defaults.withCredentials = true;

// CSRF token for web routes (meta tag provided by Blade)
try {
    const tokenEl = document.querySelector('meta[name="csrf-token"]');
    const token = tokenEl ? tokenEl.getAttribute('content') : null;
    if (token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
} catch (e) {
    console.warn('CSRF token meta not found');
}

// --- Global Super Admin check ---
window.isSuperAdmin = false;

window.checkSuperAdmin = async function () {
    try {
        const response = await window.axios.get('/check-super-admin');
        window.isSuperAdmin = response.data.is_super_admin;
        return window.isSuperAdmin;
    } catch (error) {
        console.error('Failed to check super admin', error);
        return false;
    }
};
