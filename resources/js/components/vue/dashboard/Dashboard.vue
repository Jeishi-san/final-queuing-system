<script setup>
    import SidebarMenu from './Menu.vue';
    import Header from './Header.vue';

    import MainContent from './Main.vue';
    import Profile from './UserProfile.vue';
    import QueueList from './QueueList.vue';
    import Tickets from './Tickets.vue';

    import { ref } from "vue";


    const filterClicked = ref(false);

    const filterON = (value) => {
        filterClicked.value = value
    }

    const components = {
        "/dashboard": MainContent,
        "/dashboard/my-profile": Profile,
        "/dashboard/queue-list": QueueList,
        "/dashboard/tickets": Tickets,
    };

    const CurrentComponent = components[window.location.pathname] || Dashboard;

    const isSidebarOpen = ref(true);

    const pageName = {
        "/dashboard/tickets": "Tickets",
        "/dashboard/queue-list": "Queue",
        "/dashboard/my-profile": "Profile"
    }[window.location.pathname] || "Dashboard";

</script>

<template>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <SidebarMenu/>

        <!-- Main Section -->
        <div class="flex flex-col flex-1 transition-all duration-300">
            <!-- Header -->
            <Header @isFilterClicked="filterON"  :pageName="pageName"></Header>

            <!-- Content Area -->
            <component :is="CurrentComponent" :isFilterClicked="filterClicked"/>
        </div>
    </div>
</template>
