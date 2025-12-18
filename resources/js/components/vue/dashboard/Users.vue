<script setup>
    import { ref, onMounted, nextTick, computed, watch } from 'vue';

    const grid_cols = "grid grid-cols-[180px_200px_150px_130px_150px_auto] gap-4";
    const grid_cols2 = "grid grid-cols-[180px_200px_150px_auto] gap-4";
    const style_header = "font-semibold text-[#003D5B]";

    const staffList = ref([]);
    const clientList = ref([]);
    const ticketList = ref([]);

    const isStaffList = ref(true);

    const loading = ref(true);
    const selectedUser = ref(null);
    const isStaffDetailsModalOpen = ref(false);
    const isClientTicketsModalOpen = ref(false);
    const highlightId = ref(null);

    // Fetch Users
    const fetchUsers = async () => {
        loading.value = true;
        try {
            const res = await axios.get('/users');
            staffList.value = res.data.filter(user => user.role === 'it_staff');
            clientList.value = res.data.filter(user => user.role === 'agent');
            console.log('Fetched staff items:', staffList.value);
            console.log('Fetched client items:', clientList.value);
        } catch (error) {
            console.error('Error fetching staff items:', error);
        } finally {
            loading.value = false;
        }
    };

    // Fetch User Logs
    const fetchUserLogs = async (userEmail) => {
        loading.value = true;
        try {
            const res = await axios.get(`/users/it-staff/activity`, {
                params: { email: userEmail }
            });


            // make sure activityLogs exists
            if (!Array.isArray(selectedUser.value.activityLogs)) {
                selectedUser.value.activityLogs = [];
            }

            // append
            selectedUser.value.activityLogs.push(...res.data);

            // sort ASC (oldest → newest)
            selectedUser.value.activityLogs.sort(
                (a, b) => new Date(b.created_at) - new Date(a.created_at)
            );

            console.log('Fetched user activity logs:', selectedUser.value.activityLogs);

        } catch (error) {
            console.error('Error fetching user activity logs:', error);
        } finally {
            loading.value = false;
        }
    };

    // Fetch tickets
    const fetchTickets = async (userEmail) => {
        loading.value = true;
        try {
            const res = await axios.get('/users/clients/tickets', {
                params: { email: userEmail }
            });

            // make sure tickets exists
            if (!Array.isArray(selectedUser.value.tickets)) {
                selectedUser.value.tickets = [];
            }

            // append
            selectedUser.value.tickets.push(...res.data);

            // sort ASC (oldest → newest)
            selectedUser.value.tickets.sort(
                (a, b) => new Date(b.created_at) - new Date(a.created_at)
            );

            console.log('Fetched user tickets:', selectedUser.value.tickets);
        } catch (error) {
            console.error('Error fetching tickets items:',error);
        } finally {
            loading.value = false;
        }
    };

    // Open user modal
    const openModal = (user) => {
        if(isStaffList.value) {
            fetchUserLogs(user.email);
            selectedUser.value = user;
            isStaffDetailsModalOpen.value = true;
        } else {
            fetchTickets(user.email);
            selectedUser.value = user;
            isClientTicketsModalOpen.value = true;
        }

        console.log('Selected user:', selectedUser.value);
    };

    // Date format
    const formatDate = (iso) => {
        if (!iso) return "N/A";
        return new Date(iso).toLocaleString();
    };

    // Status styling
    const getStatusClasses = (status) => {
        switch (status) {
            case 'queued':
                return 'bg-yellow-100 text-yellow-800';
            case 'in progress':
                return 'bg-blue-100 text-blue-800';
            case 'resolved':
                return 'bg-green-100 text-green-800';
            case 'pending approval':
                return 'bg-orange-100 text-orange-800';
            case 'on hold':
                return 'bg-violet-100 text-violet-800';
            case 'cancelled':
            case 'dequeued':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    /* =======================
    NAVIGATION
    ======================= */
    const goToTicket = (ticketId) => {
        if (!ticketId) return;
        window.location.href = `/dashboard/tickets?highlight=${ticketId}`;
    };

    onMounted(fetchUsers);

    onMounted(async () => {

        // read highlight param
        const params = new URLSearchParams(window.location.search);
        highlightId.value = params.get('highlight');

        // fetch tickets
        const res = await axios.get('/tickets');
        ticketList.value = res.data;

        // wait for DOM to update
        await nextTick();

        // scroll to highlighted ticket
        if (highlightId.value) {
            const target = document.querySelector('.bg-yellow-300');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });


</script>

<template>
  <div class="flex flex-col h-[calc(100vh-100px)] w-full justify-start items-center px-10 py-5 box-border">

    <!-- Table Container -->
    <div :class="['w-full flex flex-col overflow-hidden h-full transition-all',
                    isStaffList ? 'max-w-[1100px]' : 'max-w-[800px]']">

        <div class="flex items-center justify-between mb-5">
            <h1 class="text-xl font-bold text-white"> {{ isStaffList ? 'IT Staff' : 'Client ' }} Accounts</h1>
            <button
                @click="isStaffList = !isStaffList"
                class="text-white text-lg font-light hover:text-[#18bcfd] hover:font-normal transition-colors"
            >
                Switch to {{ isStaffList ? 'Client' : 'IT Staff' }} View
            </button>
        </div>

        <!-- Table Header 1 -->
        <div v-if="isStaffList" :class="[grid_cols, 'bg-gray-200 p-3 rounded-t-2xl']">
            <div :class="style_header">Name</div>
            <div :class="style_header">Email</div>
            <div :class="style_header">Employee ID</div>
            <div :class="style_header">Contact Number</div>
            <div :class="style_header">Account Status</div>
            <div :class="style_header">Date Created</div>
        </div>

        <!-- Table Rows for IT Staff -->
        <div v-if="isStaffList" class="bg-[#99bbc4] overflow-y-auto h-full">
            <div
                v-for="staff in staffList"
                :key="staff.id"
                :class="[
                    grid_cols, 'border-b p-3 hover:bg-gray-100 cursor-pointer',
                    staff.id == highlightId ? 'bg-[#029cda]' : 'hover:bg-gray-100']"

                ref="staffRows"
                @click="openModal(staff)"
            >
                <div>{{ staff.name }}</div>
                <div>{{ staff.email }}</div>
                <div>{{ staff.employee_id }}</div>
                <div>{{ staff.contact_number }}</div>
                <div>{{ staff.account_status }}</div>
                <div>{{ formatDate(staff.created_at) }}</div>

            </div>

            <!-- Loading state -->
            <div v-if="loading" class="text-center py-4 text-gray-700">Loading...</div>

            <!-- No data state -->
            <div v-if="!loading && ticketList.length === 0" class="text-center py-4 text-gray-700">No account found.</div>
        </div>

        <!-- Table Header 2 -->
        <div v-if="!isStaffList" :class="[grid_cols, 'bg-gray-200 p-3 rounded-t-2xl']">
            <div :class="style_header">Name</div>
            <div :class="style_header">Email</div>
            <div :class="style_header">Account Status</div>
            <div :class="style_header">Date Created</div>
        </div>

        <!-- Table Rows for Clients -->
        <div v-if="!isStaffList" class="bg-[#99bbc4] overflow-y-auto h-full">
            <div
                v-for="client in clientList"
                :key="client.id"
                :class="[
                    grid_cols2, 'border-b p-3 hover:bg-gray-100 cursor-pointer',
                    client.id == highlightId ? 'bg-[#029cda]' : 'hover:bg-gray-100']"

                ref="staffRows"
                @click="openModal(client)"
            >
                <div>{{ client.name }}</div>
                <div>{{ client.email }}</div>
                <div>{{ client.account_status }}</div>
                <div>{{ formatDate(client.created_at) }}</div>

            </div>

            <!-- Loading state -->
            <div v-if="loading" class="text-center py-4 text-gray-700">Loading...</div>

            <!-- No data state -->
            <div v-if="!loading && ticketList.length === 0" class="text-center py-4 text-gray-700">No account found.</div>
        </div>
    </div>

    <!-- Staff Activity Logs Modal -->
    <div v-if="isStaffDetailsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
        <div class="bg-white rounded-2xl w-full max-w-2xl p-6">
            <!-- Modal Title -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold mb-1">Staff Info</h2>
                <button class="text-gray-600" @click="isStaffDetailsModalOpen = false, selectedUser.activityLogs = null">Close</button>
            </div>

            <div class="flex items-stretch">
                <img src="../../../../assets/img/prof.jpg" alt="my-profile"
                class="w-36 rounded-lg border-2 border-gray-300 object-cover"/>
                <div class="flex flex-col ml-6 space-y-2">
                    <h2 class="text-md">Name: <span class="font-semibold">{{ selectedUser?.name }}</span></h2>
                    <h2 class="text-md">Email: <span class="font-semibold">{{ selectedUser?.email }}</span></h2>
                    <h2 class="text-md">Employee ID: <span class="font-semibold">{{ selectedUser?.employee_id }}</span></h2>
                    <h2 class="text-md">Contact Number: <span class="font-semibold">{{ selectedUser?.contact_number ?? 'N/A' }}</span></h2>
                    <h2 class="text-md">Account Status: <span class="font-semibold">{{ selectedUser?.account_status }}</span></h2>
                </div>
            </div>

            <!-- Activity Logs -->

            <h1 class="text-lg font-semibold my-3">Activity Logs</h1>
            <div class="overflow-y-auto h-[400px] scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 rounded-lg">

                <table class="min-w-full text-sm">
                    <thead class="bg-gray-400 sticky top-0">
                        <tr>
                            <th class="py-2 px-3 text-left font-semibold">Date</th>
                            <th class="py-2 px-3 text-left font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- Logs -->
                        <tr
                            v-for="(log, index) in selectedUser.activityLogs"
                            :key="log.id || index"
                            class="border-b hover:bg-gray-200 transition"
                        >
                            <td class="py-2 px-3">
                                {{ formatDate(log.date) }}
                            </td>
                            <td class="py-2 px-3">
                                {{ log.action }}
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!selectedUser?.activityLogs || selectedUser.activityLogs.length === 0">
                            <td colspan="4" class="text-center py-4 text-gray-400">
                                No activity recorded.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Client Tickets Modal -->
    <div v-if="isClientTicketsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
        <div class="bg-white rounded-2xl w-full max-w-2xl p-6">
            <!-- Modal Title -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold mb-1">Client Info</h2>
                <button class="text-gray-600" @click="isClientTicketsModalOpen = false, selectedUser.tickets = null">Close</button>
            </div>

            <div class="flex items-stretch">

                <div class="grid grid-cols-2 gap-1 ml-1">
                    <h2 class="text-md">Name: <span class="font-semibold">{{ selectedUser?.name }}</span></h2>
                    <h2 class="text-md">Account Status: <span class="font-semibold">{{ selectedUser?.account_status }}</span></h2>
                    <h2 class="text-md">Email: <span class="font-semibold">{{ selectedUser?.email }}</span></h2>
                    <h2 class="text-md">Joined: <span class="font-semibold">{{ formatDate(selectedUser?.created_at) }}</span></h2>
                </div>
            </div>

            <!-- Tickets -->
            <h1 class="text-lg font-semibold my-3">Tickets</h1>
            <div class="overflow-y-auto h-[400px] scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 rounded-lg">

                <table class="min-w-full text-sm">
                    <thead class="bg-gray-400 sticky top-0">
                        <tr>
                            <th class="py-2 px-3 text-left font-semibold">Ticket Number</th>
                            <th class="py-2 px-3 text-left font-semibold">Status</th>
                            <th class="py-2 px-3 text-left font-semibold">Queue Number</th>
                            <th class="py-2 px-3 text-left font-semibold">Date Submitted</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(ticket, index) in selectedUser.tickets"
                            :key="ticket.id || index"
                            class="border-b hover:bg-gray-200 transition"
                            @click="goToTicket(ticket.id)"
                        >
                            <td class="py-2 px-3">
                                {{ ticket.ticket_number }}
                            </td>
                            <td :class="[getStatusClasses(ticket.status), 'py-2 px-3']">
                                {{ (ticket.status) }}
                            </td>
                            <td class="py-2 px-3">
                                {{ ticket.queue_number }}
                            </td>
                            <td class="py-2 px-3">
                                {{ formatDate(ticket.created_at) }}
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!selectedUser?.tickets || selectedUser.tickets.length === 0">
                            <td colspan="4" class="text-center py-4 text-gray-400">
                                No tickets submitted.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

  </div>
</template>
