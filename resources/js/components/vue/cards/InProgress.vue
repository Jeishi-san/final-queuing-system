<template>
    <div :class="['flex flex-col text-center p-5', style_div]">
        <!-- w-[95%] h-65 bg-white rounded-3xl shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)] -->
                
        <h3 :class="[style_h3]">{{ title }}</h3>
        <!-- text-3xl font-bold text-[#003D5B] -->

        <!-- Animate dynamic content -->
        <div class="relative overflow-hidden">
            <transition name="slide-y" mode="out-in">
                <h1 :key="queueNum" :class="[style_h1]">
                {{ queueNum }} <!-- data from queue list entry -->
                </h1>
                <!-- text-8xl font-bold text-[#003D5B] my-5 -->
            </transition>
        </div>
        
        <!-- Ticket ID -->
        <div class="relative overflow-hidden mt-1">
        <transition name="slide-y" mode="out-in">
            <p :key="ticketId" :class="[style_p]">
            Ticket ID:
            <span class="font-medium">{{ ticketId }}</span> <!-- data from ticket list entry -->
            </p>
            <!-- text-base text-gray-700 -->
        </transition>
        </div>

        <div class="relative overflow-hidden">
        <transition name="slide-y" mode="out-in">
            <p :key="itStaff" :class="[style_p]">
            Attended by:
            <span class="font-medium">{{ itStaff }}</span> <!-- data from the one who started the ticket -->
            </p>
            <!-- text-base text-gray-700 -->
        </transition>
        </div>

    </div>
</template>

<script setup>
    defineProps({
        title: {
            type: String,
            default: "In Progress",
        },
        queueNum: {
            type: String,
            default: "DAM073010",
        },
        ticketId: {
            type: String,
            default: "INC000012496537",
        },
        itStaff: {
            type: String,
            default: "Randy Damos",
        },
        style_div: {
            type: [String, Array, Object],
            default: "",
        },
        style_h3: {
            type: [String, Array, Object],
            default: "",
        },
        style_h1: {
            type: [String, Array, Object],
            default: "",
        },
        style_p: {
            type: [String, Array, Object],
            default: "",
        },
    });
</script>

<style scoped>
/* Slide upward animation */
.slide-y-enter-active,
.slide-y-leave-active {
  transition: all 0.4s ease;
  display: inline-block;
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