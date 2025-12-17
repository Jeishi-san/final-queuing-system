<script setup>
    import { ref, onMounted, nextTick, computed, watch } from 'vue';
    import TicketLogs from './TicketLogs.vue';

    const grid_cols = "grid grid-cols-[180px_200px_150px_130px_150px_auto] gap-4";
    const grid_cols2 = "grid grid-cols-[180px_200px_150px_auto] gap-4";
    const style_header = "font-semibold text-[#003D5B]";

    const staffList = ref([]);
    const clientList = ref([]);
    const ticketList = ref([]);

    const isStaffList = ref(true);

    const loading = ref(true);
    const selectedTicket = ref(null);
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

    // Fetch staff
    const fetchStaff = async () => {
        loading.value = true;
        try {
            const res = await axios.get('/staff');
            staffList.value = res.data;
            console.log('Fetched staff items:', staffList.value);
        } catch (error) {
            console.error('Error fetching staff items:', error);
        } finally {
            loading.value = false;
        }
    };

    // Fetch clients
    const fetchClients = async () => {
        loading.value = true;
        try {
            const res = await axios.get('/clients');
            clientList.value = res.data;
            console.log('Fetched client items:', clientList.value);
        } catch (error) {
            console.error('Error fetching client items:', error);
        } finally {
            loading.value = false;
        }
    };

    // Fetch tickets
    const fetchTickets = async () => {
        loading.value = true;
        try {
            const res = await axios.get('/tickets', { params: filters.value });
            ticketList.value = res.data;
            console.log('Fetched tickets items:', ticketList.value);
        } catch (error) {
            console.error('Error fetching tickets items:',error);
        } finally {
            loading.value = false;
        }
    };

    // Open ticketlogs
    const openModal = (ticket) => {
    selectedTicket.value = ticket;
    };

    // Date format
    const formatDate = (iso) => {
        if (!iso) return "N/A";
        return new Date(iso).toLocaleString();
    };

    //refresh list after deletion
    const refreshList = () => {
        fetchTickets();
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

    <!-- IT STAFF: Table Container -->
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

        <!-- Table Header 2 -->
        <div v-if="!isStaffList" :class="[grid_cols, 'bg-gray-200 p-3 rounded-t-2xl']">
            <div :class="style_header">Name</div>
            <div :class="style_header">Email</div>
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

    <TicketLogs v-if="selectedTicket" :ticketId="selectedTicket.id" @close="selectedTicket = null" @deleted="refreshList()" />

  </div>
</template>
