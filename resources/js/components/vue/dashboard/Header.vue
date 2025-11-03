<script setup>
    import { ref, computed } from "vue";

    import Date from '../tools/Date.vue';

    import prof from '../../../../assets/img/prof.jpg';

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
            <button
                v-if="!isSearching"
                @click="toggleSearch"
                class="hover:text-gray-300 transition-colors cursor-pointer">
                <FontAwesomeIcon :icon="['fas', 'search']" class="text-white text-xl"/>
            </button>

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

            <!-- Profile Icon -->
            <a href="/dashboard/my-profile" class="">
                <img
                    :src="prof"
                    alt="Profile Icon"
                    class="w-10 h-10 rounded-full border-2 border-white hover:opacity-80 transition"
                />
            </a>
        </div>

    </header>
</template>
