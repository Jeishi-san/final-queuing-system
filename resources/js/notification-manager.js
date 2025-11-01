// resources/js/notification-manager.js

class NotificationManager {
    constructor() {
        this.pollingInterval = null;
        this.isDropdownOpen = false;
        this.isLoading = false;
        this.init();
    }

    init() {
        console.log('🔔 NotificationManager initialized');
        this.bindEvents();
        this.startPolling();
        this.loadNotifications(); // Load initial notifications
    }

    bindEvents() {
        const button = document.getElementById('notificationButton');
        const dropdown = document.getElementById('notificationDropdown');
        const refreshBtn = document.getElementById('refreshNotifications');
        const markAllForm = document.getElementById('markAllReadForm');

        console.log('Binding events:', { 
            button: !!button, 
            dropdown: !!dropdown, 
            refreshBtn: !!refreshBtn, 
            markAllForm: !!markAllForm 
        });

        // Toggle dropdown
        button?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        // Refresh notifications
        refreshBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('Manual refresh triggered');
            this.loadNotifications();
        });

        // Mark all as read
        markAllForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.markAllAsRead();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!button?.contains(e.target) && !dropdown?.contains(e.target)) {
                this.closeDropdown();
            }
        });

        // Prevent dropdown from closing when clicking inside
        dropdown?.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isDropdownOpen) {
                this.closeDropdown();
            }
        });
    }

    toggleDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        if (!dropdown) return;
        
        dropdown.classList.toggle('hidden');
        this.isDropdownOpen = !dropdown.classList.contains('hidden');
        
        console.log('Dropdown toggled:', { isOpen: this.isDropdownOpen });
        
        if (this.isDropdownOpen) {
            this.loadNotifications(); // Refresh when opening
        }
    }

    closeDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
            this.isDropdownOpen = false;
        }
    }

    async loadNotifications() {
        if (this.isLoading) {
            console.log('⚠️ Notification load already in progress, skipping...');
            return;
        }

        this.isLoading = true;
        console.log('🔄 Loading notifications...');
        
        try {
            // Show loading state
            this.showLoadingState();

            const csrfToken = this.getCsrfToken();
            
            const response = await fetch('/notifications/unread', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            });

            console.log('📡 Fetch response status:', response.status, response.statusText);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('📨 Notifications data received:', data);
            
            if (data.success) {
                this.updateNotificationCount(data.count);
                this.renderNotifications(data.notifications);
                console.log('✅ Notifications loaded successfully');
            } else {
                throw new Error(data.message || 'API returned unsuccessful response');
            }
        } catch (error) {
            console.error('❌ Error loading notifications:', error);
            this.renderError(error.message);
        } finally {
            this.isLoading = false;
        }
    }

    getCsrfToken() {
        // Try multiple ways to get CSRF token
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) return metaTag.content;
        
        const tokenInput = document.querySelector('input[name="_token"]');
        if (tokenInput) return tokenInput.value;
        
        console.warn('⚠️ CSRF token not found');
        return '';
    }

    showLoadingState() {
        const container = document.getElementById('notificationList');
        if (container) {
            container.innerHTML = `
                <div class="p-6 text-center text-gray-500 text-sm">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto mb-2"></div>
                    Loading notifications...
                </div>
            `;
        }
    }

    updateNotificationCount(count) {
        const unreadCount = document.getElementById('unreadCount');
        const markAllForm = document.getElementById('markAllReadForm');
        
        console.log('🔢 Updating notification count:', count);
        
        if (unreadCount) {
            if (count > 0) {
                unreadCount.textContent = count > 99 ? '99+' : count;
                unreadCount.classList.remove('hidden');
                if (markAllForm) markAllForm.classList.remove('hidden');
                // Add pulse animation for new notifications
                unreadCount.classList.add('animate-pulse');
                setTimeout(() => unreadCount.classList.remove('animate-pulse'), 2000);
            } else {
                unreadCount.classList.add('hidden');
                if (markAllForm) markAllForm.classList.add('hidden');
            }
        }
    }

    renderNotifications(notifications) {
        const container = document.getElementById('notificationList');
        if (!container) {
            console.error('❌ Notification container not found');
            return;
        }

        console.log('🎨 Rendering notifications:', notifications);

        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="p-6 text-center text-gray-500 text-sm">
                    <div class="text-2xl mb-2">🔕</div>
                    No notifications yet.
                    <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
                </div>
            `;
            return;
        }

        container.innerHTML = notifications.map(notification => {
            console.log('📝 Processing notification:', notification);
            
            // Use the standardized data structure
            const message = notification.message || 'Ticket updated';
            const updatedBy = notification.data?.updated_by || 'System';
            const ticketId = notification.ticket_id || notification.data?.ticket_id;
            const ticketTitle = notification.data?.ticket_title || notification.data?.ticket_number || 'Unknown Ticket';
            const createdAt = notification.created_at || 'Recently';
            const isUnread = !notification.read_at;
            const changes = notification.data?.changes || {};

            return `
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200 notification-item" 
                 data-notification-id="${notification.id}">
                <div class="flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 mb-1 leading-tight">
                            ${this.escapeHtml(message)}
                        </p>
                        ${this.renderChanges(changes)}
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-500">
                                ${this.escapeHtml(createdAt)}
                            </span>
                            ${isUnread ? `
                            <button class="mark-as-read text-xs text-green-600 hover:text-green-800 font-medium transition-colors" 
                                    data-id="${notification.id}"
                                    title="Mark as read">
                                ✓ Read
                            </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ${ticketId ? `
                <div class="mt-3 pt-2 border-t border-gray-100">
                    <a href="/tickets/${ticketId}" 
                       class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors inline-flex items-center gap-1">
                        📋 View Ticket
                    </a>
                </div>
                ` : ''}
            </div>
            `;
        }).join('');

        // Add event listeners for mark-as-read buttons
        container.querySelectorAll('.mark-as-read').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAsReadSingle(button.dataset.id);
            });
        });

        console.log('✅ Notifications rendered successfully');
    }

    renderChanges(changes) {
        if (!changes || Object.keys(changes).length === 0) return '';
        
        const changeItems = Object.entries(changes).map(([field, change]) => {
            const fieldNames = {
                status: 'Status',
                it_personnel_id: 'Assignment',
                component_id: 'Component'
            };
            
            const fieldName = fieldNames[field] || field;
            const from = change.from || 'None';
            const to = change.to || 'None';
            
            return `
                <div class="text-xs text-gray-600 mt-1">
                    <span class="font-medium">${fieldName}:</span> 
                    <span class="line-through text-red-500">${from}</span> 
                    → 
                    <span class="text-green-600 font-medium">${to}</span>
                </div>
            `;
        }).join('');
        
        return `<div class="mt-2 pl-2 border-l-2 border-blue-200">${changeItems}</div>`;
    }

    renderError(errorMessage = 'Failed to load notifications') {
        const container = document.getElementById('notificationList');
        if (container) {
            container.innerHTML = `
                <div class="p-6 text-center text-red-600 text-sm">
                    <div class="text-2xl mb-2">⚠️</div>
                    ${this.escapeHtml(errorMessage)}
                    <div class="mt-3">
                        <button onclick="window.notificationManager?.loadNotifications()" 
                                class="px-4 py-2 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition-colors font-medium">
                            Retry
                        </button>
                    </div>
                </div>
            `;
        }
    }

    escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe;
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    async markAllAsRead() {
        console.log('📝 Marking all notifications as read');
        
        try {
            const csrfToken = this.getCsrfToken();
            const response = await fetch('/notifications/mark-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            console.log('📡 Mark all read response:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('✅ Mark all read success:', data);
                this.loadNotifications(); // Refresh the list
                
                // Show success feedback
                this.showTempMessage('All notifications marked as read', 'success');
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('❌ Error marking all as read:', error);
            this.showTempMessage('Failed to mark all as read', 'error');
        }
    }

// In notification-manager.js - update the markAsReadSingle method
async markAsReadSingle(notificationId) {
    console.log('📝 Marking single notification as read:', notificationId);
    
    try {
        const csrfToken = this.getCsrfToken();
        const response = await fetch(`/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        console.log('📡 Mark single read response:', response.status);

        if (response.ok) {
            const data = await response.json();
            console.log('✅ Mark single read success:', data);
            
            // Update the UI immediately - FIXED: Remove the mark-as-read button
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                // Remove the mark-as-read button
                const markAsReadBtn = notificationItem.querySelector('.mark-as-read');
                if (markAsReadBtn) {
                    markAsReadBtn.remove();
                }
                
                // Add visual indication that it's read
                notificationItem.style.opacity = '0.7';
                notificationItem.style.backgroundColor = '#f9fafb'; // light gray
                
                // Add subtle animation
                notificationItem.style.opacity = '0.7';
                setTimeout(() => {
                    notificationItem.style.opacity = '1';
                }, 300);
            }
            
            // Refresh the notification count and list
            await this.loadNotifications();
            this.showTempMessage('Notification marked as read', 'success');
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('❌ Error marking notification as read:', error);
        this.showTempMessage('Failed to mark as read', 'error');
    }
}

// Also update the renderNotifications method to ensure mark-as-read buttons work:
renderNotifications(notifications) {
    const container = document.getElementById('notificationList');
    if (!container) {
        console.error('❌ Notification container not found');
        return;
    }

    console.log('🎨 Rendering notifications:', notifications);

    if (!notifications || notifications.length === 0) {
        container.innerHTML = `
            <div class="p-6 text-center text-gray-500 text-sm">
                <div class="text-2xl mb-2">🔕</div>
                No notifications yet.
                <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
            </div>
        `;
        return;
    }

    container.innerHTML = notifications.map(notification => {
        console.log('📝 Processing notification:', notification);
        
        // Use the standardized data structure
        const message = notification.message || 'Ticket updated';
        const updatedBy = notification.data?.updated_by || 'System';
        const ticketId = notification.ticket_id || notification.data?.ticket_id;
        const ticketTitle = notification.data?.ticket_title || notification.data?.ticket_number || 'Unknown Ticket';
        const createdAt = notification.created_at || 'Recently';
        const isUnread = !notification.read_at; // This should be true for unread notifications
        const changes = notification.data?.changes || {};

        console.log('🔍 Notification unread status:', {
            id: notification.id,
            isUnread: isUnread,
            read_at: notification.read_at
        });

        return `
        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200 notification-item ${isUnread ? 'bg-blue-50 border-l-4 border-blue-500' : ''}" 
             data-notification-id="${notification.id}">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 mb-1 leading-tight">
                        ${this.escapeHtml(message)}
                        ${isUnread ? '<span class="ml-2 inline-block w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>' : ''}
                    </p>
                    ${this.renderChanges(changes)}
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-gray-500">
                            ${this.escapeHtml(createdAt)}
                        </span>
                        ${isUnread ? `
                        <button class="mark-as-read text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 font-medium transition-colors" 
                                data-id="${notification.id}"
                                title="Mark as read">
                            ✓ Mark Read
                        </button>
                        ` : `
                        <span class="text-xs text-gray-400">Read</span>
                        `}
                    </div>
                </div>
            </div>
            ${ticketId ? `
            <div class="mt-3 pt-2 border-t border-gray-100">
                <a href="/tickets/${ticketId}" 
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors inline-flex items-center gap-1">
                    📋 View "${this.escapeHtml(ticketTitle)}"
                </a>
            </div>
            ` : ''}
        </div>
        `;
    }).join('');

    // Add event listeners for mark-as-read buttons
    container.querySelectorAll('.mark-as-read').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            console.log('🎯 Mark-as-read button clicked:', button.dataset.id);
            this.markAsReadSingle(button.dataset.id);
        });
    });

    console.log('✅ Notifications rendered successfully');
}

    showTempMessage(message, type = 'info') {
        // Create temporary message element
        const tempMsg = document.createElement('div');
        tempMsg.className = `fixed top-20 right-4 px-4 py-2 rounded-lg text-white text-sm font-medium z-50 transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-600' : 
            type === 'error' ? 'bg-red-600' : 'bg-blue-600'
        }`;
        tempMsg.textContent = message;
        document.body.appendChild(tempMsg);

        // Remove after 3 seconds
        setTimeout(() => {
            if (tempMsg.parentNode) {
                tempMsg.parentNode.removeChild(tempMsg);
            }
        }, 3000);
    }

    startPolling() {
        console.log('🔄 Starting notification polling...');
        // Poll for new notifications every 20 seconds
        this.pollingInterval = setInterval(() => {
            if (!this.isDropdownOpen && !this.isLoading) {
                console.log('🔄 Auto-polling for notifications');
                this.loadNotifications();
            }
        }, 20000); // 20 seconds
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            console.log('🛑 Notification polling stopped');
        }
    }

    // Clean up method to prevent memory leaks
    destroy() {
        this.stopPolling();
        // Remove any event listeners if needed
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM loaded, initializing NotificationManager...');
    window.notificationManager = new NotificationManager();
});

// Export for manual testing
window.debugNotifications = function() {
    if (window.notificationManager) {
        console.log('🐛 Manual debug triggered');
        window.notificationManager.loadNotifications();
    }
};

// Handle page visibility changes (stop polling when tab is hidden)
document.addEventListener('visibilitychange', function() {
    if (window.notificationManager) {
        if (document.hidden) {
            window.notificationManager.stopPolling();
        } else {
            window.notificationManager.startPolling();
        }
    }
});

// Handle page unload
window.addEventListener('beforeunload', function() {
    if (window.notificationManager) {
        window.notificationManager.destroy();
    }
});