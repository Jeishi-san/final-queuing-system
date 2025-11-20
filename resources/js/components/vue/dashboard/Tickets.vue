<template>
  <div class="flex flex-col h-[calc(100vh-100px)] w-full justify-start items-center px-10 py-5 box-border">

    <!-- Table Container -->
    <div class="w-full max-w-[1100px] flex flex-col shadow-lg rounded-3xl overflow-hidden">

      <!-- Table Header -->
      <div class="grid grid-cols-[150px_120px_200px_150px_160px_90px] bg-gray-200 p-3">
        <div :class="style_header">Ticket Number</div>
        <div :class="style_header">Holder Name</div>
        <div :class="style_header">Email</div>
        <div :class="style_header">Issue</div>
        <div :class="style_header">Date Added</div>
        <div :class="style_header">Status</div>
      </div>

      <!-- Table Rows -->
      <div class="bg-[#99bbc4] overflow-y-auto max-h-[calc(100vh-160px)]">
        <div
            v-for="ticket in ticketList"
            :key="ticket.id"
            :class="[
                'grid grid-cols-[150px_120px_200px_150px_160px_90px] border-b p-3 hover:bg-gray-100 cursor-pointer',
                ticket.id == highlightId ? 'bg-[#029cda]' : 'hover:bg-gray-100']"

            ref="ticketRows"
            @click="openModal(ticket)"
        >
          <div>{{ ticket.ticket_number }}</div>
          <div>{{ ticket.holder_name }}</div>
          <div>{{ ticket.holder_email }}</div>
          <div>{{ ticket.issue }}</div>
          <div>{{ formatDate(ticket.created_at) }}</div>

          <!-- Status inline dropdown -->
          <div @click.stop>
            <select
                v-model="ticket.status"
                @change="updateStatus(ticket)"
                class="bg-white px-2 py-1 rounded border"
            >
                <option
                    v-for="status in allStatuses"
                    :key="status"
                    :value="status"
                    :disabled="!isAllowedStatus(ticket.status, status)"
                >
                    {{ status }}
                </option>
            </select>

          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="text-center py-4 text-gray-700">Loading...</div>

        <!-- No data state -->
        <div v-if="!loading && ticketList.length === 0" class="text-center py-4 text-gray-700">No tickets found.</div>
      </div>

    </div>

    <!-- Modal for full edit -->
    <TicketModal v-if="selectedTicket" @close="selectedTicket = null">
      <template #title>Edit Ticket</template>
      <template #body>
        <div class="flex flex-col space-y-2">
          <label>Holder Name</label>
          <input v-model="selectedTicket.holder_name" class="border rounded px-2 py-1" />

          <label>Holder Email</label>
          <input v-model="selectedTicket.holder_email" class="border rounded px-2 py-1" />

          <label>Issue Type</label>
          <input v-model="selectedTicket.issue" class="border rounded px-2 py-1" />
        </div>
      </template>
      <template #footer>
        <button
          class="bg-[rgb(0,122,135)] text-white px-4 py-2 rounded hover:bg-[#006873]"
          @click="saveTicket(selectedTicket)"
        >
          Save
        </button>
      </template>
    </TicketModal>

  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import TicketModal from './UpdateTicket.vue';

const style_header = "font-semibold text-[#003D5B]";
const ticketList = ref([]);
const loading = ref(true);
const selectedTicket = ref(null);

const highlightId = ref(null);

const allStatuses = [
  'pending approval',
  'queued',
  'in progress',
  'on hold',
  'resolved',
  'cancelled'
];

const allowedNext = {
  'pending approval': ['queued', 'cancelled'],
  'queued': ['in progress', 'cancelled'],
  'in progress': ['resolved', 'on hold', 'cancelled'],
  'on hold': ['in progress', 'cancelled'],
  'resolved': [],
  'cancelled': []
};

// Logic: current is always valid; next allowed statuses are valid
const isAllowedStatus = (current, target) => {
  if (current === target) return true; // Always allow current
  return allowedNext[current]?.includes(target);
};


// Fetch tickets
const fetchTickets = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/tickets');
    ticketList.value = res.data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

// Inline status update
const updateStatus = async (ticket) => {
  try {
    await axios.put(`/tickets/${ticket.id}`, { status: ticket.status });
  } catch (error) {
    console.error('Failed to update status:', error);
  }
};

// Open modal for full edit
const openModal = (ticket) => {
  selectedTicket.value = { ...ticket }; // clone to avoid immediate table change
};

// Save from modal
const saveTicket = async (ticket) => {
  try {
    await axios.put(`/tickets/${ticket.id}`, ticket);
    const idx = ticketList.value.findIndex(t => t.id === ticket.id);
    if (idx !== -1) ticketList.value[idx] = { ...ticket };
    selectedTicket.value = null;
  } catch (error) {
    console.error('Failed to save ticket:', error);
  }
};

onMounted(fetchTickets);

onMounted(async () => {
  // read highlight param
  const params = new URLSearchParams(window.location.search);
  highlightId.value = params.get('highlight');

  // fetch tickets
  const res = await axios.get('/tickets');
  ticketList.value = res.data;

  // wait for DOM to update
  await nextTick();

  // scroll to highlighted ticket
  if (highlightId.value) {
    const target = document.querySelector('.bg-yellow-300');
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }
});

    // Date format
    const formatDate = (iso) => {
        if (!iso) return "N/A";
        return new Date(iso).toLocaleString();
    };
</script>
