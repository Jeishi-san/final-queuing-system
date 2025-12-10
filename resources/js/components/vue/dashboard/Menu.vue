<script setup>
    import icon from '../../../../assets/img/login-icon.png';
    import { ref, onMounted } from "vue";

    const isSidebarOpen = ref(true);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleSidebar() {
        isSidebarOpen.value = !isSidebarOpen.value;
    }

    function logout() {
        document.getElementById('logout-form').submit();
    }

    onMounted(() => {
        // setTimeout(() => {
        //     isSidebarOpen.value = false;
        // }, 2000)
    });

    /* STYLES */
    const navLinkClass = "group flex items-center px-12 py-2 text-[#003D5B] hover:bg-gray-700 hover:text-white transition-colors";
    const iconClass = "mr-1 text-[#003D5B] text-xl group-hover:text-white";
</script>

<template>
    <aside :class="['bg-white text-[#003D5B] overflow-hidden',
            isSidebarOpen ? 'w-80 shadow-[0_10px_100px_50px_rgba(0,0,0,0.3)]' : 'w-13 shadow-none']">

        <!-- Sidebar Header -->
        <div :class="['flex flex-col p-4', isSidebarOpen ? '' : 'items-center']">

            <div class="flex justify-end">
                <button @click="toggleSidebar" class="focus:outline-none cursor-pointer">
                    <span class="text-[#003D5B] text-lg">
                        <FontAwesomeIcon :icon="['fas', 'bars']"/>
                    </span>
                </button>
            </div>

            <div class="flex items-center mt-2" v-if="isSidebarOpen">
                <img :src="icon" alt="Login Icon" class="w-30 h-30"/>
                <span class="text-3xl font-bold ml-2">
                    CNX IT Ops<br/>
                    enQ
                </span>
            </div>

        </div>

        <!-- Sidebar Menu Items -->
        <nav class="text-[#003D5B] font-medium" v-if="isSidebarOpen">
            <h3 class="px-10">MAIN</h3>
            <ul class="mb-6">
                <a href="/dashboard" :class="navLinkClass">
                    <span :class="iconClass">
                        <FontAwesomeIcon :icon="['fas', 'gauge']" />
                    </span>Dashboard</a>
                <a href="/dashboard/queue-list" :class="navLinkClass">
                    <span :class="iconClass">
                        <FontAwesomeIcon :icon="['fas', 'people-line']" />
                    </span>Queue List</a>
                <a href="/dashboard/tickets" :class="navLinkClass">
                    <span :class="iconClass">
                        <FontAwesomeIcon :icon="['fas', 'ticket']" />
                    </span>Tickets</a>
                <a href="/queue" :class="navLinkClass">
                    <span :class="iconClass">
                        <FontAwesomeIcon :icon="['fas', 'desktop']" />
                    </span>Queue Display</a>
            </ul>

            <h3 class="px-10">ACCOUNT</h3>
            <ul>
                <a href="/dashboard/my-profile" :class="navLinkClass">
                    <span :class="iconClass">
                        <FontAwesomeIcon :icon="['fas', 'user']" />
                    </span>Profile</a>
                <button @click="logout" :class="[navLinkClass, 'w-full']">
                    <span :class="iconClass">
                        <FontAwesomeIcon :icon="['fas', 'right-from-bracket']" />
                    </span>Logout</button>
            </ul>

            <form id="logout-form" action="/logout" method="POST" style="display: none;">
                <input type="hidden" name="_token" :value="csrfToken" />
            </form>
        </nav>

        <!-- Notification Bell for Collapsed Sidebar -->
        <div v-if="!isSidebarOpen" class="mt-4 flex justify-center">
            <a href="/dashboard/notifications" class="relative p-2 text-[#003D5B] hover:bg-gray-700 hover:text-white rounded transition-colors">
                <FontAwesomeIcon :icon="['fas', 'bell']" class="text-xl" />
                <span v-if="$notificationStore?.unreadCount > 0"
                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-4 h-4 flex items-center justify-center">
                    {{ $notificationStore.unreadCount > 99 ? '99+' : $notificationStore.unreadCount }}
                </span>
            </a>
        </div>

    </aside>
</template>
