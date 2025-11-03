<template>
    <div class="flex flex-col w-full h-full px-10 py-5 overflow-hidden box-border">

        <div class="grid grid-cols-3 gap-6 h-full">

            <!-- LEFT: Profile Details -->
            <div class="col-span-1 bg-white rounded-2xl shadow p-6 flex flex-col space-y-6 box-border">

                <!-- Profile Header -->
                <div class="relative col-span-1 bg-white rounded-2xl shadow p-6 flex flex-col items-center text-center">

                            <!-- Floating Edit Button -->
                            <!-- <button
                                title="Edit Profile"
                                class="absolute top-4 right-4 text-[#003D5B] text-xl hover:text-[#029cda] transition"
                            >
                                <FontAwesomeIcon :icon="['fas', 'pen-to-square']" />
                            </button> -->

                    <!-- Profile Picture -->
                    <img
                        :src="user.avatar"
                        alt="Profile Photo"
                        class="w-[150px] h-[150px] rounded-full border-4 border-[#003D5B] mb-3"
                    />

                    <!-- User Info -->
                    <h2 class="text-xl font-bold text-[#003D5B]">{{ user.name }}</h2>

                    <p class="text-gray-600">{{ user.role }}</p>

                    <span
                        class="px-3 py-1 my-3 text-xs font-medium rounded-full"
                        :class="user.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'">
                        {{ user.status }}
                    </span>

                    <!-- Profile Edit Button -->
                    <button class="bg-[#003D5B] text-white text-sm px-4 py-2 rounded-xl hover:bg-[#004c73] transition">
                        Edit Profile
                    </button>

                </div>

                <!-- Divider -->
                <hr class="border-gray-200" />

                <!-- User Info -->
                <div class="text-sm text-gray-700 space-y-2">
                    <p><strong>Email:</strong> {{ user.email }}</p>
                    <p><strong>Employee ID:</strong> {{ user.employeeId }}</p>
                    <p><strong>Date Joined:</strong> {{ user.dateJoined }}</p>
                    <p><strong>Department:</strong> {{ user.department }}</p>
                    <p><strong>Contact:</strong> {{ user.contact }}</p>
                </div>

                <!-- (Optional Stats Placeholder) -->
                <div class="mt-auto bg-gray-100 p-4 rounded-xl text-center">
                    <p class="font-medium text-[#003D5B]">
                        Tickets Handled: <span class="font-bold">{{ stats.ticketsHandled }}</span>
                    </p>
                    <p class="font-medium text-[#003D5B]">
                        Avg. Resolution Time: <span class="font-bold">{{ stats.avgResolutionTime }}</span>
                    </p>
                </div>
            </div>

            <!-- RIGHT: Activity Log -->
            <div class="col-span-2 bg-white rounded-2xl shadow p-6 flex flex-col">
            <h2 class="text-xl font-semibold text-[#003D5B] mb-4">Activity Log</h2>

            <div class="overflow-y-auto h-[500px] scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 rounded-lg">
            <table class="min-w-full text-sm">
            <thead class="bg-gray-100 sticky top-0">
            <tr>
            <th class="py-2 px-3 text-left font-semibold">Date</th>
            <th class="py-2 px-3 text-left font-semibold">Action</th>
            <th class="py-2 px-3 text-left font-semibold">Ticket ID</th>
            <th class="py-2 px-3 text-left font-semibold">Details</th>
            </tr>
            </thead>
            <tbody>
            <tr
            v-for="(log, index) in activityLogs"
            :key="index"
            class="border-b hover:bg-gray-50 transition"
            >
            <td class="py-2 px-3">{{ log.date }}</td>
            <td class="py-2 px-3">{{ log.action }}</td>
            <td class="py-2 px-3 text-[#003D5B] font-semibold">{{ log.ticketId }}</td>
            <td class="py-2 px-3">{{ log.details }}</td>
            </tr>
            <tr v-if="activityLogs.length === 0">
            <td colspan="4" class="text-center py-4 text-gray-400">
            No activity recorded.
            </td>
            </tr>
            </tbody>
            </table>
            </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref } from "vue";

    import prof from '../../../../assets/img/prof.jpg';

    // Mock user data (replace with props or API later)
    const user = ref({
        name: "Ante Abeth Recubaz",
        role: "Sr. Technical representative",
        department: "IT Operations",
        status: "Active",
        email: "anteabeth@concentrix.com",
        employeeId: "CNX-1024",
        dateJoined: "Jan 15, 2023",
        contact: "+63 912 345 6789",
        avatar: prof,
    });

// Optional stats (for future use)
const stats = ref({
  ticketsHandled: 87,
  avgResolutionTime: "1h 45m",
});

// Mock activity logs
const activityLogs = ref([
  {
    date: "2025-10-20",
    action: "Updated Ticket",
    ticketId: "DAM073010",
    details: "Changed status to 'Resolved'",
  },
  {
    date: "2025-10-19",
    action: "Assigned Ticket",
    ticketId: "DAM072909",
    details: "Assigned to self for review",
  },
  {
    date: "2025-10-18",
    action: "Created Ticket",
    ticketId: "DAM072804",
    details: "Logged issue: 'PC not connecting to LAN'",
  },
]);
</script>

<style scoped>
/* Optional: Thin scrollbar styling */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #c0c0c0;
  border-radius: 3px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background-color: #a0a0a0;
}
</style>
