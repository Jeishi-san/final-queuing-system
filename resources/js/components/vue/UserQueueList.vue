<template>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                flex flex-col items-center p-5 bg-white text-center
                shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)] rounded-2xl
                xs:w-full lg:w-[25%]"
    >

        <h3 class="w-full text-2xl font-bold text-[#003D5B] mb-4">
            My Queue List
        </h3>

        <div class="flex w-full border-b border-gray-300">
            <button
                class="w-1/2 py-2 text-sm font-semibold transition border-b-2"
                :class="activeTab === 'queued'
                    ? 'border-[#003D5B] text-[#003D5B]'
                    : 'border-transparent text-gray-500'"
                @click="activeTab = 'queued'"
            >
                Queued Tickets
            </button>

            <button
                class="w-1/2 py-2 text-sm font-semibold transition border-b-2"
                :class="activeTab === 'my'
                    ? 'border-[#003D5B] text-[#003D5B]'
                    : 'border-transparent text-gray-500'"
                @click="activeTab = 'my'"
            >
                My Submitted Tickets
            </button>
        </div>

        <div class="w-full mt-4 text-left max-h-64 overflow-y-auto">
            
            <div v-if="loading" class="text-center py-4 text-gray-500">
                <span class="animate-pulse">Loading...</span>
            </div>

            <div v-else-if="activeTab === 'queued'">
                <div v-if="queuedTickets.length === 0" class="text-center text-gray-400 py-4 text-sm">
                    No tickets in queue.
                </div>
                <table v-else class="w-full text-sm">
                    <thead class="text-[#003D5B] font-semibold sticky top-0 bg-white">
                        <tr>
                            <th class="pb-2 text-center">Queue #</th>
                            <th class="pb-2 text-center">Ticket #</th>
                            <th class="pb-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in queuedTickets" :key="i" class="text-gray-700 border-b border-gray-100 last:border-0 text-center">
                            <td class="py-2">{{ item.queue_number }}</td>
                            <td class="py-2">{{ item.ticket ? item.ticket.ticket_number : 'N/A' }}</td>
                            <td class="py-2">
                                <span 
                                    class="px-2 py-0.5 rounded-full text-xs font-medium uppercase"
                                    
                                    :class="getStatusClasses(item.ticket ? item.ticket.status : 'default')"
                                >
                                    {{ item.ticket ? item.ticket.status : 'Unknown' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else>
                <div v-if="myTickets.length === 0" class="text-center text-gray-400 py-4 text-sm">
                    You have not submitted any tickets.
                </div>
                <table v-else class="w-full text-sm">
                    <thead class="text-[#003D5B] font-semibold sticky top-0 bg-white">
                        <tr>
                            <th class="pb-2 text-center">Ticket #</th>
                            <th class="pb-2 text-center">Status</th>
                            <th class="pb-2 text-center">Date Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in myTickets" :key="i" class="text-gray-700 border-b border-gray-100 last:border-0 text-center">
                            
                            <td class="py-2 font-medium">{{ item.ticket_number }}</td>
                            
                            <td class="py-2">
                                <span 
                                    class="px-2 py-0.5 rounded-full text-xs font-medium uppercase"
                                    :class="getStatusClasses(item.status)"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                            
                            <td class="py-2 text-xs text-gray-500">
                                {{ new Date(item.created_at).toLocaleDateString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue'
    import axios from 'axios';

    const activeTab = ref('queued')
    const loading = ref(false);
    
    // Data containers
    const queuedTickets = ref([]);
    const myTickets = ref([]); 

    // Helper function to return CSS classes based on status
    const getStatusClasses = (status) => {
        switch (status) {
            case 'queued':
            case 'pending approval':
                return 'bg-yellow-100 text-yellow-800';
            case 'in progress':
                return 'bg-blue-100 text-blue-800';
            case 'resolved':
                return 'bg-green-100 text-green-800';
            case 'on hold':
            case 'cancelled':
            case 'dequeued':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    // Fetch Data on Mount
    onMounted(async () => {
        loading.value = true;
        try {
            // 1. Fetch Waiting/Queued Tickets (Global Queue)
            const queueRes = await axios.get('/queues/waiting'); 
            queuedTickets.value = queueRes.data;

            // 2. Fetch My Submitted Tickets
            const myTicketsRes = await axios.get('/agent/submitted-tickets');
            myTickets.value = myTicketsRes.data; 

        } catch (error) {
            console.error("Failed to load user queue lists:", error);
        } finally {
            loading.value = false;
        }
    });
</script>