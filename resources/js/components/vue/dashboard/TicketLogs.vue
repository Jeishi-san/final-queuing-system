<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6">

      <!-- Modal Title -->
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold mb-1">Ticket Logs</h2>
        <button class="text-gray-600" @click="deleteTicket">Delete this ticket</button>
      </div>
      <h2 class="text-md mb-4">Ticket Number: <span class="font-semibold">{{ ticket?.ticket_number }}</span></h2>

      <!-- Logs Body -->
      <div class="max-h-96 overflow-y-auto space-y-2">
        <div
          v-for="log in enrichedLogs"
          :key="log.id"
          class="p-2 border rounded bg-gray-100"
        >
          <div class="text-sm text-gray-500">{{ formatDate(log.created_at) }}</div>
          <div class="text-base font-medium">{{ log.action }}</div>
          <div v-if="log.user_name" class="text-sm text-gray-600">By: {{ log.user_name }}</div>
        </div>

        <div v-if="loading" class="text-center py-4 text-gray-700">Loading...</div>
        <div v-if="!loading && logs.length === 0" class="text-center py-4 text-gray-700">No logs found.</div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end mt-6">
        <button
          class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500"
          @click="$emit('close')"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  ticketId: {
    type: Number,
    required: true
  }
});

const ticket = ref(null);
const logs = ref([]);
const enrichedLogs = ref([]); // logs + user info
const users = ref([]);
const loading = ref(true);

const fetchLogs = async () => {
  loading.value = true;
  try {
    // 1. Fetch ticket details (to get ticket number)
    const resTicket = await axios.get(`/tickets/${props.ticketId}`);
    ticket.value = resTicket.data;

    // Fetch ticket logs
    const resLogs = await axios.get(`/tickets/${props.ticketId}/logs`);
    logs.value = resLogs.data;

    // Fetch all users
    const resUsers = await axios.get(`/users`);
    users.value = resUsers.data;

    // Map logs to include user name
    enrichedLogs.value = logs.value.map(log => {
      const user = users.value.find(u => u.id === log.user_id);
      return {
        ...log,
        user_name: user ? user.name : null,
        ticket_number: ticket.value.ticket_number
      };
    });

    console.log("Enriched logs:", enrichedLogs.value);

  } catch (error) {
    console.error('Failed to fetch ticket logs or users:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchLogs);

// Helper to format date
const formatDate = (iso) => {
  return new Date(iso).toLocaleString();
};

const emit = defineEmits(["close", "deleted"]);

//delete ticket
const deleteTicket = async () => {
  if (!confirm("Deleting a ticket also delete its entry in the queue, if applicable.\nAre you sure you want to delete this ticket?")) return;

  try {
    await axios.delete(`queues/by-ticket/${props.ticketId}`);
    await axios.delete(`/tickets/${props.ticketId}`);
    alert("Ticket deleted.");
    // notify parent to refresh list
    emit("deleted");
    // close modal
    emit("close");

    // refresh page
            window.location.href = `/dashboard/tickets`;

  } catch (error) {
    console.error("Delete failed:", error);
    alert("Failed to delete ticket.");
  }
};




</script>
