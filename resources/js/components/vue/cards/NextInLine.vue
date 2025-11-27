<template>
    <div class="w-[95%] 100vh bg-white rounded-3xl flex flex-col items-center text-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)] p-5">

        <h3 class="text-3xl font-bold text-[#003D5B]">Waiting</h3>

        <div v-if="queueList.length == 0" class="mt-24 text-7xl font-bold text-[#003D5B]">
            Loading...
        </div>

        <div class="mt-5 space-y-6 text-7xl font-bold text-[#003D5B]">
            <h1 v-for="queue in queueList" :key="queue.id">
                {{ queue.queue_number }} <!-- replace with your column -->
            </h1>
        </div>

    </div>
</template>

<script setup>
    import { ref, onMounted } from "vue";

    const queueList = ref([]); // reactive array to store queue data

    // Fetch queue data from backend API when component mounts
    onMounted(async () => {
        try {
            const response = await axios.get('/queues'); // <-- your API endpoint
            queueList.value = response.data; // assume response.data is an array of tickets
            console.log(queueList.value);
        } catch (error) {
            console.error("Failed to fetch queue:", error);
        }
    });
</script>
