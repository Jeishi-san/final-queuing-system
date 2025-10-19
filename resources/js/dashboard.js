/**
 * Dashboard JavaScript Module (Production-Ready)
 * Handles dynamic updates, modals, and delegated form submission.
 */

class DashboardManager {
    constructor() {
        // Core Elements - UPDATED to match your blade
        this.modal = document.getElementById('assignModal');
        this.modalClose = document.getElementById('assignModalClose');
        this.modalContainer = document.getElementById('assignFormContainer');
        this.toast = document.getElementById('toast');
        this.tableContainer = document.getElementById('ticketTableContainer');
        this.statsPanel = document.getElementById('statsPanel');
        this.filters = document.getElementById('ticketFilters');
        this.extraFilters = document.getElementById('extraFilters');
        this.refreshBtn = document.getElementById('refreshDashboardBtn');
        this.applyFiltersBtn = document.getElementById('applyFiltersBtn');
        this.clearFiltersBtn = document.getElementById('clearFiltersBtn');

        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Modal close - UPDATED for your modal structure
        this.modalClose?.addEventListener('click', () => this.closeModal());
        this.modal?.addEventListener('click', e => {
            if (e.target === this.modal) this.closeModal();
        });

        // Refresh button
        this.refreshBtn?.addEventListener('click', () => this.refreshDashboard());

        // Filters submit
        this.filters?.addEventListener('submit', e => {
            e.preventDefault();
            this.refreshDashboard();
        });

        this.extraFilters?.addEventListener('submit', e => {
            e.preventDefault();
            this.refreshDashboard();
        });

        // Clear filters
        this.clearFiltersBtn?.addEventListener('click', e => {
            e.preventDefault();
            window.location.href = this.clearFiltersBtn.getAttribute('href');
        });

        // Auto refresh on dropdown changes - UPDATED for your filter IDs
        ['statusFilter', 'itPersonnelFilter'].forEach(id => {
            const el = document.getElementById(id);
            el?.addEventListener('change', () => {
                if (!document.activeElement.isEqualNode(this.applyFiltersBtn))
                    this.refreshDashboard();
            });
        });

        // ✅ FIXED: Correct button class for opening modal
        document.addEventListener('click', e => {
            const button = e.target.closest('.openAssignModal');
            if (button) {
                e.preventDefault();
                const ticketId = button.dataset.ticketId || button.dataset.id;
                if (ticketId) this.openModal(ticketId);
            }
        });

        // ✅ FIXED: Pagination with URL correction
        document.addEventListener('click', e => {
            const link = e.target.closest('#ticketTableContainer .pagination a');
            if (link) {
                e.preventDefault();
                
                // ✅ FIXED: Convert dashboard URL to tickets-table URL
                const originalUrl = new URL(link.href);
                let correctedUrl;
                
                if (originalUrl.pathname === '/dashboard') {
                    // Convert /dashboard?page=2 to /dashboard/tickets-table?page=2
                    correctedUrl = `/dashboard/tickets-table${originalUrl.search}`;
                } else {
                    correctedUrl = link.href;
                }
                
                console.log('Pagination - Original URL:', link.href);
                console.log('Pagination - Corrected URL:', correctedUrl);
                
                this.refreshDashboard(correctedUrl);
            }
        });

        // ✅ ADDED: Escape key to close modal
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && this.modal && !this.modal.classList.contains('hidden')) {
                this.closeModal();
            }
        });
    }

    // Refresh the dashboard table + stats panels
    async refreshDashboard(url = null) {
        try {
            const params = new URLSearchParams(new FormData(this.filters || document.createElement('form')));
            const extraParams = new URLSearchParams(new FormData(this.extraFilters || document.createElement('form')));
            const allParams = new URLSearchParams(params.toString() + '&' + extraParams.toString());

            // ✅ FIXED: Use the provided URL directly for pagination, otherwise build URL
            const tableUrl = url || `/dashboard/tickets-table?${allParams.toString()}`;
            const statsUrl = `/dashboard/tickets-stats?${allParams.toString()}`;

            console.log('Refresh - Table URL:', tableUrl);
            
            this.tableContainer?.classList.add('opacity-50', 'pointer-events-none');

            // ✅ FIXED: Only fetch stats if we're not doing pagination
            const promises = [fetch(tableUrl, { 
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } 
            })];

            // Only update stats on filter changes, not pagination
            if (!url) {
                promises.push(fetch(statsUrl, { 
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } 
                }));
            }

            const [tableRes, statsRes] = await Promise.all(promises);

            // Update table
            if (tableRes.ok) {
                const html = await tableRes.text();
                this.tableContainer.innerHTML = html;
                
                // Rebind assign buttons after table refresh
                this.rebindAssignButtons();
            } else {
                throw new Error(`Table fetch failed: ${tableRes.status}`);
            }

            // Update stats only if we fetched them
            if (statsRes && statsRes.ok) {
                const html = await statsRes.text();
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const newStats = wrapper.querySelector('#statsPanel');
                if (newStats && this.statsPanel) {
                    this.statsPanel.replaceWith(newStats);
                    this.statsPanel = newStats;
                }
            }

            this.showToast('✅ Dashboard updated');
        } catch (err) {
            console.error('❌ Dashboard refresh error:', err);
            this.showToast('⚠️ Failed to refresh dashboard', 'error');
        } finally {
            this.tableContainer?.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    // Rebind assign buttons after table refresh
    rebindAssignButtons() {
        document.querySelectorAll('.openAssignModal').forEach(button => {
            // Remove existing event listeners
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            // Add new event listener
            newButton.addEventListener('click', (e) => {
                e.preventDefault();
                const ticketId = newButton.dataset.ticketId || newButton.dataset.id;
                if (ticketId) this.openModal(ticketId);
            });
        });
    }

    // Open Assign / Edit Modal - UPDATED with better error handling
    async openModal(ticketId) {
        if (!ticketId) return this.showToast('⚠️ Invalid ticket ID', 'error');

        console.log('Opening modal for ticket:', ticketId);

        // Show modal immediately with loading spinner
        this.modal.classList.remove('hidden');
        this.modal.classList.add('flex');
        
        // Show loading state in modal container
        this.modalContainer.innerHTML = `
            <div class="flex justify-center items-center min-h-[200px]">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>`;

        try {
            const res = await fetch(`/tickets/${ticketId}/assign`, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }

            const html = await res.text();
            
            // ✅ FIXED: Replace modal container content with the loaded HTML
            this.modalContainer.innerHTML = html;
            
            // ✅ FIXED: Initialize the assign modal functionality
            this.initAssignModalContent();

            console.log('✅ Modal loaded successfully');

        } catch (err) {
            console.error('❌ Failed to load assignment form:', err);
            this.modalContainer.innerHTML = `
                <div class="text-center p-6">
                    <p class="text-red-500 dark:text-red-400 mb-4">
                        ⚠️ Failed to load assignment form
                    </p>
                    <button class="closeAssignModal px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                        Close
                    </button>
                </div>`;
            this.showToast('⚠️ Failed to load assignment form', 'error');
            
            // Re-bind close button in error state
            this.modalContainer.querySelector('.closeAssignModal')?.addEventListener('click', () => this.closeModal());
        }
    }

    // Initialize modal content after loading
    initAssignModalContent() {
        // Close modal buttons
        this.modalContainer.querySelectorAll('.closeAssignModal').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeModal();
            });
        });

        // Form submission
        const form = this.modalContainer.querySelector('#assignForm');
        if (form) {
            // Remove any existing event listeners
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
            
            newForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleAssignFormSubmit(newForm);
            });
        }
    }

    // Close modal
    closeModal() {
        if (this.modal) {
            this.modal.classList.add('hidden');
            this.modal.classList.remove('flex');
            // Keep the modal in DOM but clear its content
            this.modalContainer.innerHTML = `
                <div class="flex justify-center items-center min-h-[200px]">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>`;
        }
    }

    // Handle Assign Form submission
    async handleAssignFormSubmit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';

        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <div class="flex items-center gap-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                    Saving...
                </div>`;
        }

        const formData = new FormData(form);
        
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();

            if (res.ok && data.success) {
                this.showToast('✅ Ticket updated successfully');
                this.closeModal();
                await this.refreshDashboard();
            } else {
                throw new Error(data.message || Object.values(data.errors || {}).join(', ') || 'Update failed');
            }
        } catch (err) {
            console.error('❌ Ticket update error:', err);
            this.showToast(err.message || '⚠️ Failed to update ticket', 'error');
        } finally {
            // Restore button state
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }

    // Toast notification
    showToast(message, type = 'success') {
        if (!this.toast) return;
        this.toast.textContent = message;
        this.toast.className = `fixed bottom-5 right-5 px-6 py-3 rounded-xl shadow-lg text-white z-50 max-w-sm transition ${
            type === 'error' ? 'bg-red-600' : 'bg-green-600'
        }`;
        this.toast.classList.remove('hidden');
        setTimeout(() => this.toast.classList.add('hidden'), 3000);
    }
}

// Initialize
window.dashboard = null;
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new DashboardManager();
});