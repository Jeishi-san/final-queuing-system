<script setup>
    import { ref, computed } from "vue";

    import Date from '../tools/Date.vue';

    // import prof from '../../../../assets/img/user-icon.svg';

    const props = defineProps({
        pageName: {
            type: String,
            default: "Dashboard",
        },
    });

    const pageName = computed(() => props.pageName);

    const isSearching = ref(false);
    const searchQuery = ref("");

    const toggleSearch = () => {
        isSearching.value = !isSearching.value;
        searchQuery.value = "";
    };

    // Inline transition behavior
    const onEnter = (el) => {
        el.style.opacity = 0;
        el.style.transform = "translateX(1rem)";
        requestAnimationFrame(() => {
            el.style.transition = "all 0.2s ease-in-out";
            el.style.opacity = 1;
            el.style.transform = "translateX(0)";
     });
    };

    const onLeave = (el) => {
        el.style.transition = "all 0.2s ease-in-out";
        el.style.opacity = 1;
        el.style.transform = "translateX(0)";
        requestAnimationFrame(() => {
            el.style.opacity = 0;
            el.style.transform = "translateX(1rem)";
        });
    };

    const emit = defineEmits(['isFilterClicked']);

    const isFilterClicked = ref(false);

    const filterON = () => {
        emit('isFilterClicked', true);
        isFilterClicked.value = true;
    };

    const filterOFF = () => {
        emit('isFilterClicked', false);
        isFilterClicked.value = false;
    };
</script>

<template>
    <header class="h-30 flex items-center justify-between px-10 pt-10 transition-all duration-300">

        <!-- Left -->
        <div v-if="!isSearching" class="flex items-end space-x-8">
            <h1 class="text-3xl font-semibold text-white">{{ pageName }}</h1>
            <Date class="text-[16px] text-gray-200 font-normal"></Date>
        </div>

        <div v-if="isSearching">
            <h1 class="text-3xl font-semibold text-white">Searching...</h1>
        </div>

        <!-- Right -->
        <div class="flex items-center space-x-3 relative">
            <!-- Search Icon -->
            <!-- <button
                v-if="!isSearching"
                @click="toggleSearch"
                class="hover:text-gray-300 transition-colors cursor-pointer">
                <FontAwesomeIcon :icon="['fas', 'search']" class="text-white text-xl"/>
            </button> -->

            <!-- Search Bar -->
            <transition
                @enter="onEnter"
                @leave="onLeave"
            >
                <div
                    v-if="isSearching"
                    class="flex items-center space-x-2 absolute right-15 bg-white/75 backdrop-blur-md px-3 py-1 rounded-full"
                >
                    <FontAwesomeIcon :icon="['fas', 'search']" class="text-[#003D5B]"/>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search..."
                        class="w-230 py-1 text-[#003D5B] rounded-md focus:outline-none"
                    />
                    <button
                        @click="toggleSearch"
                        class="hover:text-gray-300 transition-colors"
                    >
                        <FontAwesomeIcon :icon="['fas', 'times']" class="text-[#003D5B] text-lg"/>
                    </button>
                </div>
            </transition>

            <!-- Filter button -->
            <button v-if="!isFilterClicked" @click="filterON" class="px-2 py-1 rounded bg-white text-[#003D5B]">Filter</button>
            <button v-if="isFilterClicked" @click="filterOFF" class="px-2 py-1 rounded bg-red-500 text-white">Close Filter</button>


            <!-- Profile Icon -->
            <a href="/dashboard/my-profile" class="">
                <!-- <img
                    :src="prof"
                    alt="Profile Icon"

                /> -->
                <svg
                    class="w-10 h-10 rounded-full border-2 border-white hover:opacity-80 transition"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 640 640">
                    <!-- White background -->
                    <rect width="640" height="640" fill="white" rx="50" /> <!-- optional rx for rounded corners -->

                    <!-- Profile icon -->
                    <path
                        d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72
                        200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112
                        447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576
                        528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z"
                        fill="#003D5B"
                    />
                </svg>
            </a>
        </div>

    </header>
</template>
