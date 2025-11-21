<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6">

      <!-- Modal Title -->
      <h2 class="text-xl font-semibold mb-4">
        Ticket Logs
      </h2>

      <!-- Modal Body -->
      <div class="space-y-2 max-h-[400px] overflow-y-auto">
        <div v-for="log in logs" :key="log.id" class="border-b pb-2">
          <div class="text-gray-700"><strong>{{ log.user }}</strong> - {{ formatDate(log.created_at) }}</div>
          <div class="text-gray-500 text-sm">{{ log.action }}</div>
        </div>
        <div v-if="logs.length === 0" class="text-center text-gray-500">No logs found.</div>
      </div>

      <!-- Modal Footer -->
      <div class="flex justify-end mt-4">
        <button
          class="bg-[#003D5B] text-white px-4 py-2 rounded hover:bg-[#006873]"
          @click="$emit('close')"
        >
          Close
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch} from 'vue';

const props = defineProps({
  ticketId: [String, Number]
});
const emit = defineEmits(['close']);

const logs = ref([]);

const fetchLogs = async () => {
  if (!props.ticketId) return;
  try {
    const res = await axios.get(`/tickets/${props.ticketId}/logs`);
    logs.value = res.data;
  } catch (err) {
    console.error('Failed to fetch ticket logs:', err);
  }
};

watch(() => props.ticketId, fetchLogs, { immediate: true });

const formatDate = (iso) => {
  if (!iso) return 'N/A';
  return new Date(iso).toLocaleString();
};
</script>
