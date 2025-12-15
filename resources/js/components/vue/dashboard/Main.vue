<script setup>
    import { ref, onMounted } from "vue";
    import { Chart, PieController, ArcElement, Tooltip, Legend } from "chart.js";

    Chart.register(PieController, ArcElement, Tooltip, Legend);

    /* =======================
    REACTIVE STATE
    ======================= */
    const waiting = ref(0);               // Waiting count
    const queueList = ref([]);            // Queue list (right table)
    const ticketList = ref([]);           // Ticket list (left table)
    const loading = ref(true);
    const ticketsPieChart = ref(null);
    const ticketsResolvedByMePieChart = ref(null);

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
            const response = await axios.get("/tickets/summary");
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

            // Resolved by me chart
            const resolved = data.mine_vs_others || { mine: 0, others: 0 };
            if (ticketsResolvedByMePieChart.value) {
                ticketsResolvedByMePieChart.value.data.datasets[0].data = [
                    resolved.mine,
                    resolved.others,
                ];
                ticketsResolvedByMePieChart.value.update();
            }

        } catch (error) {
            console.error("Failed to fetch tickets:", error);
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

        // PIE charts code (null-safe to prevent crashes)
        const ctx = document
            .getElementById("ticketsPieChart")
            ?.getContext("2d");

        const ctx2 = document
            .getElementById("ticketsResolvedByMePieChart")
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

        if (ctx2) {
            ticketsResolvedByMePieChart.value = new Chart(ctx2, {
                type: "pie",
                data: {
                    labels: ["Mine", "Others"],
                    datasets: [
                        {
                            label: "Resolved Tickets",
                            data: [],
                            backgroundColor: [
                                "#10B981", // green
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
</script>

<template>
    <main class="flex w-full h-full px-10 py-5 box-border space-x-5">
        <!-- Left -->
        <div class="w-[60%] flex flex-col space-y-5 rounded-3xl">
            <!-- left-top -->
            <div class="flex gap-5">
                <!-- Left Side: Pie Chart -->
                <div class="w-1/2 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Ticket Distribution</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsPieChart"></canvas>
                    </div>
                </div>

                <!-- Right Side: Pie Chart Staff -->
                <div class="w-1/2 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets Resolved by Me</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsResolvedByMePieChart" ></canvas>
                    </div>
                </div>

                <!-- Right Side: Pie Chart SuperAdmin-->
                <div class="w-1/2 bg-white rounded-3xl shadow p-5">
                    <h2 class="text-lg font-semibold mb-2 text-[#003D5B]">Tickets Resolved by Staff</h2>
                    <div class="w-[85%] h-[85%] mx-auto">
                        <canvas id="ticketsResolvedByMePieChart" ></canvas>
                    </div>
                </div>
            </div>

            <!-- left-bottom -->
            <div class="h-full bg-white rounded-3xl flex flex-col p-5 text-[#003D5B]">
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
        </div>

        <!-- Right -->
        <div class="w-[40%] h-full bg-white rounded-3xl flex flex-col p-5 text-[#003D5B]">
            <div class="w-full bg-white rounded-3xl flex justify-between pb-5 text-lg">
                <h1 class="font-semibold">Queue Quick View</h1>
                <span>{{ waiting ? waiting : 0 }} waiting</span>
                <a href="/dashboard/queue-list">View List</a>
            </div>

            <!-- Table for Queue List -->
            <div class="w-full max-h-full flex flex-col">
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
    </main>
</template>
