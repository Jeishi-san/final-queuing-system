    /**
     * Assign Modal JavaScript
     * Handles modal functionality for ticket assignment
     * Designed to work with DashboardManager
     */

    class AssignModalHandler {
        constructor() {
            this.modal = null;
            this.container = null;
            this.isInitialized = false;
        }

        /**
         * Initialize modal with loaded content
         */
        init(modalContainer) {
            if (this.isInitialized) {
                this.cleanup();
            }

            this.container = modalContainer;
            this.modal = document.getElementById('assignModal');
            
            if (!this.container || !this.modal) {
                console.warn('Assign modal elements not found');
                return;
            }

            this.bindEvents();
            this.isInitialized = true;
        }

        /**
         * Bind modal events
         */
        bindEvents() {
            // Close modal buttons
            this.container.querySelectorAll('.closeAssignModal').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.close();
                });
            });

            // Form submission
            const form = this.container.querySelector('#assignForm');
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    await this.handleFormSubmit(form);
                });
            }

            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.modal && !this.modal.classList.contains('hidden')) {
                    this.close();
                }
            });
        }

        /**
         * Handle form submission
         */
        async handleFormSubmit(form) {
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
                    this.showToast('✅ Ticket updated successfully!');
                    this.close();
                    
                    // ✅ ENHANCED: Refresh notifications after successful update
                    this.refreshNotifications();
                    
                    // Refresh dashboard if available
                    if (window.dashboard && typeof window.dashboard.refreshDashboard === 'function') {
                        await window.dashboard.refreshDashboard();
                    } else {
                        // Fallback refresh
                        window.location.reload();
                    }
                } else {
                    throw new Error(data.message || Object.values(data.errors || {}).join(', ') || 'Update failed');
                }
            } catch (err) {
                console.error('❌ Ticket update error:', err);
                this.showToast(err.message || '⚠️ Failed to update ticket', true);
            } finally {
                // Restore button state
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        }

        /**
         * Refresh notifications after ticket update
         */
        refreshNotifications() {
            // ✅ NEW: Refresh notifications if notification manager exists
            if (window.notificationManager && typeof window.notificationManager.loadNotifications === 'function') {
                console.log('🔄 Refreshing notifications after ticket update');
                setTimeout(() => {
                    window.notificationManager.loadNotifications();
                }, 1000); // Small delay to ensure notification is created
            } else {
                console.log('ℹ️ Notification manager not available for refresh');
            }
            
            // ✅ NEW: Also try dashboard notification refresh if available
            if (window.dashboard && typeof window.dashboard.refreshNotifications === 'function') {
                window.dashboard.refreshNotifications();
            }
        }

        /**
         * Close modal
         */
        close() {
            if (this.modal) {
                this.modal.classList.add('hidden');
                this.modal.classList.remove('flex');
                
                // Clear container content but keep the structure
                if (this.container) {
                    this.container.innerHTML = `
                        <div class="flex justify-center items-center min-h-[200px]">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        </div>`;
                }
            }
            this.cleanup();
        }

        /**
         * Cleanup event listeners
         */
        cleanup() {
            // Remove event listeners to prevent duplicates
            if (this.container) {
                const newContainer = this.container.cloneNode(false);
                this.container.parentNode.replaceChild(newContainer, this.container);
                this.container = newContainer;
            }
            this.isInitialized = false;
        }

        /**
         * Show toast notification
         */
        showToast(message, isError = false) {
            // Use dashboard's toast if available
            if (window.dashboard && typeof window.dashboard.showToast === 'function') {
                window.dashboard.showToast(message, isError ? 'error' : 'success');
                return;
            }

            // Fallback toast implementation
            const toast = document.createElement('div');
            toast.textContent = message;
            toast.className = `
                fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-white text-sm
                transition-all duration-300 z-[9999] font-medium
                ${isError ? 'bg-red-500' : 'bg-green-600'}
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
            }, 10);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 3000);
        }
    }

    // Global instance
    window.assignModalHandler = new AssignModalHandler();

    /**
     * Initialize assign modal when content is loaded
     * This is called by DashboardManager after loading modal content
     */
    function initAssignModal() {
        const modalContainer = document.getElementById('assignFormContainer');
        if (modalContainer) {
            window.assignModalHandler.init(modalContainer);
        }
    }

    /**
     * Fallback initialization for direct modal usage
     */
    document.addEventListener('DOMContentLoaded', () => {
        // Only initialize if we're on a page with the modal container
        const modalContainer = document.getElementById('assignFormContainer');
        const modal = document.getElementById('assignModal');
        
        if (modalContainer && modal && modalContainer.children.length > 0) {
            // Check if content is already loaded (direct page access)
            const hasContent = modalContainer.querySelector('#assignForm') || 
                            modalContainer.textContent.trim().length > 100;
            
            if (hasContent) {
                window.assignModalHandler.init(modalContainer);
            }
        }
    });

    // Export for ES modules
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { AssignModalHandler, initAssignModal };
    }