<template>
  <div class="notifications-dropdown">
    <div class="dropdown" ref="dropdown">
      <button 
        class="btn btn-link nav-link position-relative p-0"
        @click="toggleDropdown"
        ref="dropdownButton"
      >
        <i class="fas fa-bell"></i>
        <span 
          v-if="unreadCount > 0" 
          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </button>

      <div 
        v-if="isOpen" 
        class="dropdown-menu show notification-dropdown"
        style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate(-250px, 40px);"
      >
        <div class="dropdown-header d-flex justify-content-between align-items-center">
          <span>Notifications</span>
          <div>
            <button 
              v-if="unreadCount > 0"
              @click="markAllAsRead" 
              class="btn btn-sm btn-outline-primary me-2"
              :disabled="loading"
            >
              Mark All Read
            </button>
            <button @click="viewAll" class="btn btn-sm btn-outline-secondary">
              View All
            </button>
          </div>
        </div>

        <div class="dropdown-divider"></div>

        <div class="notifications-list" style="max-height: 400px; overflow-y: auto;">
          <div 
            v-if="loading && notifications.length === 0" 
            class="text-center p-3"
          >
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span class="ms-2">Loading...</span>
          </div>

          <div 
            v-else-if="notifications.length === 0" 
            class="text-center p-3 text-muted"
          >
            No notifications
          </div>

          <div 
            v-else
            v-for="notification in notifications" 
            :key="notification.id"
            :class="['notification-item', { unread: !notification.read_at }]"
            @click="handleNotificationClick(notification)"
          >
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <p class="mb-1">{{ notification.message }}</p>
                <small class="text-muted">{{ notification.created_at }}</small>
              </div>
              <div class="ms-2">
                <button 
                  v-if="!notification.read_at"
                  @click.stop="markAsRead(notification.id)"
                  class="btn btn-sm btn-outline-success"
                  title="Mark as read"
                >
                  <i class="fas fa-check"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'NotificationBell',
  data() {
    return {
      isOpen: false,
      loading: false,
      notifications: [],
      unreadCount: 0,
      pollInterval: null
    }
  },
  mounted() {
    this.loadUnreadCount(); // Only load count initially
    this.startPolling();
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    this.stopPolling();
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    toggleDropdown() {
      this.isOpen = !this.isOpen;
      if (this.isOpen) {
        this.loadNotifications();
      }
    },
    
    handleClickOutside(event) {
      const dropdown = this.$refs.dropdown;
      // Safety check if refs exist
      if (dropdown && !dropdown.contains(event.target)) {
        this.isOpen = false;
      }
    },
    
    // ✅ FIXED: Route matches /api/notifications
    async loadNotifications() {
      try {
        this.loading = true;
        const response = await axios.get('/api/notifications?page=1');
        
        // Match Laravel Pagination Structure
        this.notifications = response.data.data.slice(0, 5); // Show top 5
        
        // Optional: Update count if provided in response
        if(response.data.unread_count !== undefined) {
           this.unreadCount = response.data.unread_count;
        }
      } catch (error) {
        console.error('Failed to load notifications:', error);
      } finally {
        this.loading = false;
      }
    },

    // ✅ FIXED: Route matches /api/notifications/unread-count
    async loadUnreadCount() {
       try {
        const response = await axios.get('/api/notifications/unread-count');
        this.unreadCount = response.data.count;
      } catch (error) {
        console.error('Failed to load unread count:', error);
      }
    },
    
    // ✅ FIXED: Route matches /api/notifications/mark-all-read
    async markAllAsRead() {
      try {
        const response = await axios.post('/api/notifications/mark-all-read');
        
        if (response.data.success) {
          this.notifications = this.notifications.map(notification => ({
            ...notification,
            read_at: new Date().toISOString()
          }));
          this.unreadCount = 0;
        }
      } catch (error) {
        console.error('Failed to mark all as read:', error);
      }
    },
    
    // ✅ FIXED: Route matches /api/notifications/{id}/read
    async markAsRead(notificationId) {
      try {
        const response = await axios.post(`/api/notifications/${notificationId}/read`);
        
        if (response.data.success) {
          const notification = this.notifications.find(n => n.id === notificationId);
          if (notification) {
            notification.read_at = new Date().toISOString();
          }
          // Update count from server or decrement locally
          if (response.data.unread_count !== undefined) {
             this.unreadCount = response.data.unread_count;
          } else {
             this.unreadCount = Math.max(0, this.unreadCount - 1);
          }
        }
      } catch (error) {
        console.error('Failed to mark as read:', error);
      }
    },
    
    handleNotificationClick(notification) {
      if (!notification.read_at) {
        this.markAsRead(notification.id);
      }
      
      // Navigate to your manual route for notifications
      window.location.href = '/dashboard/notifications';
    },
    
    viewAll() {
      window.location.href = '/dashboard/notifications';
      this.isOpen = false;
    },
    
    startPolling() {
      this.pollInterval = setInterval(() => {
        if (!this.isOpen) {
          this.loadUnreadCount();
        }
      }, 30000); 
    },
    
    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
      }
    }
  }
}
</script>

<style scoped>
.notification-dropdown {
  min-width: 350px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  z-index: 1000;
}

.notification-item {
  padding: 12px 15px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  background: white;
}

.notification-item:hover {
  background-color: #f8f9fa;
}

.notification-item.unread {
  background-color: #f0f7ff;
  border-left: 3px solid #007bff;
}

.dropdown-header {
  padding: 10px;
  background-color: #f8f9fa;
  border-bottom: 1px solid #eee;
}
</style>