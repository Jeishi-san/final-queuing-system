<template>
    <div class="w-[95%] 100vh bg-white rounded-3xl flex flex-col items-center text-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)] p-5
                xs:w-full ">

        <h3 class="text-3xl font-bold text-[#003D5B] mt-10 mb-5
                    xs:m-0">Waiting</h3>

        <div v-if="queueList.length == 0" class="mt-24 text-7xl font-bold text-[#003D5B]">
            Loading...
        </div>

        <div class="mt-4 space-y-2">
            <div v-for="queue in enrichedQueue" :key="queue.id" class="flex flex-col items-center">
                <h1 class="font-bold text-[#003D5B]
                            xs:text-5xl
                            md:text-8xl">
                    {{ queue.queue_number }}
                </h1>
                <p class="text-base text-gray-700 mt-1">Ticket Number:
                    <span class="font-medium">{{ queue.ticket_number }}</span>
                </p>
                <div class="h-[1px] bg-gray-300
                            xs:w-full xs:mt-2"></div>
            </div>
        </div>

    </div>
</template>

<script setup>
    import { ref, onMounted } from "vue";

    const queueList = ref([]); // reactive array to store queue data
    const enrichedQueue = ref([]); // reactive array to store enriched queue data
    const tickets = ref([]);         // all tickets (to get ticket_number)

    // Fetch queue data from backend API when component mounts
    onMounted(async () => {
        try {
            // Fetch queue list
            const resQueues = await axios.get('/queues/waiting'); // <-- your API endpoint
            queueList.value = resQueues.data;

            // Fetch tickets
            const resTickets = await axios.get('/tickets');
            tickets.value = resTickets.data;

            // Map logs to include ticket number
            enrichedQueue.value = queueList.value.map(queue => {
                const ticket = tickets.value.find(t => t.id === queue.ticket_id);

                return {
                    ...queue,
                    ticket_number: ticket ? ticket.ticket_number : null
                };
            });
            console.log(enrichedQueue.value);
        } catch (error) {
            console.error("Failed to fetch queue:", error);
        }
    });
</script>
