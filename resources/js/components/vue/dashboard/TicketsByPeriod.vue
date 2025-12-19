<script setup>
    import { ref, computed, onMounted, watch } from 'vue';

    const selectedPeriod = ref('daily');
    const tickets = ref([]);

    // custom range
    const customStart = ref('');
    const customEnd = ref('');

    const isCustom = computed(() => selectedPeriod.value === 'custom');



    const fetchTickets = async () => {
        const res = await axios.get("/tickets");
        tickets.value = res.data.filter(t => t.deleted_at === null)
        .map(t => ({
            created_at: t.created_at
        }));
        console.log("Tickets by Period", tickets.value);
    };

    /* ---------- Helpers ---------- */
    const formatDate = (date) =>
    date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric"
    });

    const formatMonth = (date) =>
        date.toLocaleDateString("en-US", {
            month: "short",
            year: "numeric"
        });

    const getWeekRange = (date) => {
        const start = new Date(date);
        start.setDate(start.getDate() - start.getDay());

        const end = new Date(start);
        end.setDate(end.getDate() + 6);

        return `${formatDate(start)} - ${formatDate(end)}`;
    };
    /* ---------- Column Label ---------- */
    const periodColumnLabel = computed(() => {
        switch (selectedPeriod.value) {
            case 'weekly':
                return 'Week'
            case 'monthly':
                return 'Month'
            case 'yearly':
                return 'Year'
            case 'custom':
                return 'Period'
            default:
                return 'Date'
        }
    });


    /* ---------- Table Rows ---------- */
    const tableRows = computed(() => {
        if (selectedPeriod.value === "custom") {
            if (!customStart.value || !customEnd.value) return [];

            const start = new Date(customStart.value);
            const end = new Date(customEnd.value);
            let count = 0;

            tickets.value.forEach(t => {
                const d = new Date(t.created_at);
                if (d >= start && d <= end) count++;
            });

            return [{
                label: `${customStart.value} - ${customEnd.value}`,
                count
            }];
        }

        const map = new Map();

        tickets.value.forEach(ticket => {
            const date = new Date(ticket.created_at);
            let key, label;

            switch (selectedPeriod.value) {
                case "daily":
                    key = date.toDateString();
                    label = formatDate(date);
                    break;

                case "weekly":
                    key = `${date.getFullYear()}-${getWeekRange(date)}`;
                    label = getWeekRange(date);
                    break;

                case "monthly":
                    key = `${date.getFullYear()}-${date.getMonth()}`;
                    label = formatMonth(date);
                    break;

                case "yearly":
                    key = date.getFullYear();
                    label = date.getFullYear().toString();
                    break;
            }

            map.set(key, (map.get(key) || { label, count: 0 }));
            map.get(key).count++;
        });

        let rows = Array.from(map.values()).reverse();

        if (selectedPeriod.value === "custom") {
            const total = rows.reduce((sum, r) => sum + r.count, 0);
            return [{ label: "Selected Range", count: total }];
        }

        return rows;
    });

    onMounted(fetchTickets);
</script>

<template>
    <div class="w-1/3 bg-white rounded-3xl shadow p-5">
        <!-- Header -->
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-[#003D5B]">
                Tickets by Period
            </h2>

            <select
                v-model="selectedPeriod"
                class="w-40 border rounded-lg px-3 py-1 text-sm focus:outline-none"
            >
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="custom">Custom</option>
            </select>
        </div>

        <div v-if="isCustom" class="flex gap-2 mb-3">
            <input
                type="date"
                v-model="customStart"
                class="border rounded-md px-2 py-1 text-sm w-full"
            />
            <input
                type="date"
                v-model="customEnd"
                class="border rounded-md px-2 py-1 text-sm w-full"
            />
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="border-b text-gray-600">
                        <th class="py-2">
                            {{ periodColumnLabel }}
                        </th>
                        <th class="py-2 text-right">
                            Tickets Created
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(row, index) in tableRows"
                        :key="index"
                        class="border-b last:border-0"
                    >
                        <td class="py-2">
                            {{ row.label }}
                        </td>
                        <td class="py-2 text-right font-medium">
                            {{ row.count }}
                        </td>
                    </tr>

                    <tr v-if="tableRows.length === 0">
                        <td
                            colspan="2"
                            class="py-4 text-center text-gray-400"
                        >
                            No data available
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
