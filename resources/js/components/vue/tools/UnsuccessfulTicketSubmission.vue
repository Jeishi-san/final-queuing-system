<template>
    <div>
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
                class="
                    fixed inset-0 bg-white/50 transition-opacity backdrop-blur-lg z-1
                    flex flex-col justify-center items-center space-y-5"
            >
                <!-- Check icon -->
                <span class="text-[#fd1b1b]">
                    <FontAwesomeIcon :icon="['fas', 'exclamation']" class="text-[250px]"/>
                </span>

                <p class="text-5xl font-bold text-[#fd1b1b]">Ticket Number Already Exists</p>
            </div>

        </transition>
    </div>
</template>

<script setup>

</script>
