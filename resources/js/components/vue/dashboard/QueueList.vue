<template>
  <div class="flex flex-col items-center h-[calc(100vh-100px)] w-full px-10 py-5 box-border">

    <!-- FILTER BAR -->
    <div v-if="isFilterClicked" class="w-full max-w-6xl mb-4 p-3 bg-white rounded-xl shadow-sm">

      <!-- Row 1 -->
      <div class="flex gap-4 justify-between">

        <!-- Queue Number -->
        <input
          v-model="filters.queue_number"
          placeholder="Search Queue No."
          class="p-2 rounded border w-1/3"
        />

        <!-- Ticket Number -->
        <input
          v-model="filters.ticket_number"
          placeholder="Search Ticket No."
          class="p-2 rounded border w-1/3"
        />

        <!-- Date Range -->
        <label class="flex items-center gap-2">
          From
          <input type="date" v-model="filters.start_date" class="p-2 rounded border">
        </label>

        <!-- Apply Filters Button -->
        <button @click="applyFilters" class="px-20 py-2 rounded bg-[#003D5B] text-white ml-auto">Apply</button>
      </div>

      <!-- Row 2 -->
      <div class="flex gap-4 mt-3 justify-between">

        <!-- Assigned To -->
        <select v-model="filters.assigned_to" class="p-2 rounded border w-1/3">
          <option value="">All assignees</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>

        <!-- Status -->
        <select v-model="filters.status" class="p-2 rounded border w-1/3">
          <option value="">All statuses</option>
          <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
        </select>

        <!-- Date Range -->
        <label class="flex items-center gap-7">
          To
          <input type="date" v-model="filters.end_date" class="p-2 rounded border">
        </label>

        <!-- Reset Filters Button -->
        <button @click="resetFilters" class="px-20 py-2 rounded border border-[#003D5B] ml-auto">Reset</button>
      </div>
    </div>

    <!-- MANAGE VIEW - Table Container -->
    <div v-if="isManageClicked" class="w-full max-w-6xl flex flex-col shadow-lg overflow-hidden h-full">

        <div class="flex justify-between items-center">
            <h2 class="text-white text-3xl font-semibold mb-5">Managing Queue Items...</h2>
            <button v-if="queueList.some(q => q.delete_ready)" @click="delete_queue_items" class="px-2 py-1 rounded bg-red-500 text-white">Delete Items</button>
        </div>

        <!-- Table Header -->
        <div :class="[manage_grid_cols, 'bg-gray-200 p-3 rounded-t-2xl']">
            <button v-if="!selectAll" @click="selectAll = true">
                <span class="text-[#003D5B] text-lg">
                    <FontAwesomeIcon :icon="['far', 'square']"/>
                </span>
            </button>
            <button v-else @click="selectAll = false">
                <span class="text-[#003D5B] text-lg">
                    <FontAwesomeIcon :icon="['fas', 'square-check']"/>
                </span>
            </button>

            <div :class="style_header">Queue No.</div>
            <div :class="style_header">Ticket Number</div>
            <div :class="style_header">Assigned to</div>
            <div :class="style_header">Last Modified</div>
            <div :class="style_header">Status</div>
        </div>

        <!-- Table Rows -->
        <div class="overflow-y-auto bg-[#99bbc4] flex-1 max-h-[calc(100vh-160px)]">
            <div
                v-for="queue in queueList"
                :key="queue.id"
                :class="[manage_grid_cols, 'border-b p-3 hover:bg-gray-100 cursor-pointer']"
            >
                <button v-if="!queue.delete_ready" @click="queue.delete_ready = true">
                    <span class="text-[#003D5B] text-lg">
                        <FontAwesomeIcon :icon="['far', 'square']"/>
                    </span>
                </button>
                <button v-else @click="queue.delete_ready = false">
                    <span class="text-[#003D5B] text-lg">
                        <FontAwesomeIcon :icon="['fas', 'square-check']"/>
                    </span>
                </button>

                <div>{{ queue.queue_number }}</div>
                <div>{{ queue.ticket?.ticket_number ?? 'N/A' }}</div>
                <div>{{ queue.assigned_user?.name}}</div>
                <div>{{ formatDate(queue.updated_at) }}</div>
                <div>{{ queue.ticket?.status }}</div>
            </div>

            <!-- Loading state -->
            <div v-if="loading" class="text-center py-4 text-gray-700">Loading...</div>

            <!-- No data state -->
            <div v-if="!loading && queueList.length === 0" class="text-center py-4 text-gray-700">No queue item found.</div>
        </div>

    </div>


    <!-- Table Container -->
    <div v-if="!isManageClicked" class="w-full max-w-6xl flex flex-col shadow-lg overflow-hidden h-full">

      <!-- Table Header -->
      <div :class="[grid_cols, 'bg-gray-200 p-3 rounded-t-2xl']">
        <div :class="style_header">Queue No.</div>
        <div :class="style_header">Ticket Number</div>
        <div :class="style_header">Assigned to</div>
        <div :class="style_header">Last Modified</div>
        <div :class="style_header">Status</div>
      </div>

      <!-- Table Rows -->
      <div class="overflow-y-auto bg-[#99bbc4] flex-1 max-h-[calc(100vh-160px)]">
        <div
          v-for="queue in queueList"
          :key="queue.id"
          :class="[grid_cols, 'border-b p-3 hover:bg-gray-100 cursor-pointer']"
          @click="goToTicket(queue.ticket?.id)"
        >
            <div>{{ queue.queue_number }}</div>
            <div>{{ queue.ticket?.ticket_number ?? 'N/A' }}</div>
            <div>{{ queue.assigned_user?.name}}</div>
            <div>{{ formatDate(queue.updated_at) }}</div>
            <div>{{ queue.ticket?.status }}</div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="text-center py-4 text-gray-700">Loading...</div>

        <!-- No data state -->
        <div v-if="!loading && queueList.length === 0" class="text-center py-4 text-gray-700">No queue item found.</div>
      </div>

    </div>

  </div>
