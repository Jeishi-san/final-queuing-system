<template>
    <div class="relative overflow-hidden min-h-screen flex flex-col">
        <header class="w-full flex items-center justify-between p-4 bg-white shadow-md">
            
            <div class="flex items-center justify-center">
                <!-- icon -->
                <img :src="icon" alt="App Icon" class="w-20 h-20 ml-6 mr-3" />
            
                <!-- System Title -->
                <h2 class="text-2xl font-bold text-[#003D5B]"
                > {{ $systemName }}
                </h2>
            </div>

            <!-- Date and Time -->
            <div class="flex flex-col items-center space-y-2 mr-6 text-[#003D5B]">
                <Clock />
                <Date />
            </div>  
        </header>

        <div class="flex w-full px-10 py-15">
            <!-- LEFT SIDE -->
            <div class="flex flex-col w-full space-y-10">
                <InProgress
                    style_div="w-[95%] h-65 bg-white rounded-3xl items-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)]"
                    style_h3="text-3xl font-bold text-[#003D5B]"
                    style_h1="text-8xl font-bold text-[#003D5B] my-5"
                    style_p="text-base text-gray-700"/> <!-- In Progress Ticket Card 1 -->
                <InProgress
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
                />
            
            </transition>

            <!-- Floating Button -->
            <!-- Clicked: url have ./addding-ticket -->
            <button
                v-if="!showAddTicket"
                @click="showAddTicket = !showAddTicket"
                class="fixed top-32 right-0 bg-white text-[#003D5B] p-1 px-2 rounded-tl-xl rounded-bl-xl shadow-lg hover:text-white hover:bg-[#029cda] transition"
            >
                <span class="">
                    <FontAwesomeIcon :icon="['fas', 'arrow-left-long']" />
                </span>
            </button>

            <!-- Ticket Submitted Modal -->
            <TicketSubmitted v-if="ticketSubmitted"/>
        </aside>

        <!-- back button here, Don't show when isAdminSignedIn-->
        <router-link to="/dashboard">
            <button
                v-if="$globalState.loginSuccess"
                @click="$emit('prev_page')"
                class="fixed bottom-5 left-5 text-white p-1 px-2 shadow-xl rounded-2xl hover:bg-[#029cda] transition"
            >
                <span class="font-bold text-xl">
                    <FontAwesomeIcon :icon="['fas', 'arrow-left-long']" />
                </span>
            </button>
        </router-link>
    </div>
</template>

<script setup>
    import { ref, onBeforeUnmount } from 'vue';

    import Clock from './tools/Clock.vue';
    import Date from './tools/Date.vue';
    import InProgress from './cards/InProgress.vue';
    import NextInLine from './cards/NextInLine.vue';
    import AddTicket from './AddTicket.vue';
    import TicketSubmitted from './tools/TicketSubmitted.vue';

    import icon from '../../assets/login-icon.png';    

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
    


    
</script>