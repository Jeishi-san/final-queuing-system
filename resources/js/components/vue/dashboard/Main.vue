<script setup>
    import { ref, onMounted, watch } from "vue";
    import { Chart, PieController, ArcElement, Tooltip, Legend } from "chart.js";

    Chart.register(PieController, ArcElement, Tooltip, Legend);

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
    const searchEmail = ref('');
    const clients = ref([]);
    const clientTicketCount = ref(0);
    const showAddTicket = ref(false);

    const form = ref({
        holder_name: "",
        holder_email: "",
        ticket_number: "",
        issue: "not applicable",
        status: "pending approval"
    });

    //init
    searchEmail.value = 'john@concentrix.com';
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
            waiting.value = response.data.waiting;

            console.log("Resolved Total", resolvedTickets.value);
            console.log("Waiting", waiting.value);

            const response2 = await axios.get("/queues/waiting");
            queueList.value = response2.data;
            console.log("Waiting Queue Items", queueList.value);

            const response3 = await axios.get("/queues/list");
            inProgressList.value = response3.data.filter(
                item => item.ticket.status === "in progress"
            );
            console.log("InProgress Items", inProgressList.value);
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
            const clientCounts = Object.values(clientCount);

            if (ticketsByClientPieChart.value) {
                ticketsByClientPieChart.value.data.labels = clientLabels;
                ticketsByClientPieChart.value.data.datasets[0].data = clientCounts;
                ticketsByClientPieChart.value.update();
            }


        } catch (error) {
            console.error("Failed to fetch tickets:", error);
        }
    };

    const fetchClients = async () => {
        try {
            const response = await axios.get("/users");
            clients.value = response.data.filter(user => user.role === 'agent');
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

                <!-- Center: Pie Chart tickt resolved by me -->
                <div class="w-1/3 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets Managed by Me</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsByMePieChart" ></canvas>
                    </div>
                </div>

                <!-- Right Side: Pie Chart ticket dist by client-->
                <div class="w-1/3 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets by Client</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsByClientPieChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- bottom -->
            <div class="h-full rounded-3xl flex space-x-5 text-[#003D5B]">
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
                                    class="w-4 h-4 transition'"
                                />
                            </button>
                        </div>

                        <!-- Add Ticket Form (conditional) -->
                        <div v-if="showAddTicket"
                            class="w-full bg-white rounded-b-2xl
                                    shadow-[0_70px_200px_50px_rgba(0,0,0,0.3)]">
                            <div class="p-4 py-6 flex flex-col justify-center h-full relative">
                                <form
                                    @submit.prevent="handleTicketSumbit"
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
                                @click="goToTicket(ticket.ticket?.id)"
                            >
                                <div>{{ ticket?.ticket_number }}</div>
                                <div>{{ ticket?.status }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
