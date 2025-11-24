<script setup>
    import { ref, onBeforeUnmount } from 'vue';

    import Clock from './tools/Clock.vue';
    import Date from './tools/Date.vue';
    import InProgress from './cards/InProgress.vue';
    import NextInLine from './cards/NextInLine.vue';

    import icon from '../../../assets/img/login-icon.png';

    const systemName = 'IT Ops Queuing System';

    const showAddTicket = ref(false);
    const ticketSubmitted = ref(false);

    // keep timer ids to clear if component unmounts
    let showTimer = null;
    let hideTimer = null;

    function handleTicketSubmitted() {
        showAddTicket.value = false;

        // clear any existing timers
        clearTimeout(showTimer);
        clearTimeout(hideTimer);

        // show success after 1 ms
        showTimer = setTimeout(() => {
            ticketSubmitted.value = true;

            // hide success after 2 seconds
            hideTimer = setTimeout(() => {
                ticketSubmitted.value = false
            }, 2000)
        }, 1)
    }

    onBeforeUnmount(() => {
        clearTimeout(showTimer);
        clearTimeout(hideTimer);
    });

        import { onMounted } from "vue";

    const inProgress = ref([]); // reactive array to store inProgress data

    // Fetch inProgress data from backend when component mounts
    onMounted(async () => {
        try {
            const response = await axios.get('/queues/inProgress'); // <-- your API endpoint
            inProgress.value = response.data;
            console.log(inProgress.value);
        } catch (error) {
            console.error("Failed to fetch queue:", error);
        }
    });

</script>

<template>
    <div class="relative overflow-hidden min-h-screen flex flex-col">
        <header class="w-full flex items-center justify-between p-4 bg-white shadow-md">

            <div class="flex items-center justify-center">
                <!-- icon -->
                <img :src="icon" alt="App Icon" class="w-20 h-20 ml-6 mr-3" />

                <!-- System Title -->
                <h2 class="text-2xl font-bold text-[#003D5B]"
                > {{ systemName }}
                </h2>
            </div>

            <!-- Date and Time -->
            <div class="flex flex-col items-center space-y-2 mr-6 text-[#003D5B]">
                <Clock />
                <Date />
            </div>
        </header>

        <div class="flex w-full px-10 py-14">
            <!-- LEFT SIDE -->
            <div class="flex flex-col w-full space-y-10">
                <InProgress
                    :queueNum="inProgress[0]?.queue_number?? 'nothing yet'"
                    :ticketId="inProgress[0]?.ticket.ticket_number?? 'none'"
                    :itStaff="inProgress[0]?.assigned_user.name?? 'none'"
                    style_div="w-[95%] h-65 bg-white rounded-3xl items-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)]"
                    style_h3="text-3xl font-bold text-[#003D5B]"
                    style_h1="text-8xl font-bold text-[#003D5B] my-5"
                    style_p="text-base text-gray-700"/> <!-- In Progress Ticket Card 1 -->
                <InProgress
                    :queueNum="inProgress[1]?.queue_number?? 'nothing yet'"
                    :ticketId="inProgress[1]?.ticket.ticket_number?? 'none'"
                    :itStaff="inProgress[1]?.assigned_user.name?? 'none'"
                    style_div="w-[95%] h-65 bg-white rounded-3xl"
                    style_h3="text-3xl font-bold text-[#003D5B]"
                    style_h1="text-8xl font-bold text-[#003D5B] my-5"
                    style_p="text-base text-gray-700"/> <!-- In Progress Ticket Card 2 -->
            </div>

            <!-- RIGHT SIDE -->
            <div class="w-full flex justify-end">
                <NextInLine/> <!-- Next in Line Tickets Card -->
            </div>
        </div>

        <!-- back button here, Show when isAdminSignedIn-->
        <a href="/dashboard">
            <button
                @click="$emit('prev_page')"
                class="fixed bottom-5 left-5 text-white p-1 px-2 shadow-xl rounded-2xl hover:bg-[#029cda] transition"
            >
                <span class="font-bold text-xl">
                    <FontAwesomeIcon :icon="['fas', 'arrow-left-long']" />
                </span>
            </button>
        </a>
    </div>
</template>
