<template>
  <div class="flex flex-col items-center h-[calc(100vh-100px)] w-full px-10 py-5 box-border">

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
            <div>{{ queue.updated_at }}</div>
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
    import axios from 'axios';

    const style_header = "font-semibold text-[#003D5B]";

    // reactive variables
    const queueList = ref([]);
    const loading = ref(true);

    // fetch data from backend
    const fetchTickets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/queues/listAll');
        queueList.value = response.data; // adjust if API returns { data: [...] } structure
        console.log(response.data);
    } catch (error) {
        console.error('Error fetching queue items:', error);
    } finally {
        loading.value = false;
    }
    };

    onMounted(() => {
        fetchTickets();
    });

    const goToTicket = (ticketId) => {
        if (!ticketId) return;
        window.location.href = `/dashboard/tickets?highlight=${ticketId}`;
    };
</script>
