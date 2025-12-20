<script setup>
    import { ref, onMounted, watch, nextTick } from "vue";
    import { Chart, PieController, ArcElement, Tooltip, Legend } from "chart.js";
    import TicketsByPeriod from './TicketsByPeriod.vue';

    Chart.register(PieController, ArcElement, Tooltip, Legend);
    // Defensive default to avoid layout.padding undefined errors
    Chart.defaults.layout = Chart.defaults.layout || {};
    Chart.defaults.layout.padding = 0;

    /* =======================
    REACTIVE STATE
    ======================= */
    const waiting = ref(0);               // Waiting count
    const queueList = ref([]);            // Queue list (right table)
    const ticketList = ref([]);           // Ticket list (left table)
    const ticketListByClient = ref([]);
    const loading = ref(true);
    const ticketsPieChart = ref(null);
    const ticketsByMePieChart = ref(null);
    const ticketsByClientPieChart = ref(null);
    const ticketsByStaffPieChart = ref(null);
    const ticketsByClientPieChart_admin = ref(null);
    const searchEmail = ref('');
    const clients = ref([]);
    const clientTicketCount = ref(0);
    const showAddTicket = ref(false);
    const isSuperAdmin = ref(false);
    const isListSwitched = ref(false);

    const periodType = ref("daily");
    const ticketsByPeriodTable = ref([]);

    const form = ref({
        holder_name: "",
        holder_email: "",
        ticket_number: "",
        issue: "not applicable",
        status: "pending approval"
    });

    const handleTicketSubmit = async () => {
        if (!form.value.ticket_number) return;

        console.log("Ticket adding...", form.value);
        loading.value = true;

        try {
            form.value.holder_name = clients.value.find(c => c.email === form.value.holder_email)?.name || '';

            const response = await axios.post('/tickets', form.value)
            console.log("Ticket added successfully:", response.data);

            // Clear input
            form.value.ticket_number = "";
            showAddTicket.value = false;
            await fetchTickets();
            await fetchTicketsByClient();
            await fetchforPieCharts();
        } catch (error) {
            if (error.response) {
                console.error("Adding failed:", error.response.data);
            } else {
                console.error("Error:", error);
            }
        } finally {
            loading.value = false;
        }
    };

    //init
    searchEmail.value = '';
    form.value.holder_email = searchEmail.value;

    // FIX: These were used but never declared (runtime error)
    const resolvedTickets = ref([]);
    const inProgressList = ref([]);

    /* =======================
    DATA FETCHING
    ======================= */
    const fetchQueuedTickets = async () => {
        try {
            const response = await axios.get("/tickets/queued");
            resolvedTickets.value = response.data.resolved_tickets;

            console.log("Resolved Total", resolvedTickets.value);

            const response2 = await axios.get("/queues/waiting");
            queueList.value = response2.data;

            const response3 = await axios.get("/queues/list");
            inProgressList.value = response3.data.filter(
                item => item.ticket.status === "in progress"
            );

            waiting.value = response3.data.length;
            if(isSuperAdmin) {
                const response = await axios.get("/queues/list");
                queueList.value = response.data;
            }

        } catch (error) {
            console.error("Failed to fetch tickets:", error);
        }
    };

    const fetchTickets = async () => {
        loading.value = true;
        try {
            const res = await axios.get("/tickets");
            ticketList.value = res.data;
            console.log("Fetched tickets items:", ticketList.value);
        } catch (error) {
            console.error("Error fetching tickets items:", error);
        } finally {
            loading.value = false;
        }
    };

    const fetchforPieCharts = async () => {
        try {
            const response = await axios.get("/tickets/summary", {params: { clientEmail: searchEmail.value }});
            const data = response.data;

            // Ticket distribution
            const statusCounts = data.status_counts || {};
            const labels = Object.keys(statusCounts);
            const counts = Object.values(statusCounts);

            if (ticketsPieChart.value) {
                ticketsPieChart.value.data.labels = labels;
                ticketsPieChart.value.data.datasets[0].data = counts;
                ticketsPieChart.value.update();
            }

            if(isSuperAdmin.value) {
                // Tickets by staff chart
                const staffArray = data.staff || [];
                const staffLabels = staffArray.map(s => s.name);
                const staffCounts = staffArray.map(s => s.count);

                if (ticketsByStaffPieChart.value) {
                    ticketsByStaffPieChart.value.data.labels = staffLabels;
                    ticketsByStaffPieChart.value.data.datasets[0].data = staffCounts;
                    ticketsByStaffPieChart.value.update();
                }

                // Tickets by client chart, Super Admin View
                const clientCount = data.client || {};
                const clientLabels = Object.keys(clientCount);
                let clientCounts = Object.values(clientCount);

                // Check if all values are 0 or array is empty
                const hasData = clientCounts.some(v => v > 0);
                if (!hasData) {
                    clientLabels.length = 0;
                    clientCounts = [1];
                    clientLabels.push("No Tickets");
                }

                if (ticketsByClientPieChart_admin.value) {
                    ticketsByClientPieChart_admin.value.data.labels = clientLabels;
                    ticketsByClientPieChart_admin.value.data.datasets[0].data = clientCounts;
                    ticketsByClientPieChart_admin.value.update();
                }

            } else {
                // Tickets by me chart
                const mineCount = data.mine_counts || {};
                const mineLabels = Object.keys(mineCount);
                const mineCounts = Object.values(mineCount);

                if (ticketsByMePieChart.value) {
                    ticketsByMePieChart.value.data.labels = mineLabels;
                    ticketsByMePieChart.value.data.datasets[0].data = mineCounts;
                    ticketsByMePieChart.value.update();
                }

                // Tickets by client chart
                const clientCount = data.client || {};
                const clientLabels = Object.keys(clientCount);
                let clientCounts = Object.values(clientCount);

                // Check if all values are 0 or array is empty
                const hasData = clientCounts.some(v => v > 0);
                if (!hasData) {
                    clientLabels.length = 0;
                    clientCounts = [1];
                    clientLabels.push("No Tickets");
                }

                if (ticketsByClientPieChart.value) {
                    ticketsByClientPieChart.value.data.labels = clientLabels;
                    ticketsByClientPieChart.value.data.datasets[0].data = clientCounts;
                    ticketsByClientPieChart.value.update();
                }
            }

        } catch (error) {
            console.error("Failed to fetch tickets:", error);
        }
    };

    const fetchClients = async () => {
        try {
            const response = await axios.get("/users");
            clients.value = response.data.filter(user => user.role === 'agent');

            if (clients.value.length > 0) {
                searchEmail.value = clients.value[0].email;
            }
        } catch (error) {
            console.error("Error fetching clients:", error);
        }
    };

    const fetchTicketsByClient = async () => {
        loading.value = true;
        try {
            const res = await axios.get("/tickets");
            ticketListByClient.value = res.data.filter(ticket => {
                return searchEmail.value
                    ? ticket.holder_email === searchEmail.value
                    : true;
            });

            clientTicketCount.value = ticketListByClient.value.length;
        } catch (error) {
            console.error("Error fetching tickets items:", error);
        } finally {
            loading.value = false;
        }
    };

    const fetchUserRole = async () => {
        try {
            const response = await axios.get('/check-super-admin');
            isSuperAdmin.value = response.data.is_super_admin;
            console.log('Super admin status checked:', isSuperAdmin.value);
        } catch (error) {
            console.error('Error checking super admin status:', error);
        }
    };

    /* =======================
    NAVIGATION
    ======================= */
    const goToTicket = (ticketId) => {
        if (!ticketId) return;
        window.location.href = `/dashboard/tickets?highlight=${ticketId}`;
    };

    // Date format
    const formatDate = (iso) => {
        if (!iso) return "N/A";
        return new Date(iso).toLocaleString();
    };

    /* =======================
    LIFECYCLE
    ======================= */
    onMounted(() => {
        fetchUserRole();
        fetchQueuedTickets();
        fetchTickets();
        fetchforPieCharts();
        fetchClients();
        fetchTicketsByClient();

        // PIE charts code (null-safe to prevent crashes)
        const ctx = document
            .getElementById("ticketsPieChart")
            ?.getContext("2d");
        if (ctx) {
            ticketsPieChart.value = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: "Tickets",
                            data: [],
                            backgroundColor: [
                                "#F87171", // red
                                "#FBBF24", // yellow
                                "#3B82F6", // blue
                                "#F97316", // orange
                                "#10B981", // green
                                "#8B5CF6", // purple
                                "#06B6D4", // teal
                            ],
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: 0 },
                    plugins: {
                        legend: {
                            position: "right",
                        },
                    },
                },
            });
        }

        const ctx2 = document
            .getElementById("ticketsByMePieChart")
            ?.getContext("2d");

        if (ctx2) {
            ticketsByMePieChart.value = new Chart(ctx2, {
                type: "pie",
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: "Tickets",
                            data: [],
                            backgroundColor: [
                                "#F87171", // red
                                "#FBBF24", // yellow
                                "#3B82F6", // blue
                                "#F97316", // orange
                                "#10B981", // green
                                "#8B5CF6", // purple
                                "#06B6D4", // teal
                            ],
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: 0 },
                    plugins: {
                        legend: {
                            position: "right",
                        },
                    },
                },
            });
        }

        const ctx3 = document
            .getElementById("ticketsByClientPieChart")
            ?.getContext("2d");

        if (ctx3) {
            ticketsByClientPieChart.value = new Chart(ctx3, {
                type: "pie",
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: "Tickets",
                            data: [],
                            backgroundColor: [
                                "#F87171", // red
                                "#FBBF24", // yellow
                                "#FBBF24", // yellow
                                "#FBBF24", // yellow
                                "#10B981", // green
                                "#8B5CF6", // purple
                                "#06B6D4", // teal
                            ],
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: 0 },
                    plugins: {
                        legend: {
                            position: "right",
                        },
                    },
                },
            });
        }
    });

    // Watch for changes
    watch(searchEmail, () => {
        fetchforPieCharts();
        fetchTicketsByClient();
        form.value.holder_email = searchEmail.value;
    });

    // watch(() => form.value.holder_email, (newEmail) => {
    //     const user = users.find(u => u.email === newEmail);
    //     form.value.holder_name = user ? user.name : '';
    // });

    watch(isSuperAdmin, async (val) => {
        if (!val) return;

        await nextTick(); // ensure canvas is in DOM

        const ctx4 = document
            .getElementById("ticketsByStaffPieChart")
            ?.getContext("2d");

        if (ctx4) {
            ticketsByStaffPieChart.value = new Chart(ctx4, {
                type: "pie",
                data: {
                    labels: [],
                    datasets: [{
                        label: "Tickets",
                        data: [],
                        backgroundColor: [
                            "#F87171",
                            "#FBBF24",
                            "#3B82F6",
                            "#10B981",
                            "#8B5CF6",
                            "#06B6D4",
                        ],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: 0 },
                    plugins: { legend: { position: "right" } },
                },
            });
            fetchforPieCharts(); // now safe
        }

        const ctx5 = document
            .getElementById("ticketsByClientPieChart_admin")
            ?.getContext("2d");

        if (ctx5) {
            ticketsByClientPieChart_admin.value = new Chart(ctx5, {
                type: "pie",
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: "Tickets",
                            data: [],
                            backgroundColor: [
                                "#F87171", // red
                                "#FBBF24", // yellow
                                "#FBBF24", // yellow
                                "#FBBF24", // yellow
                                "#10B981", // green
                                "#8B5CF6", // purple
                                "#06B6D4", // teal
                            ],
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: 0 },
                    plugins: {
                        legend: {
                            position: "right",
                        },
                    },
                },
            });
        }
    });

