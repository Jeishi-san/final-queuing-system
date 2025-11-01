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
        
        this.currentPage = 1;
        this.currentFilters = new URLSearchParams();

        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCurrentState();
    }

    loadCurrentState() {
        // Get current page and filters from URL
        const urlParams = new URLSearchParams(window.location.search);
        this.currentPage = urlParams.get('page') || 1;
        this.currentFilters = new URLSearchParams();
        
        // Store current filter values (EXCLUDE page parameter from filters)
        ['search', 'status', 'it_personnel_id'].forEach(param => {
            const value = urlParams.get(param);
            if (value) this.currentFilters.set(param, value);
        });

        console.log('Loaded state - Page:', this.currentPage, 'Filters:', Object.fromEntries(this.currentFilters));
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
            this.currentPage = 1; // Reset to first page on new search
            this.refreshDashboard();
        });

        this.extraFilters?.addEventListener('submit', e => {
            e.preventDefault();
            this.currentPage = 1; // Reset to first page on new filter
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
                if (!document.activeElement.isEqualNode(this.applyFiltersBtn)) {
                    this.currentPage = 1; // Reset to first page on filter change
                    this.refreshDashboard();
                }
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

        // ✅ FIXED: Pagination with preserved state
        document.addEventListener('click', e => {
            const link = e.target.closest('#ticketTableContainer .pagination a');
            if (link) {
                e.preventDefault();
                
                // Extract page number from the clicked link
                const url = new URL(link.href);
                const page = url.searchParams.get('page') || '1';
                this.currentPage = page;
                
                console.log('Pagination - Navigating to page:', this.currentPage);
                
                this.refreshDashboard();
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
    async refreshDashboard() {
        try {
            // Build URL with current filters and page
            const params = new URLSearchParams();
            
            // Add current filters (EXCLUDE page from filters)
            this.currentFilters.forEach((value, key) => {
                if (key !== 'page' && value) {
                    params.set(key, value);
                }
            });
            
            // ✅ FIXED: Always use currentPage for pagination
            if (this.currentPage && this.currentPage !== '1') {
                params.set('page', this.currentPage);
            }
            
            // ✅ FIXED: Get form values WITHOUT overwriting currentPage
            const formParams = new URLSearchParams(new FormData(this.filters || document.createElement('form')));
            const extraParams = new URLSearchParams(new FormData(this.extraFilters || document.createElement('form')));
            
            // ✅ FIXED: Merge form params but EXCLUDE page parameter
            formParams.forEach((value, key) => {
                if (key !== 'page' && value) {
                    params.set(key, value);
                    this.currentFilters.set(key, value);
                } else if (key !== 'page' && !value) {
                    params.delete(key);
                    this.currentFilters.delete(key);
                }
            });
            
            extraParams.forEach((value, key) => {
                if (key !== 'page' && value) {
                    params.set(key, value);
                    this.currentFilters.set(key, value);
                } else if (key !== 'page' && !value) {
                    params.delete(key);
                    this.currentFilters.delete(key);
                }
            });

            const tableUrl = `/dashboard/tickets-table?${params.toString()}`;
            const statsUrl = `/dashboard/tickets-stats?${params.toString()}`;

            console.log('Refresh - Table URL:', tableUrl);
            console.log('Refresh - Current Page:', this.currentPage);
            console.log('Refresh - Current Filters:', Object.fromEntries(this.currentFilters));
            
            this.tableContainer?.classList.add('opacity-50', 'pointer-events-none');

            // Fetch both table and stats
            const [tableRes, statsRes] = await Promise.all([
                fetch(tableUrl, { 
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } 
                }),
                fetch(statsUrl, { 
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } 
                })
            ]);

            if (!tableRes.ok) {
                throw new Error(`Table fetch failed: ${tableRes.status}`);
            }

            if (!statsRes.ok) {
                throw new Error(`Stats fetch failed: ${statsRes.status}`);
            }

            // Update table
            const tableHtml = await tableRes.text();
            this.tableContainer.innerHTML = tableHtml;
            
            // Rebind assign buttons after table refresh
            this.rebindAssignButtons();

            // Update stats
            const statsHtml = await statsRes.text();
            const wrapper = document.createElement('div');
            wrapper.innerHTML = statsHtml.trim();
            const newStats = wrapper.querySelector('#statsPanel');
            if (newStats && this.statsPanel) {
                this.statsPanel.replaceWith(newStats);
                this.statsPanel = newStats;
            }

            // ✅ FIXED: Update browser URL with correct page
            this.updateBrowserUrl(params.toString());
            
            this.showToast('✅ Dashboard updated');
        } catch (err) {
            console.error('❌ Dashboard refresh error:', err);
            this.showToast('⚠️ Failed to refresh dashboard', 'error');
        } finally {
            this.tableContainer?.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    // Update browser URL without page reload
    updateBrowserUrl(params) {
        const newUrl = params ? `${window.location.pathname}?${params}` : window.location.pathname;
        window.history.replaceState({}, '', newUrl);
        console.log('URL updated to:', newUrl);
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

    // Handle Assign Form submission - FIXED for route issue
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

        try {
            const formData = new FormData(form);
            const ticketId = formData.get('ticket_id');
            
            if (!ticketId) {
                throw new Error('Ticket ID is required');
            }

            // Build update data - REMOVED _method since we're using POST directly
            const updateData = {
                it_personnel_id: formData.get('it_personnel_id') || null,
                status: formData.get('status'),
                component_id: formData.get('component_id') || null
            };

            // Validate status according to your controller
            const validStatuses = ['pending', 'in_progress', 'resolved', 'overdue', 'cancelled'];
            if (!validStatuses.includes(updateData.status)) {
                throw new Error(`Invalid status: "${updateData.status}". Must be one of: ${validStatuses.join(', ')}`);
            }

            console.log('📤 Sending ticket update:', updateData);

            // Use POST method directly (no _method needed)
            const res = await fetch(`/tickets/${ticketId}`, {
                method: 'POST', // Use POST method directly
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(updateData)
            });

            // Check if response is JSON
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await res.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Please try again.');
            }

            const data = await res.json();

            if (!res.ok) {
                // Handle validation errors
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || `HTTP error! status: ${res.status}`);
            }

            if (data.success) {
                this.showToast('✅ Ticket updated successfully');
                this.closeModal();
                await this.refreshDashboard();
            } else {
                throw new Error(data.message || 'Update failed without error');
            }

        } catch (err) {
            console.error('❌ Ticket update error:', err);
            
            // More specific error messages for common issues
            let userMessage = err.message;
            if (err.message.includes('Failed to fetch')) {
                userMessage = 'Network error. Please check your connection and try again.';
            } else if (err.message.includes('CSRF token')) {
                userMessage = 'Session expired. Please refresh the page and try again.';
            } else if (err.message.includes('500') || err.message.includes('Internal Server Error')) {
                userMessage = 'Server error. Please try again in a moment.';
            } else if (err.message.includes('PUT method is not supported')) {
                userMessage = 'Server configuration error. Please contact administrator.';
            } else if (err.message.includes('405') || err.message.includes('Method Not Allowed')) {
                userMessage = 'Action not allowed. Please refresh the page and try again.';
            }

            this.showToast(userMessage, 'error');
            
            // Log detailed error for debugging
            if (!userMessage.includes('Network error') && !userMessage.includes('Session expired')) {
                console.debug('Detailed error:', err);
            }
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