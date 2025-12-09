<script setup>
    import { ref, onBeforeUnmount } from 'vue';

    import Clock from './tools/Clock.vue';
    import Date from './tools/Date.vue';
    import InProgress from './cards/InProgress.vue';
    import NextInLine from './cards//NextInLine.vue';
    import AddTicket from './AddTicket.vue';
    import TicketSubmitted from './tools/TicketSubmitted.vue';
    import FailedTicketSubmission from './tools/UnsuccessfulTicketSubmission.vue';

    import icon from '../../../assets/img/login-icon.png';

    const loginSuccess = ref(true);

    const showAddTicket = ref(false);
    const ticketSubmitted = ref(false);
    const failedTicketSubmitted = ref(false);

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

    function handleFailedTicketSubmission() {
        showAddTicket.value = false;

        // clear any existing timers
        clearTimeout(showTimer);
        clearTimeout(hideTimer);

        // show failed after 1 ms
        showTimer = setTimeout(() => {
            failedTicketSubmitted.value = true;

            // hide failed after 2 seconds
            hideTimer = setTimeout(() => {
                failedTicketSubmitted.value = false
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

    const style_div = "w-[95%] h-[50%] bg-white rounded-3xl items-center justify-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)] "
                    +"xs:w-full";
    const style_h3 = "text-3xl font-bold text-[#003D5B] ";
    const style_h1 = "font-bold text-[#003D5B] my-5 "
                    +"xs:text-5xl md:text-8xl";
    const style_p = "text-base text-gray-700";
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
                <Date/>
                <Clock/>
            </div>
        </header>
        <a href="/">
            <button
                @click="$emit('prev_page')"
                class=" text-white mt-5 p-1 px-2 hover:text-[#029cda] hover:bg-white rounded-[100%] transition"
            >
                <span class="font-bold text-xl">
                    <FontAwesomeIcon :icon="['fas', 'house']" />
                </span>
            </button>
        </a>

        <div class="px-10 py-14 pt-7
                    xs:w-full xs:space-y-10
                    xl:flex xl:space-x-10 xl:space-y-0">
            <!-- LEFT SIDE -->
            <div class="flex flex-col w-full space-y-10">
                <InProgress
                    :queueNum="inProgress[0]?.queue_number?? 'none'"
                    :ticketId="inProgress[0]?.ticket.ticket_number?? 'none'"
                    :itStaff="inProgress[0]?.assigned_user.name?? 'none'"
                    :style_div="style_div"
                    :style_h3="style_h3"
                    :style_h1="style_h1"
                    :style_p="style_p"/> <!-- In Progress Ticket Card 1 -->
                <InProgress
                    :queueNum="inProgress[1]?.queue_number?? 'none'"
                    :ticketId="inProgress[1]?.ticket.ticket_number?? 'none'"
                    :itStaff="inProgress[1]?.assigned_user.name?? 'none'"
                    :style_div="style_div"
                    :style_h3="style_h3"
                    :style_h1="style_h1"
                    :style_p="style_p"/> <!-- In Progress Ticket Card 2 -->
            </div>

            <!-- RIGHT SIDE -->
            <div class="w-full flex">
                <NextInLine/> <!-- Next in Line Tickets Card -->
            </div>
        </div>

        <aside> <!-- Don't show when isAdminSignedIn -->
            <!-- Backdrop -->
            <transition
                enter-active-class="transition-opacity duration-500"
                leave-active-class="transition-opacity duration-500"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showAddTicket"
                    @click="showAddTicket = false"
                    class="fixed inset-0 bg-black/30 transition-opacity backdrop-blur-sm z-0"
                ></div>
            </transition>

            <!-- Slide Transition Wrapper -->
            <transition
                name="slide"
                enter-active-class="transform transition duration-500"
                enter-from-class="translate-x-full opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transform transition duration-500"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
            >

                <!-- side panel for Add Ticket Component -->
                <AddTicket
                    v-if="showAddTicket"
                    @submitted="handleTicketSubmitted"
                    @failed="handleFailedTicketSubmission"
                />

            </transition>

            <!-- Floating Button -->
            <!-- Clicked: url have ./addding-ticket -->
            <button
                v-if="!showAddTicket"
                @click="showAddTicket = !showAddTicket"
                class="fixed right-0 bg-white text-[#003D5B] p-1 px-2 rounded-tl-xl rounded-bl-xl shadow-lg hover:text-white hover:bg-[#029cda] transition
                        xs:top-[203px]
                        md:top-[135px]"
            >
                <span class="">
                    <FontAwesomeIcon :icon="['fas', 'arrow-left-long']" />
                </span>
            </button>

            <!-- Close button when screen is xs and md -->
            <button
                v-if="showAddTicket"
                @click="showAddTicket = !showAddTicket"
                class="lg:hidden fixed top-10 right-5 bg-white text-[#003D5B] border border-[#003D5B]  p-1 px-2 rounded-xl shadow-lg hover:text-white hover:bg-[#029cda] hover:border-none transition"
            >
                <span class="">Close</span>
            </button>

            <!-- Ticket Submitted Modals -->
            <TicketSubmitted v-if="ticketSubmitted"/>
            <FailedTicketSubmission v-if="failedTicketSubmitted"/>
        </aside>
    </div>
</template>
