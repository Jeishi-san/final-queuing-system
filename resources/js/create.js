/**
 * create.js
 * Handles ticket creation, validation, and real-time panel refreshing.
 */

// 🔧 Global configuration (injected from Blade)
const APP = window.APP || {};
const csrfToken =
    APP.csrfToken ||
    document.querySelector('meta[name="csrf-token"]')?.content ||
    '';
const refreshUrl = APP.refreshUrl || document.body.dataset.refreshUrl || '/tickets/panels';
const storeUrl = APP.storeUrl || document.body.dataset.storeUrl || '/tickets';
const checkTicketUrl = APP.checkTicketUrl || document.body.dataset.checkTicketUrl || '/tickets/check';

// Debug (remove later)
console.log('APP config loaded:', { csrfToken, refreshUrl, storeUrl, checkTicketUrl });

document.addEventListener('DOMContentLoaded', () => {
    const $ = sel => document.querySelector(sel);

    // 🔹 Elements
    const form = $('#createTicketForm');
    const feedback = $('#formFeedback');
    const toast = $('#toast');
    const submitBtn = $('#submitBtn');
    const submitText = $('#submitText');
    const submitSpinner = $('#submitSpinner');
    const manualRefreshBtn = $('#manualRefreshBtn');
    const refreshText = $('#refreshText');
    const refreshSpinner = $('#refreshSpinner');
    const panel = $('#ticketPanel');

    let autoRefreshInterval;

    // ✅ Toast Notifications
    const showToast = (msg, type = 'success') => {
        toast.textContent = msg;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded-lg shadow-lg text-white z-50 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);
    };

    // ✅ Button Loading States
    const setLoading = (loading) => {
        submitBtn.disabled = loading;
        submitText.textContent = loading ? 'Submitting...' : 'Submit Ticket';
        submitSpinner.classList.toggle('hidden', !loading);
    };

    const setRefreshLoading = (loading) => {
        manualRefreshBtn.disabled = loading;
        refreshText.textContent = loading ? 'Refreshing...' : '🔄 Refresh Ticket Panel';
        refreshSpinner.classList.toggle('hidden', !loading);
    };

    // ✅ Email Validation Helpers
    const validateEmail = (email) =>
        !email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());

    const isRealGmail = (email) =>
        email && /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email.trim());

    // ✅ Refresh Ticket Panel
    const refreshPanel = async () => {
        try {
            setRefreshLoading(true);
            const res = await fetch(refreshUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const html = await res.text();
            panel.innerHTML = html;
        } catch (error) {
            console.error('❌ Error refreshing ticket panel:', error);
            panel.innerHTML = `
                <div class="text-red-600 p-4 bg-red-50 rounded">
                    Failed to load tickets. Please try again later.
                </div>`;
        } finally {
            setRefreshLoading(false);
        }
    };

    // ✅ Check for Existing Ticket
    const checkExistingTicket = async (ticketNumber) => {
        if (!ticketNumber) return false;

        try {
            const res = await fetch(
                `${checkTicketUrl}?ticket_number=${encodeURIComponent(ticketNumber)}`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
            );
            const data = await res.json();
            return data.exists === true;
        } catch (err) {
            console.warn('⚠️ Could not check existing ticket:', err);
            return false;
        }
    };

    // ✅ Handle Form Submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        setLoading(true);
        feedback.classList.add('hidden');

        const fd = new FormData(form);
        const agentEmail = fd.get('agent_email');
        const teamLeaderEmail = fd.get('team_leader_email');
        const ticketNumber = fd.get('ticket_number')?.trim();

        // 🔸 Validate Emails
        const errMsg =
            !agentEmail
                ? 'Agent email is required'
                : !validateEmail(agentEmail)
                ? 'Invalid agent email format'
                : teamLeaderEmail && !validateEmail(teamLeaderEmail)
                ? 'Invalid team leader email format'
                : null;

        if (errMsg) {
            feedback.innerHTML = `<div class="p-3 bg-red-100 text-red-700 rounded">${errMsg}</div>`;
            feedback.classList.remove('hidden');
            showToast('❌ Fix validation errors', 'error');
            setLoading(false);
            return;
        }

        // 🔸 Real Gmail Flag
        if (isRealGmail(agentEmail)) fd.append('real_gmail_agent', '1');
        if (teamLeaderEmail && isRealGmail(teamLeaderEmail)) fd.append('real_gmail_leader', '1');

        // 🔸 Prevent Duplicate Ticket
        if (ticketNumber) {
            const exists = await checkExistingTicket(ticketNumber);
            if (exists) {
                feedback.className = 'p-4 rounded bg-yellow-100 text-yellow-700';
                feedback.innerHTML = `⚠️ Ticket number <strong>${ticketNumber}</strong> already exists.`;
                feedback.classList.remove('hidden');
                showToast('⚠️ Ticket already exists', 'error');
                setLoading(false);
                return;
            }
        }

        // 🔸 Submit Ticket
        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: fd,
            });

            const data = await res.json();

            if (res.status === 422) {
                const messages = Object.values(data.errors || {}).flat().join('<br>');
                throw new Error(messages || 'Validation failed.');
            }

            if (!res.ok || !data.success)
                throw new Error(data.message || 'Submission failed.');

            showToast('✅ Ticket submitted successfully!');
            await refreshPanel();
            form.reset();

            feedback.className = 'p-4 rounded bg-green-100 text-green-700';
            feedback.innerHTML = `✅ Ticket #${data.ticket?.ticket_number ?? '(auto-generated)'} created successfully.`;
            feedback.classList.remove('hidden');
        } catch (err) {
            console.error('❌ Ticket submission error:', err);
            showToast('❌ Submission failed', 'error');
            feedback.className = 'p-4 rounded bg-red-100 text-red-700';
            feedback.innerHTML = err.message ?? 'Submission failed. Try again.';
            feedback.classList.remove('hidden');
        } finally {
            setLoading(false);
        }
    });

    // ✅ Manual Refresh
    manualRefreshBtn.addEventListener('click', async () => {
        try {
            await refreshPanel();
            showToast('✅ Panel refreshed!');
        } catch {
            showToast('❌ Failed to refresh', 'error');
        }
    });

    // ✅ Auto Refresh (every 30s if visible)
    const startAutoRefresh = () => {
        autoRefreshInterval = setInterval(() => {
            if (!document.hidden) refreshPanel().catch(console.warn);
        }, 30000);
    };

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(autoRefreshInterval);
        } else {
            startAutoRefresh();
        }
    });

    startAutoRefresh();
});
