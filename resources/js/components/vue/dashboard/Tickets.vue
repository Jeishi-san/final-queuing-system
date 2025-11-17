<template>
  <div class="flex flex-col items-center h-[calc(100vh-100px)] w-full px-10 py-5 box-border">

    <!-- Table Container -->
    <div class="w-full max-w-6xl flex flex-col shadow-lg rounded-3xl overflow-hidden h-full">

      <!-- Table Header -->
      <div class="grid grid-cols-[150px_120px_220px_120px_160px_120px_100px] bg-gray-200 p-3 rounded-t-2xl">
        <div :class="style_header">Ticket ID</div>
        <div :class="style_header">Name</div>
        <div :class="style_header">Email</div>
        <div :class="style_header">Issue Type</div>
        <div :class="style_header">Date Added</div>
        <div :class="style_header">Status</div>
        <div :class="style_header">Action</div>
      </div>

      <!-- Table Rows -->
      <div class="overflow-y-auto bg-[#99bbc4] flex-1 max-h-[calc(100vh-160px)]">
        <div
          v-for="ticket in ticketList"
          :key="ticket.id"
          class="grid grid-cols-[150px_120px_220px_120px_160px_120px_100px] border-b p-3 hover:bg-gray-100 cursor-pointer"
        >
          <div>{{ ticket.ticket_number }}</div>
          <div>{{ ticket.holder_name }}</div>
          <div>{{ ticket.holder_email }}</div>
          <div>{{ ticket.issue }}</div>
          <div>{{ ticket.created_at }}</div>
          <div>{{ ticket.status }}</div>
          <div class="flex space-x-3 justify-center">
            <button class="bg-[rgb(0,122,135)] text-white px-2 rounded hover:bg-[#006873] transition">Update</button>
            <button class="bg-[#C9302C] text-white px-2 rounded hover:bg-[#A52824] transition">Delete</button>
          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="text-center py-4 text-gray-700">Loading...</div>

        <!-- No data state -->
        <div v-if="!loading && ticketList.length === 0" class="text-center py-4 text-gray-700">No tickets found.</div>
      </div>

    </div>

  </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue';
    import axios from 'axios';

    const style_header = "font-semibold text-[#003D5B]";

    const ticketList = ref([]);
    const loading = ref(true);

    const fetchTickets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/tickets'); // replace with your API endpoint
        ticketList.value = response.data; // adjust if API returns { data: [...] } structure
        console.log(response.data);
    } catch (error) {
        console.error('Error fetching tickets:', error);
    } finally {
        loading.value = false;
    }
    };

    onMounted(fetchTickets);
</script>
