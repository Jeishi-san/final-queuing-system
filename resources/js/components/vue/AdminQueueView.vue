<script setup>
    import { ref, onBeforeUnmount } from 'vue';

    import Clock from './tools/Clock.vue';
    import Date from './tools/Date.vue';
    import InProgress from './cards/InProgress.vue';
    import NextInLine from './cards/NextInLine.vue';

    import icon from '../../../assets/img/login-icon.png';

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
    <div class="relative
                xs:flex xs:flex-col xs:items-center">
        <header class="p-4 bg-white shadow-md
                        xs:w-full xs:flex xs:flex-col xs:items-center
                        md:flex-row md:justify-between md:px-8">

            <div class="
                        xs:flex xs:flex-col xs:items-center xs:mb-2
                        md:flex-row md:justify-between md:mb-0">
                <!-- icon -->
                <img :src="icon" alt="App Icon" class="w-20 h-20" />

                <!-- System Title -->
                <h2 class="text-2xl font-bold text-[#003D5B]">CNX IT Ops enQ</h2>
            </div>

            <!-- Date and Time -->
            <div class="text-[#003D5B]
                        xs:flex xs:space-x-2
                        md:flex-col-reverse md:space-x-0 md:items-center">
                <Clock />
                <Date />
            </div>
        </header>

        <div class="px-10 py-14 pt-7
                    xs:w-full xs:space-y-10
                    xl:flex xl:space-x-10 xl:space-y-0">
            <!-- LEFT SIDE -->
            <div class="flex flex-col w-full space-y-10">
                <InProgress
                    :queueNum="inProgress[0]?.queue_number?? 'none'"
                    :ticketId="inProgress[0]?.ticket.ticket_number?? 'none'"
                    :itStaff="inProgress[0]?.assigned_user.name?? 'none'"
                    style_div="w-[95%] h-[50%] bg-white rounded-3xl items-center justify-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)]
                                xs:w-full"
                    style_h3="text-3xl font-bold text-[#003D5B]"
                    style_h1="font-bold text-[#003D5B] my-5
                                xs:text-5xl md:text-8xl"
                    style_p="text-base text-gray-700"/> <!-- In Progress Ticket Card 1 -->
                <InProgress
                    :queueNum="inProgress[1]?.queue_number?? 'none'"
                    :ticketId="inProgress[1]?.ticket.ticket_number?? 'none'"
                    :itStaff="inProgress[1]?.assigned_user.name?? 'none'"
                    style_div="w-[95%] h-[50%] bg-white rounded-3xl items-center justify-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)]
                                xs:w-full"
                    style_h3="text-3xl font-bold text-[#003D5B]"
                    style_h1="font-bold text-[#003D5B] my-5
                                xs:text-5xl md:text-8xl"
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
                class="fixed shadow-xl transition hover:bg-[#029cda]
                        xs:bottom-1/2 xs:left-[-40px] xs:pl-11 xs:py-5 xs:pr-1 xs:bg-white xs:text-[#003D5B] xs:hover:text-white xs:rounded-[100%]
                        xl:bottom-5 xl:left-5 xl:text-white xl:p-1 xl:px-2 xl:rounded-2xl xl:bg-transparent"
            >
                <span class="font-bold text-xl">
                    <FontAwesomeIcon :icon="['fas', 'arrow-left-long']" />
                </span>
            </button>
        </a>
    </div>
</template>
