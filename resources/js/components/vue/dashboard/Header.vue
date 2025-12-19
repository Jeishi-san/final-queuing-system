<script setup>
    import { ref, computed, onMounted, onUnmounted } from "vue";
    import DateComponent from '../tools/Date.vue'; // Renamed to avoid conflict with native Date
    //import axios from 'axios'; //nganong gi-add nasad ni? it was told before na no need nani kay naka global access nani through bootstrap.js


    const props = defineProps({
        pageName: {
            type: String,
            default: "Dashboard",
        },
    });

    const isQueuePage = ref(false);
    const isTicketsPage = ref(false);
    const pageName = computed(() => props.pageName);

    // Search Logic
    const isSearching = ref(false);
    const searchQuery = ref("");
    const toggleSearch = () => {
        isSearching.value = !isSearching.value;
        searchQuery.value = "";
    };

    // Transition Logic
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

    // Filter & Manage Logic
    const emit = defineEmits(['isFilterClicked', 'isManageClicked']);
    const isFilterClicked = ref(false);
    const isManageClicked = ref(false);

    const filterON = () => { emit('isFilterClicked', true); isFilterClicked.value = true; };
    const filterOFF = () => { emit('isFilterClicked', false); isFilterClicked.value = false; };
    const manageON = () => { emit('isManageClicked', true); isManageClicked.value = true; };
    const manageOFF = () => { emit('isManageClicked', false); isManageClicked.value = false; };

    const getPageName = () =>  {
        if (pageName.value == "Queue") {
            isQueuePage.value = true;
        } else if (pageName.value === "Tickets") {
            isTicketsPage.value = true;
        } else {
            isQueuePage.value = false;
            isTicketsPage.value = false;
        }
    };

    // --- User Logic ---
    const user = ref({});
    // Use Vite to resolve default avatar from resources/assets
    const defaultAvatar = new URL('../../../../assets/img/profile.png', import.meta.url).href;
    const getProfileImage = (path) => {
        if (!path) return defaultAvatar;
        if (path.startsWith('http') || path.startsWith('data:')) return path;
        return `/storage/${path}`;
    };

    const fetchUserProfile = async () => {
        try {
            const response = await axios.get('/api/user/profile');
            user.value = response.data;
        } catch (error) {
            console.error("Error fetching profile", error);
        }
    };

    // --- Notification Panel Logic ---
    const unreadCount = ref(0);
    const notifications = ref([]);
    const showNotifications = ref(false);
    const loadingNotifications = ref(false);
    let pollingInterval = null;

    const fetchUnreadCount = async () => {
        try {
            const response = await axios.get('/api/notifications/unread-count');
            unreadCount.value = response.data.count;
        } catch (error) {
            console.error("Error fetching count", error);
        }
    };

    const toggleNotifications = async () => {
        showNotifications.value = !showNotifications.value;
        if (showNotifications.value) {
            await fetchNotifications();
        }
    };

    const fetchNotifications = async () => {
        loadingNotifications.value = true;
        try {
            const response = await axios.get('/api/notifications?page=1');
            notifications.value = response.data.data.slice(0, 5);

            if(response.data.unread_count !== undefined) {
                unreadCount.value = response.data.unread_count;
            }
        } catch (error) {
            console.error("Error fetching list", error);
        } finally {
            loadingNotifications.value = false;
        }
    };

    const markAsRead = async (id) => {
        try {
            await axios.post(`/api/notifications/${id}/read`);
            const notif = notifications.value.find(n => n.id === id);
            if(notif) notif.read_at = new Date().toISOString();

            if(unreadCount.value > 0) unreadCount.value--;
        } catch (error) {
            console.error("Error marking read", error);
        }
    };

    // ✅ FIXED: Uses standard JavaScript Date instead of Moment.js
    const formatTime = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    onMounted(() => {
        getPageName();
        fetchUserProfile();
        fetchUnreadCount();
        pollingInterval = setInterval(fetchUnreadCount, 60000);
    });

    onUnmounted(() => {
        if (pollingInterval) clearInterval(pollingInterval);
    });

</script>

