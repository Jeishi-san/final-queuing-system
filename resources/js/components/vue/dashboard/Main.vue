<template>
    <main class="flex-1 px-10 py-5 overflow-y-auto box-border">
        <!-- Top cards -->
        <div class="h-[35%] flex space-x-5 mb-[2rem]">
            <!-- In Progress Carousel -->
            <div class="w-[50%] overflow-hidden relative  bg-white rounded-3xl p-5">
                <h3 class="text-2xl font-bold text-[#003D5B] text-left mt-2">In Progress</h3>
                <div v-if="inProgressList.length > 0" class="flex flex-col justify-center items-center">
                    <!-- Animate Queue Number -->
                    <div class="relative overflow-hidden">
                        <transition name="slide-x" mode="out-in">
                        <h1 :key="currentCard.queue_number" class="text-7xl font-bold text-[#003D5B] my-2">
                            {{ currentCard.queue_number }}
                        </h1>
                        </transition>
                    </div>
                    <!-- Animate Ticket Number span -->
                    <p class="text-base text-gray-700">
                        Ticket Number:
                        <span class="relative overflow-hidden">
                        <transition name="slide-y" mode="out-in">
                            <span :key="currentCard.ticket.ticket_number" class="font-medium">
                            {{ currentCard.ticket.ticket_number }}
                            </span>
                        </transition>
                        </span>
                    </p>

                    <!-- Animate IT Staff span -->
                    <p class="text-base text-gray-700">
                        Attended by:
                        <span class="relative overflow-hidden">
                        <transition name="slide-y" mode="out-in">
                            <span :key="currentCard.assigned_user.name" class="font-medium">
                            {{ currentCard.assigned_user.name }}
                            </span>
                        </transition>
                        </span>
                    </p>
                </div>

                <!-- Dots navigation -->
                <div class="absolute bottom-3 left-1/2 transform -translate-x-1/2 flex space-x-2">
                    <button
                        v-for="(item, i) in inProgressList"
                        :key="item.ticket_id"
                        @click="setIndex(i)"
                        class="w-2 h-2 rounded-full"
                        :class="i === currentIndex ? 'bg-[#003D5B]' : 'bg-gray-300'"
                        aria-label="'Go to slide ' + (i + 1)"
                    ></button>
                </div>
            </div>

            <!-- Total ticket in Queue today -->
            <div class="w-[50%] h-full bg-white rounded-3xl flex flex-col text-center justify center p-5">
                <h3 class="text-2xl font-bold text-[#003D5B] text-left mt-2">Successfully Resolved Tickets</h3>
                <h1 class="text-[100px] font-bold text-[#003D5B]">{{resolvedTickets}}</h1> <!-- Total tickets in Queue List -->
            </div>
        </div>

        <!-- Waiting in Queue card -->
        <div class="h-[calc(65%_-_2rem)] bg-white rounded-3xl flex flex-col p-5 text-[#003D5B]">
            <div class="w-full bg-white rounded-3xl flex justify-between pb-5">
                <span class="text-lg font-semibold">Waiting in Queue: {{ waiting }}</span> <!-- Number of waiting in queue (inQueueToday - resolvedTickets) -->
                <a href="/queue">View Public Queue Display</a>
            </div>

            <!-- Table for Queue List in Waiting -->
            <div class="w-full">
                <!-- Table Header -->
                <div class="grid grid-cols-3 gap-4 bg-gray-200 p-3 rounded-t-2xl">
                    <div class="font-semibold text-[#003D5B]">Order</div>
                    <div class="font-semibold text-[#003D5B]">Ticket Number</div>
                    <div class="font-semibold text-[#003D5B]">Issue</div>
                </div>

                <!-- Table Rows: data must be fetch from db -->
                 <div class="max-h-64 overflow-y-auto rounded-b-2xl">
                    <div
                        v-for="queue in queueList"
                        :key="queue.id"
                        class="grid grid-cols-3 gap-4 border-b p-3 hover:bg-gray-100 cursor-pointer"
                        @click="goToTicket(queue.ticket?.id)"
                    >
                        <div>{{ queue.queue_number }}</div>
                        <div>{{ queue.ticket?.ticket_number}}</div>
                        <div>{{ queue.ticket?.issue}}</div>
                    </div>
                </div>
                <!-- More rows can be added similarly -->
            </div>

        </div>
    </main>
</template>

<script setup>
    import { ref, computed, onMounted, onUnmounted, watch } from "vue";

    import InProgress from '../cards/InProgress.vue';

    const resolvedTickets = ref(0);
    const waiting = ref(0); // Example value for waiting in queue

    const queueList = ref([]); // Queue list for waiting table

    // Simulated DB data
    const inProgressList = ref([]);

    const currentIndex = ref(0);
    const currentCard = computed(() => {
        return inProgressList.value.length ? inProgressList.value[currentIndex.value] : { ticket_id: "none" };
    });

    let intervalId = null;
    function setIndex(i) {
        currentIndex.value = i;
    }
    function startIntervalIfNeeded() {
        clearInterval(intervalId);
        if (inProgressList.value.length > 1) {
            intervalId = setInterval(() => {
            currentIndex.value = (currentIndex.value + 1) % inProgressList.value.length;
            }, 3000);
        }
    }

    const fetchQueuedTickets = async () => {
        try {
            const response = await axios.get('/tickets/queued');
            resolvedTickets.value = response.data.resolved_tickets;
            waiting.value = response.data.waiting;

            console.log("Resolved Total", resolvedTickets.value);
            console.log("Waiting", waiting.value);

            const response2 = await axios.get('/queues/waiting');
            queueList.value = response2.data;
            console.log("Waiting Queue Items", queueList.value);

            const response3 = await axios.get('/queues/list');
            inProgressList.value = response3.data.filter(item => item.ticket.status === "in progress");
            console.log("InProgress Items", inProgressList.value);
        } catch (error) {
            console.error("Failed to fetch tickets:", error);
        }
    };

    onMounted(() => {
        fetchQueuedTickets();
    });

    const goToTicket = (ticketId) => {
        if (!ticketId) return;
        window.location.href = `/dashboard/tickets?highlight=${ticketId}`;
    };

    onMounted(startIntervalIfNeeded);
    onUnmounted(() => clearInterval(intervalId));
    watch(inProgressList, startIntervalIfNeeded, { immediate: true });
</script>

<style scoped>
/* Slide horizontally for queue number */
.slide-x-enter-active,
.slide-x-leave-active {
  transition: all 0.4s ease;
}
.slide-x-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.slide-x-enter-to {
  transform: translateX(0);
  opacity: 1;
}
.slide-x-leave-from {
  transform: translateX(0);
  opacity: 1;
}
.slide-x-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

/* Slide vertically for spans */
.slide-y-enter-active,
.slide-y-leave-active {
  transition: all 0.3s ease;
}
.slide-y-enter-from {
  transform: translateY(100%);
  opacity: 0;
}
.slide-y-enter-to {
  transform: translateY(0);
  opacity: 1;
}
.slide-y-leave-from {
  transform: translateY(0);
  opacity: 1;
}
.slide-y-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>
