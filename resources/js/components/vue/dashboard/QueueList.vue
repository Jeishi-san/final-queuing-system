<template>
  <div class="flex flex-col items-center h-[calc(100vh-100px)] w-full px-10 py-5 box-border">

    <!-- FILTER BAR -->
    <div v-if="isFilterClicked" class="w-full max-w-6xl mb-4 p-3 bg-white rounded-xl shadow-sm grid grid-cols-1 md:grid-cols-3 gap-3">

      <!-- Row 1 -->
      <div class="col-span-1 md:col-span-2 flex gap-3">

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

        <!-- Assigned To -->
        <select v-model="filters.assigned_to" class="p-2 rounded border">
          <option value="">All assignees</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>

      </div>

      <!-- Row 1 right -->
      <div class="flex items-center gap-3">
        <!-- Status -->
        <select v-model="filters.status" class="p-2 rounded border">
          <option value="">All statuses</option>
          <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
        </select>
      </div>

      <!-- Row 2 -->
      <div class="col-span-3 flex gap-3 items-center mt-2">

        <label class="flex items-center gap-2">
          From
          <input type="date" v-model="filters.start_date" class="p-2 rounded border">
        </label>

        <label class="flex items-center gap-2">
          To
          <input type="date" v-model="filters.end_date" class="p-2 rounded border">
        </label>

        <button @click="applyFilters" class="px-4 py-2 rounded bg-[#003D5B] text-white">Apply</button>
        <button @click="resetFilters" class="px-4 py-2 rounded border">Reset</button>

      </div>

    </div>


    <!-- Table Container -->
    <div class="w-full max-w-6xl flex flex-col shadow-lg rounded-3xl overflow-hidden h-full">

      <!-- Table Header -->
      <div class="grid grid-cols-5 gap-3 bg-gray-200 p-3 rounded-t-2xl">
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
          class="grid grid-cols-5 gap-3 border-b p-3 hover:bg-gray-100 cursor-pointer"
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
    import { ref, onMounted } from 'vue';

    const style_header = "font-semibold text-[#003D5B]";

    const props = defineProps({
      isFilterClicked: Boolean,
    });

    // reactive variables
    const queueList = ref([]);
    const loading = ref(true);

    const users = ref([]);

    // FILTERS (SERVER-SIDE)
    const filters = ref({
        //queue_number: "",
        ticket_number: "",
        //assigned_to: "",
        status: "",
        //start_date: "",
        //end_date: "",
    });

    const statuses = ["pending approval","queued","in progress","on hold","resolved","cancelled"];

    // fetch data from backend
    const fetchQueues = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/queues/list', { params: filters.value });
            queueList.value = response.data; // adjust if API returns { data: [...] } structure
            console.log(response.data);
        } catch (error) {
            console.error('Error fetching queue items:', error);
        } finally {
            loading.value = false;
        }
    };

    const applyFilters = () => fetchQueues();

    const resetFilters = () => {
        filters.value = { status: "", ticket_number: "" };
        fetchQueues();
    };

    // Date format
    const formatDate = (iso) => {
        if (!iso) return "N/A";
        return new Date(iso).toLocaleString();
    };

    onMounted(() => {
        fetchQueues();
    });

    const goToTicket = (ticketId) => {
        if (!ticketId) return;
        window.location.href = `/dashboard/tickets?highlight=${ticketId}`;
    };
</script>