</template>

<script setup>
    import { ref, onMounted, computed } from 'vue';

    const style_header = "font-semibold text-[#003D5B]";
    const grid_cols = "grid grid-cols-[200px_200px_180px_220px_200px_auto] gap-4";
    const manage_grid_cols = "grid grid-cols-[25px_200px_200px_180px_220px_200px_auto] gap-4";

    const props = defineProps({
      isFilterClicked: Boolean,
      isManageClicked: Boolean,
    });

    // reactive variables
    const queueList = ref([]);
    const loading = ref(true);

    const users = ref([]);

    // FILTERS (SERVER-SIDE)
    const filters = ref({
        queue_number: "",
        ticket_number: "",
        assigned_to: "",
        status: "",
        start_date: "",
        end_date: "",
    });

    const statuses = ["queued","in progress","on hold","resolved","cancelled"];

    // fetch data from backend
    const fetchUsers = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/users');
            users.value = Array.isArray(response.data) ? response.data : (response.data.data ?? []);; // adjust if API returns { data: [...] } structure
            console.log(response.data);
        } catch (error) {
            console.error('Error fetching users:', error);
        } finally {
            loading.value = false;
        }
    };

    // fetch data from backend
    const fetchQueues = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/queues/list', { params: filters.value });
            queueList.value = response.data; // adjust if API returns { data: [...] } structure

            // add delete_ready to each item
            queueList.value = response.data.map(q => ({
                ...q,
                delete_ready: false
            }));

            console.log(queueList.value);


        } catch (error) {
            console.error('Error fetching queue items:', error);
        } finally {
            loading.value = false;
        }
    };

    const applyFilters = () => fetchQueues();

    const resetFilters = () => {
        filters.value = { status: "", ticket_number: "", queue_number: "", assigned_to: "", start_date: "", end_date: "" };
        fetchQueues();
    };

    // Date format
    const formatDate = (iso) => {
        if (!iso) return "N/A";
        return new Date(iso).toLocaleString();
    };

    onMounted(() => {
        fetchQueues();
        fetchUsers();
    });

    const goToTicket = (ticketId) => {
        if (!ticketId) return;
        window.location.href = `/dashboard/tickets?highlight=${ticketId}`;
    };


    //manage queue codes

    const selectAll = computed({
            get: () => queueList.value.every(q => q.delete_ready),
            set: (value) => {
                queueList.value.forEach(q => q.delete_ready = value);
        }
    });

    const delete_queue_items = async () => {
        // Get all selected queues
        const selectedQueues = queueList.value.filter(q => q.delete_ready);

        if (selectedQueues.length === 0) return; // safety check

        if (!confirm(`Are you sure you want to delete ${selectedQueues.length} queue item(s)?`)) return;

        try {
            // Loop through selected queues
            await Promise.all(
                selectedQueues.map(async (queue) => {
                    // Delete the queue
                    await axios.delete(`/queues/${queue.id}`);

                    // Update the related ticket if exists
                    if (queue.ticket_id) {
                    await axios.put(`/tickets/${queue.ticket_id}/status`, {
                        status: 'pending approval'
                    });
                    }
                })
            );

            alert('Selected queue items deleted and tickets updated successfully.');

            // refresh page
            window.location.href = `/dashboard/queue-list`;
        } catch (error) {
            console.error('Failed to delete queue items:', error);
            alert('Failed to delete one or more items.');
        }
    };

    //refresh queue list when 'Delete Items' button is clicked


</script>
