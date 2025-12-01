<template>
  <div class="container mx-auto mt-6 px-4">
    
    <div class="flex justify-between items-center mb-6">
      <div>
        <button 
          v-if="stats.unread > 0"
          @click="markAllAsRead" 
          class="bg-[#003D5B] hover:bg-[#002a40] text-white px-4 py-2 rounded-md transition-colors disabled:opacity-50"
          :disabled="loading"
        >
          Mark All as Read
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-blue-600 text-white rounded-lg shadow-md p-4">
        <div class="flex flex-col">
          <h5 class="text-lg font-semibold opacity-90">Total</h5>
          <p class="text-4xl font-bold mt-2">{{ stats.total || 0 }}</p>
        </div>
      </div>

      <div class="bg-yellow-500 text-white rounded-lg shadow-md p-4">
        <div class="flex flex-col">
          <h5 class="text-lg font-semibold opacity-90">Unread</h5>
          <p class="text-4xl font-bold mt-2">{{ stats.unread || 0 }}</p>
        </div>
      </div>

      <div class="bg-green-600 text-white rounded-lg shadow-md p-4">
        <div class="flex flex-col">
          <h5 class="text-lg font-semibold opacity-90">Read</h5>
          <p class="text-4xl font-bold mt-2">{{ stats.read || 0 }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
      <div class="p-6">
        
        <div v-if="loading && notifications.length === 0" class="text-center py-8 text-gray-500">
          <FontAwesomeIcon :icon="['fas', 'spinner']" spin class="text-3xl mb-2" />
          <p>Loading notifications...</p>
        </div>

        <div v-else-if="!loading && notifications.length === 0" class="text-center py-12 text-gray-500">
          <FontAwesomeIcon :icon="['fas', 'bell-slash']" class="text-4xl mb-4 text-gray-300" />
          <h4 class="text-xl font-semibold text-gray-700">No notifications</h4>
          <p class="text-gray-400">You're all caught up!</p>
        </div>

        <div v-else class="space-y-3">
          <div 
            v-for="notification in notifications"
            :key="notification.id"
            class="p-4 rounded-lg border transition-all duration-200 hover:shadow-md"
            :class="notification.read_at ? 'bg-white border-gray-200' : 'bg-blue-50 border-blue-200 border-l-4 border-l-blue-500'"
          >
            <div class="flex justify-between items-start">
              <div class="flex-grow">
                <h6 class="text-gray-800 font-medium text-lg">{{ notification.message }}</h6>
                <p class="text-gray-500 text-sm mt-1 flex items-center">
                  <FontAwesomeIcon :icon="['fas', 'clock']" class="mr-1" />
                  {{ notification.created_at }}
                </p>
                
                <div class="mt-2 text-xs text-gray-400 flex flex-wrap gap-2">
                  <span v-if="notification.ticket_number" class="bg-gray-100 px-2 py-1 rounded">
                    Ticket #{{ notification.ticket_number }}
                  </span>
                  <span v-if="notification.type" class="bg-gray-100 px-2 py-1 rounded">
                     {{ formatType(notification.type) }}
                  </span>
                </div>
              </div>

              <div class="ml-4 flex space-x-2">
                <button 
                  v-if="!notification.read_at"
                  @click="markAsRead(notification.id)"
                  class="text-green-600 hover:text-green-800 hover:bg-green-100 p-2 rounded-full transition-colors"
                  title="Mark as read"
                >
                  <FontAwesomeIcon :icon="['fas', 'check']" />
                </button>
                <button 
                  @click="deleteNotification(notification.id)"
                  class="text-red-600 hover:text-red-800 hover:bg-red-100 p-2 rounded-full transition-colors"
                  title="Delete"
                >
                  <FontAwesomeIcon :icon="['fas', 'trash']" />
                </button>
              </div>
            </div>
          </div>

          <div v-if="pagination.last_page > 1" class="flex justify-center mt-8 space-x-2">
            <button 
                class="px-4 py-2 border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="pagination.current_page === 1"
                @click="loadNotifications(pagination.current_page - 1)"
            >
                Previous
            </button>
            
            <span class="px-4 py-2 border rounded-md bg-gray-50 text-gray-600">
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            
            <button 
                class="px-4 py-2 border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="pagination.current_page === pagination.last_page"
                @click="loadNotifications(pagination.current_page + 1)"
            >
                Next
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'; 

export default {
  name: 'NotificationsPage',
  data() {
    return {
      loading: false,
      notifications: [],
      stats: {
        total: 0,
        unread: 0,
        read: 0
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0
      }
    }
  },
  mounted() {
    this.loadNotifications();
    this.loadUnreadCount();
  },
  methods: {
    // 1. Fetch List
    async loadNotifications(page = 1) {
      try {
        this.loading = true;
        const response = await axios.get(`/api/notifications?page=${page}`);
        
        // Laravel Pagination Response Structure
        this.notifications = response.data.data;
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total
        };

        // Update total stats from pagination data
        this.stats.total = response.data.total;
        this.updateReadCount();

      } catch (error) {
        console.error('Failed to load notifications:', error);
      } finally {
        this.loading = false;
      }
    },

    // 2. Fetch Unread Count
    async loadUnreadCount() {
      try {
        const response = await axios.get('/api/notifications/unread-count');
        this.stats.unread = response.data.count;
        this.updateReadCount();
      } catch (error) {
        console.error('Failed to load unread count:', error);
      }
    },

    updateReadCount() {
        this.stats.read = Math.max(0, this.stats.total - this.stats.unread);
    },

    // 3. Mark All Read
    async markAllAsRead() {
      try {
        this.loading = true;
        const response = await axios.post('/api/notifications/mark-all-read');
        
        if (response.data.success) {
          this.notifications.forEach(n => n.read_at = new Date().toISOString());
          this.stats.unread = 0;
          this.updateReadCount();
        }
      } catch (error) {
        console.error('Failed to mark all as read:', error);
      } finally {
        this.loading = false;
      }
    },

    // 4. Mark Single Read
    async markAsRead(notificationId) {
      try {
        const response = await axios.post(`/api/notifications/${notificationId}/read`);
        
        if (response.data.success) {
          const notification = this.notifications.find(n => n.id === notificationId);
          if (notification) {
            notification.read_at = new Date().toISOString();
          }
          if (response.data.unread_count !== undefined) {
             this.stats.unread = response.data.unread_count;
          } else {
             this.stats.unread--;
          }
          this.updateReadCount();
        }
      } catch (error) {
        console.error('Failed to mark as read:', error);
      }
    },

    // 5. Delete Single
    async deleteNotification(notificationId) {
      if (!confirm('Are you sure you want to delete this notification?')) {
        return;
      }
      
      try {
        const response = await axios.delete(`/api/notifications/${notificationId}`);
        
        if (response.data.success) {
          this.notifications = this.notifications.filter(n => n.id !== notificationId);
          this.stats.total--;
          if (response.data.unread_count !== undefined) {
             this.stats.unread = response.data.unread_count;
          }
          this.updateReadCount();
        }
      } catch (error) {
        console.error('Failed to delete notification:', error);
      }
    },

    formatType(type) {
      return type.split('\\').pop().replace(/([A-Z])/g, ' $1').trim();
    }
  }
}
</script>