</script>

<template>
    <main class="flex w-full h-full px-10 py-5 box-border space-x-5">
        <div class="w-full flex flex-col space-y-5 rounded-3xl">
            <!-- top -->
            <div class="flex gap-5">
                <!-- Left Side: Pie Chart tickt dist by status-->
                <div class="w-1/3 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets by Status</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsPieChart"></canvas>
                    </div>
                </div>

                <!-- Center, Staff View: Pie Chart tickt resolved by me -->
                <div v-if="!isSuperAdmin" class="w-1/3 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets Managed by Me</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsByMePieChart" ></canvas>
                    </div>
                </div>

                <!-- Center, Super Admin view: Pie Chart tickt resolved by me -->
                <div v-if="isSuperAdmin" class="w-1/3 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets Managed by Staff</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsByStaffPieChart" ></canvas>
                    </div>
                </div>

                <!-- Right Side: Pie Chart ticket dist by client-->
                <div v-if="!isSuperAdmin" class="w-1/3 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets by Client
                        <span class="text-sm font-light ml-5">{{ searchEmail }}</span></h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsByClientPieChart"></canvas>
                    </div>
                </div>

                <!-- Right Side, Super Admin View: ticket by period-->
                <TicketsByPeriod v-if="isSuperAdmin"/>
            </div>

            <!-- bottom | if super admin-->
            <div v-if="isSuperAdmin" class="flex h-[55svh] space-x-5 text-[#003D5B]">
                <div class="w-1/2 flex flex-col bg-white rounded-3xl p-5 text-[#003D5B]">
                    <div class="flex pb-5 text-lg relative">
                        <h1 class="font-semibold">{{ isListSwitched ? 'Queue' : 'Tickets' }} Quick View</h1>
                        <a :href="isListSwitched ? '/dashboard/queue-list' : '/dashboard/tickets'" :title="[isListSwitched ? 'Go to Queue List' : 'Go to Ticket List']">
                            <span class="">
                                <FontAwesomeIcon :icon="['fas', 'caret-right']" />
                            </span>
                        </a>
                        <button
                            class="absolute right-0 text-[#003D5B]
                                hover:text-[#4accff] transition text-sm"
                            @click="isListSwitched = !isListSwitched"
                        >
                            Switch to {{ isListSwitched ? 'Ticket' : 'Queue' }} List View
                        </button>
                    </div>

                    <!-- Table for Ticket List -->
                    <div class="rounded-t-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <colgroup>
                                <col class="w-1/3" />
                                <col class="w-1/3" />
                                <col class="w-1/3" />
                            </colgroup>
                            <thead class="bg-gray-200 sticky top-0 font-semibold text-[#003D5B]">
                                <tr v-if="!isListSwitched"> <!-- Ticket List View -->
                                    <th class="py-2 px-3 text-left">Ticket Number</th>
                                    <th class="py-2 px-3 text-left">Status</th>
                                    <th class="py-2 px-3 text-left">Last Modified</th>
                                </tr>

                                <tr v-if="isListSwitched"> <!-- Queue List View -->
                                    <th class="py-2 px-3 text-left">Queue Number</th>
                                    <th class="py-2 px-3 text-left">Ticket Number</th>
                                    <th class="py-2 px-3 text-left">Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div
                        class="flex-1 overflow-y-auto
                            scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100"
                    >
                        <table class="w-full text-sm">
                            <colgroup>
                                <col class="w-1/3" />
                                <col class="w-1/3" />
                                <col class="w-1/3" />
                            </colgroup>
                            <tbody v-if="!isListSwitched"> <!-- Ticket List View -->
                                <tr
                                    v-for="ticket in ticketList"
                                    :key="ticket.id"
                                    class="border-b hover:bg-gray-200 transition"
                                    @click="goToTicket(ticket.id)"
                                >
                                    <td class="py-2 px-3">{{ ticket.ticket_number }}</td>
                                    <td class="py-2 px-3">{{ ticket.status }}</td>
                                    <td class="py-2 px-3">{{ formatDate(ticket?.updated_at) }}</td>
                                </tr>

                                <tr v-if="(!ticketList || ticketList.length === 0) && clients">
                                    <td colspan="3" class="text-center py-4 text-gray-400">
                                        Empty ticket list.
                                    </td>
                                </tr>

                                <tr v-else-if="!clients || clients.length === 0">
                                    <td colspan="3" class="text-center py-4 text-gray-400">
                                        No registered account.
                                    </td>
                                </tr>
                            </tbody>

                            <tbody v-if="isListSwitched"> <!-- Queue List View -->
                                <tr
                                    v-for="queue in queueList"
                                    :key="queue.id"
                                    class="border-b hover:bg-gray-200 transition"
                                    @click="goToTicket(queue.ticket?.id)"
                                >
                                    <td class="py-2 px-3">{{ queue.queue_number }}</td>
                                    <td class="py-2 px-3">{{ queue.ticket?.ticket_number }}</td>
                                    <td class="py-2 px-3">{{ queue.ticket?.status }}</td>
                                </tr>

                                <tr v-if="!ticketList || ticketList.length === 0">
                                    <td colspan="3" class="text-center py-4 text-gray-400">
                                        Empty ticket list.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="w-1/2 flex flex-col space-y-5">
                    <div class="w-full h-[46%] bg-white rounded-3xl flex flex-col p-5 text-[#003D5B]">
                        <div class="w-full bg-white rounded-3xl flex pb-5 text-lg relative">
                            <!-- Left: Title -->
                            <h1 class="flex justify-start font-semibold">
                                Client Tickets<span class="ml-2 text-sm font-light" title="Total Tickets">{{ clientTicketCount ? clientTicketCount : 0 }}</span>
                            </h1>

                            <!-- Right: Search -->
                            <div class="absolute right-0 -top-[1px] w-80">
                                <select
                                    v-model="searchEmail"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-1 text-sm
                                            focus:outline-none focus:ring-1 focus:ring-[#003D5B]"
                                    >
                                    <option value="" disabled>Select Client Email</option>
                                    <option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :value="client.email"
                                    >{{ client.email }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="w-full h-[75%] flex flex-col relative">
                            <div class="rounded-t-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <colgroup>
                                        <col class="w-1/2" />
                                        <col class="w-1/2" />
                                    </colgroup>
                                    <thead class="bg-gray-200 sticky top-0 font-semibold text-[#003D5B]">
                                        <tr > <!-- Ticket List View -->
                                            <th v-if="!showAddTicket" class="py-2 px-3 text-left">Ticket Number</th>
                                            <th v-if="!showAddTicket" class="py-2 px-3 text-left">Status</th>
                                            <th v-if="showAddTicket" class="py-2 px-3 text-left">Add Ticket</th>
                                            <th class="relative">
                                                <button
                                                    @click="showAddTicket = !showAddTicket"
                                                    class="absolute right-5 top-1/2 -translate-y-1/2 group rounded-lg transition
                                                    hover:text-[#029cda]"
                                                    title="Add Ticket"
                                                >
                                                    <FontAwesomeIcon
                                                        :icon="['fas', 'plus']"
                                                        class="w-3 h-3 transition"
                                                    />
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                            <div v-if="!showAddTicket"
                                class="flex-1 overflow-y-scroll
                                    scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100"
                            >
                                <table class="w-full text-sm">
                                    <colgroup>
                                        <col class="w-1/2" />
                                        <col class="w-1/2" />
                                    </colgroup>
                                    <tbody> <!-- Ticket List View -->
                                        <tr
                                            v-for="ticket in ticketListByClient"
                                            :key="ticket.id"
                                            class="border-b hover:bg-gray-200 transition"
                                            @click="goToTicket(ticket.id)"
                                        >
                                            <td class="py-2 px-3">{{ ticket.ticket_number }}</td>
                                            <td class="py-2 px-3">{{ ticket.status }}</td>
                                        </tr>

                                        <tr v-if="!ticketListByClient || ticketListByClient.length === 0">
                                            <td colspan="3" class="text-center py-4 text-gray-400">
                                                Empty ticket list.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Add Ticket Form -->
                            <div v-if="showAddTicket"
                                class="flex-1 overflow-hidden mt-4"
                            >
                                <table class="w-full text-sm">
                                    <colgroup>
                                        <col class="w-full" />
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <td class="py-2 px-3">
                                                <form
                                                    @submit.prevent="handleTicketSubmit"
                                                    class="flex flex-col space-y-5"
                                                >
                                                    <div class="flex space-x-5">
                                                        <select
                                                            v-model="form.holder_email"
                                                            class="w-1/2 text-sm px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#003D5B]"
                                                            required
                                                        >
                                                            <option value="" disabled>Select Email</option>
                                                            <option
                                                                v-for="client in clients"
                                                                :key="client.id"
                                                                :value="client.email"
                                                            >{{ client.email }}</option>
                                                        </select>
                                                        <input
                                                            type="text"
                                                            v-model="form.ticket_number"
                                                            placeholder="Ticket Number e.g. INC000000000001"
                                                            class="w-1/2 text-sm px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#003D5B]"
                                                            required
                                                        />
                                                    </div>
                                                    <button
                                                        type="submit"
                                                        class="bg-[#003D5B] text-white px-4 py-2 rounded-lg hover:bg-[#002a3a] transition"
                                                    >
                                                        Submit Ticket
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pie Chart ticket dist by client, Admin view-->
                    <div class="w-full h-[50%] bg-white rounded-3xl shadow p-5">
                        <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets by Client
                            <span class="text-sm font-light ml-5">{{ searchEmail }}</span></h2>
                        <div class="w-[85%] h-[85%] mx-auto">
                            <canvas id="ticketsByClientPieChart_admin"></canvas>
                        </div>
                    </div>


                </div>
            </div>

            <!-- bottom | if staff -->
            <div v-if="!isSuperAdmin" class="h-full rounded-3xl flex space-x-5 text-[#003D5B]">
                <div class="w-1/2 bg-white h-full rounded-3xl flex flex-col p-5 text-[#003D5B]">
                    <div class="w-full bg-white rounded-3xl flex justify-between pb-5 text-lg">
                        <h1 class="font-semibold">Tickets Quick View</h1>
                        <a href="/dashboard/tickets">View List</a>
                    </div>

                    <!-- Table for Ticket List -->
                    <div class="w-full max-h-[390px] flex flex-col">
                        <!-- Table Header -->
                        <div class="grid grid-cols-3 gap-2 bg-gray-200 p-3 rounded-t-2xl flex-none">
                            <div class="font-semibold text-[#003D5B]">Ticket Number</div>
                            <div class="font-semibold text-[#003D5B]">Status</div>
                            <div class="font-semibold text-[#003D5B]">Last Modified</div>
                        </div>

                        <!-- Table Rows -->
                        <div class="overflow-y-auto flex-1 rounded-b-2xl">
                            <div
                                v-for="ticket in ticketList"
                                :key="ticket.id"
                                class="grid grid-cols-3 gap-4 border-b p-3 hover:bg-gray-100 cursor-pointer"
                                @click="goToTicket(ticket?.id)"
                            >
                                <div>{{ ticket?.ticket_number }}</div>
                                <div>{{ ticket?.status }}</div>
                                <div>{{ formatDate(ticket?.updated_at) }}</div>
                            </div>
                            <div v-if="!ticketList || ticketList.length === 0">
                                <div colspan="3" class="text-center py-4 text-gray-400">
                                    Empty ticket list.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-1/4 h-full bg-white rounded-3xl flex flex-col p-5 text-[#003D5B]">
                    <div class="w-full bg-white rounded-3xl flex justify-between pb-5 text-lg">
                        <h1 class="flex justify-start font-semibold">Queue Quick View
                            <span class="font-light text-sm ml-2">{{ waiting ? waiting : 0 }}</span></h1>
                        <a href="/dashboard/queue-list">View List</a>
                    </div>

                    <!-- Table for Queue List -->
                    <div class="w-full max-h-[400px] flex flex-col">
                        <!-- Table Header (FIXED column count) -->
                        <div class="grid grid-cols-2 gap-2 bg-gray-200 p-3 rounded-t-2xl flex-none">
                            <div class="font-semibold text-[#003D5B]">Queue Number</div>
                            <div class="font-semibold text-[#003D5B]">Ticket Number</div>
                        </div>

                        <!-- Table Rows -->
                        <div class="overflow-y-auto flex-1 rounded-b-2xl">
                            <div
                                v-for="queue in queueList"
                                :key="queue.id"
                                class="grid grid-cols-2 gap-4 border-b p-3 hover:bg-gray-100 cursor-pointer"
                                @click="goToTicket(queue.ticket?.id)"
                            >
                                <div>{{ queue.queue_number }}</div>
                                <div>{{ queue.ticket?.ticket_number }}</div>
                            </div>
                            <div v-if="!queueList || queueList.length === 0">
                                <div colspan="3" class="text-center py-4 text-gray-400">
                                    No tickets queued.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-1/4 h-full bg-white rounded-3xl flex flex-col p-5 text-[#003D5B]">
                    <div class="w-full bg-white rounded-3xl flex pb-5 text-lg relative">
                        <!-- Left: Title -->
                        <h1 class="flex justify-start font-semibold">
                            Client Tickets<span class="ml-2 text-sm font-light">{{ clientTicketCount ? clientTicketCount : 0 }}</span>
                        </h1>

                        <!-- Right: Search -->
                        <div class="absolute right-0 -top-[1px] w-45">
                            <select
                                v-model="searchEmail"
                                class="w-full rounded-xl border border-gray-300 px-4 py-1 text-sm
                                        focus:outline-none focus:ring-1 focus:ring-[#003D5B]"
                                >
                                <option value="" disabled>Select Client Email</option>
                                <option
                                    v-for="client in clients"
                                    :key="client.id"
                                    :value="client.email"
                                >{{ client.email }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="w-full max-h-[400px] flex flex-col relative">
                        <!-- Table Header (FIXED column count) -->
                        <div class="grid grid-cols-2 gap-2 bg-gray-200 p-3 rounded-t-2xl flex-none relative">
                            <div v-if="!showAddTicket" class="font-semibold text-[#003D5B]">Ticket Number</div>
                            <div v-if="!showAddTicket" class="font-semibold text-[#003D5B]">Status</div>
                            <div v-if="showAddTicket" class="font-semibold text-[#003D5B]">Add Ticket</div>
                            <button
                                @click="showAddTicket = !showAddTicket"
                                class="absolute right-5 top-3 group rounded-lg transition
                                   hover:text-[#029cda]"
                            >
                                <FontAwesomeIcon
                                    :icon="['fas', 'plus']"
                                    :class="[ showAddTicket ? '-rotate-45' : '', 'w-4 h-4 transition']"
                                />
                            </button>
                        </div>

                        <!-- Add Ticket Form (conditional) -->
                        <div v-if="showAddTicket"
                            class="w-full bg-white rounded-b-2xl
                                    shadow-[0_70px_200px_50px_rgba(0,0,0,0.3)]">
                            <div class="p-4 py-6 flex flex-col justify-center h-full relative">
                                <form
                                    @submit.prevent="handleTicketSubmit"
                                    class="flex flex-col space-y-5"
                                >
                                    <select
                                        v-model="form.holder_email"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#003D5B]"
                                        required
                                    >
                                        <option value="" disabled>Select Email</option>
                                        <option
                                            v-for="client in clients"
                                            :key="client.id"
                                            :value="client.email"
                                        >{{ client.email }}</option>
                                    </select>
                                    <input
                                        type="text"
                                        v-model="form.ticket_number"
                                        placeholder="Ticket Number e.g. INC000000000001"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#003D5B]"
                                        required
                                    />
                                    <button
                                        type="submit"
                                        class="bg-[#003D5B] text-white px-4 py-2 rounded-lg hover:bg-[#002a3a] transition"
                                    >
                                        Submit Ticket
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Table Rows -->
                        <div v-if="!showAddTicket"
                            class="overflow-y-auto flex-1 rounded-b-2xl">
                            <div
                                v-for="ticket in ticketListByClient"
                                :key="ticket.id"
                                class="grid grid-cols-2 gap-4 border-b p-3 hover:bg-gray-100 cursor-pointer"
                                @click="goToTicket(ticket?.id)"
                            >
                                <div>{{ ticket?.ticket_number }}</div>
                                <div>{{ ticket?.status }}</div>
                            </div>
                            <div v-if="!ticketListByClient || ticketListByClient.length === 0">
                                <div colspan="3" class="text-center py-4 text-gray-400">
                                    Empty ticket list.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
