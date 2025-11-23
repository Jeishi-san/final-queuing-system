<template>
    <!-- RIGHT: Activity Log -->
    <div class="col-span-2 bg-white rounded-2xl shadow p-6 flex flex-col">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-[#003D5B]">Activity Log</h2>

            <!-- Refresh Button -->
            <button
                @click="refreshActivityLogs"
                :disabled="loadingLogs"
                class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded flex items-center gap-2 transition disabled:opacity-50"
            >
                <FontAwesomeIcon
                    :icon="['fas', 'rotate']"
                    :class="{ 'animate-spin': loadingLogs }"
                />
                Refresh
            </button>
        </div>

        <!-- Scrollable Activity Table -->
        <div class="overflow-y-auto h-[500px] scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 rounded-lg">
            <table class="min-w-full text-s m">
                <thead class="bg-gray-100 sticky top-0">
                    <tr>
                        <th class="py-2 px-3 text-left font-semibold">Date</th>
                        <th class="py-2 px-3 text-left font-semibold">Action</th>
                        <th class="py-2 px-3 text-left font-semibold">Ticket ID</th>
                        <th class="py-2 px-3 text-left font-semibold">Details</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Logs -->
                    <tr
                        v-for="(log, index) in activityLogs"
                        :key="log.id || index"
                        class="border-b hover:bg-gray-50 transition"
                    >
                        <td class="py-2 px-3">{{ formatDateTime(log.created_at) }}</td>
                        <td class="py-2 px-3 capitalize">{{ log.action_type || log.action }}</td>
                        <td class="py-2 px-3 text-[#003D5B] font-semibold">
                            {{ log.ticket?.ticket_number || log.ticket_id || 'N/A' }}
                        </td>
                        <td class="py-2 px-3">{{ log.description || log.details }}</td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="activityLogs.length === 0 && !loadingLogs">
                        <td colspan="4" class="text-center py-4 text-gray-400">
                            No activity recorded.
                        </td>
                    </tr>

                    <!-- Loading State -->
                    <tr v-if="loadingLogs">
                        <td colspan="4" class="text-center py-4">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#003D5B] mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Local state for activity logs
const activityLogs = ref([]);
const loadingLogs = ref(false);

// Format date and time for activity logs
const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Fetch activity logs
const fetchActivityLogs = async () => {
    try {
        loadingLogs.value = true;
        const response = await axios.get('/api/user/activity-logs');
        activityLogs.value = response.data.activity_logs || [];
    } catch (err) {
        console.error('Error fetching activity logs:', err);
        activityLogs.value = [];
    } finally {
        loadingLogs.value = false;
    }
};

// Refresh activity logs only
const refreshActivityLogs = async () => {
    await fetchActivityLogs();
};

// Initialize component
onMounted(async () => {
    await fetchActivityLogs();
});
</script>

<style scoped>
/* Scrollbar styling */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #c0c0c0;
  border-radius: 3px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background-color: #a0a0a0;
}

/* Loading animation */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