<template>
    <header class="h-30 flex items-center justify-between px-10 pt-10 transition-all duration-300 relative z-20">

        <div v-if="!isSearching" class="flex items-end space-x-8">
            <h1 class="text-3xl font-semibold text-white">{{ pageName }}</h1>
            <DateComponent class="text-[16px] text-gray-200 font-normal"></DateComponent>
        </div>

        <div v-if="isSearching">
            <h1 class="text-3xl font-semibold text-white">Searching...</h1>
        </div>

        <div class="flex items-center space-x-4 relative">

            <div class="relative">
                <button
                    @click="toggleNotifications"
                    class="relative group mr-2 p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors focus:outline-none"
                >
                    <FontAwesomeIcon :icon="['fas', 'bell']" class="text-white text-xl" />
                    <span
                        v-if="unreadCount > 0"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-[#003D5B]"
                    >
                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                    </span>
                </button>

                <div
                    v-if="showNotifications"
                    @click="showNotifications = false"
                    class="fixed inset-0 z-10 cursor-default"
                ></div>

                <transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                >
                    <div
                        v-if="showNotifications"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl overflow-hidden z-20 border border-gray-200"
                    >
                        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 class="font-semibold text-gray-700">Notifications</h3>
                            <span v-if="unreadCount > 0" class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">{{ unreadCount }} new</span>
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            <div v-if="loadingNotifications" class="p-4 text-center text-gray-500">
                                <FontAwesomeIcon :icon="['fas', 'spinner']" spin /> Loading...
                            </div>

                            <div v-else-if="notifications.length === 0" class="p-4 text-center text-gray-500 text-sm">
                                No notifications found.
                            </div>

                            <div v-else>
                                <div
                                    v-for="notif in notifications"
                                    :key="notif.id"
                                    class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors flex items-start gap-3 cursor-pointer"
                                    :class="{ 'bg-blue-50/50': !notif.read_at }"
                                    @click="markAsRead(notif.id)"
                                >
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 line-clamp-2">{{ notif.message }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ formatTime(notif.created_at) }}</p>
                                    </div>
                                    <div v-if="!notif.read_at" class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 text-center border-t border-gray-100">
                            <a href="/dashboard/notifications" class="block w-full py-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </transition>
            </div>

            <transition @enter="onEnter" @leave="onLeave">
                <div v-if="isSearching" class="flex items-center space-x-2 absolute right-32 bg-white/75 backdrop-blur-md px-3 py-1 rounded-full z-10">
                    <FontAwesomeIcon :icon="['fas', 'search']" class="text-[#003D5B]"/>
                    <input type="text" v-model="searchQuery" placeholder="Search..." class="w-230 py-1 text-[#003D5B] rounded-md focus:outline-none"/>
                    <button @click="toggleSearch" class="hover:text-gray-300 transition-colors">
                        <FontAwesomeIcon :icon="['fas', 'times']" class="text-[#003D5B] text-lg"/>
                    </button>
                </div>
            </transition>

            <button v-if="isSearching" @click="toggleSearch" class="hover:text-gray-300 transition-colors cursor-pointer">
                <FontAwesomeIcon :icon="['fas', 'search']" class="text-white text-xl"/>
            </button>

            <button v-if="!isFilterClicked && (isQueuePage || isTicketsPage) " @click="filterON" class="px-2 py-1 rounded bg-white text-[#003D5B] font-medium text-sm">Filter</button>
            <button v-if="isFilterClicked" @click="filterOFF" class="px-2 py-1 rounded bg-red-500 text-white font-medium text-sm">Close</button>

            <button v-if="!isManageClicked && isQueuePage" @click="manageON" class="px-2 py-1 rounded bg-white text-[#003D5B] font-medium text-sm">Manage</button>
            <button v-if="isManageClicked" @click="manageOFF" class="px-2 py-1 rounded bg-green-500 text-white font-medium text-sm">Done</button>

            <a href="/dashboard/my-profile" class="block relative" title="View Profile">
                <img
                    v-if="getProfileImage(user.profile?.image || user.image)"
                    :src="getProfileImage(user.profile?.image || user.image)"
                    alt="User Profile"
                    class="w-10 h-10 rounded-full border-2 border-white object-cover hover:opacity-90 transition"
                />

                <svg
                    v-else
                    class="w-10 h-10 rounded-full border-2 border-white hover:opacity-80 transition bg-white"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 640 640">
                    <rect width="640" height="640" fill="white" rx="50" />
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
