<script setup>
import SidebarMenu from './Menu.vue';
import Header from './Header.vue';
import MainContent from './Main.vue';
import Profile from './UserProfile.vue';
import QueueList from './QueueList.vue';
import Tickets from './Tickets.vue';
import EditProfile from './EditProfile.vue';
import NotificationsPage from './NotificationPage.vue';

import { ref } from "vue";

const filterClicked = ref(false);
const filterON = (value) => {
    filterClicked.value = value;
}

const manageClicked = ref(false);
const manageOn = (value) => {
    manageClicked.value = value;
}

// ✅ FIXED: Map the URL to the Component
const components = {
    "/dashboard": MainContent,
    "/dashboard/my-profile": Profile,
    "/dashboard/edit-profile": EditProfile,
    "/dashboard/queue-list": QueueList,
    "/dashboard/tickets": Tickets,
    "/dashboard/notifications": NotificationsPage, // <--- This is the key fix
};

// Select component based on current browser URL
const CurrentComponent = components[window.location.pathname] || MainContent;

const isSidebarOpen = ref(true);

// ✅ FIXED: Set the Header Title
const pageName = {
    "/dashboard/tickets": "Tickets",
    "/dashboard/queue-list": "Queue",
    "/dashboard/my-profile": "Profile",
    "/dashboard/edit-profile": "Edit Profile",
    "/dashboard/notifications": "Notifications"
}[window.location.pathname] || "Dashboard";

</script>

<template>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <SidebarMenu/>

        <!-- Main Section -->
        <div class="flex flex-col flex-1 transition-all duration-300 overflow-hidden">
            <!-- Header -->
            <Header @isFilterClicked="filterON" :pageName="pageName" @isManageClicked="manageOn"></Header>

            <!-- Content Area - Made scrollable -->
            <main class="flex-1 overflow-">
                <component :is="CurrentComponent" :isFilterClicked="filterClicked" :isManageClicked="manageClicked"/>
            </main>
        </div>
    </div>
</template>
