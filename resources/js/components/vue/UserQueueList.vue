<template>
	<div
		class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
				flex flex-col items-center p-5 bg-white text-center
				shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)] rounded-2xl
				xs:w-full lg:w-[25%]"
	>

		<h3 class="w-full text-2xl font-bold text-[#003D5B] mb-4">
			My Queue List
		</h3>

		<!-- Tabs -->
		<div class="flex w-full border-b border-gray-300">
			<button
				class="w-1/2 py-2 text-sm font-semibold transition border-b-2"
				:class="activeTab === 'queued'
					? 'border-[#003D5B] text-[#003D5B]'
					: 'border-transparent text-gray-500'"
				@click="activeTab = 'queued'"
			>
				Queued Tickets
			</button>

			<button
				class="w-1/2 py-2 text-sm font-semibold transition border-b-2"
				:class="activeTab === 'my'
					? 'border-[#003D5B] text-[#003D5B]'
					: 'border-transparent text-gray-500'"
				@click="activeTab = 'my'"
			>
				My Tickets
			</button>
		</div>

		<!-- TAB CONTENT -->
		<div class="w-full mt-4 text-left max-h-64 overflow-y-auto">
			<!-- Queued Tickets Table -->
			<div v-if="activeTab === 'queued'">
				<table class="w-full text-sm">
					<thead class="text-[#003D5B] font-semibold sticky top-0 bg-white">
						<tr>
							<th class="pb-2">Queue #</th>
							<th class="pb-2">Ticket #</th>
							<th class="pb-2">Status</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, i) in queuedTickets" :key="i" class="text-gray-700">
							<td class="py-1">{{ item.queue }}</td>
							<td class="py-1">{{ item.ticket }}</td>
							<td class="py-1">{{ item.status }}</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- My Tickets Table -->
			<div v-else>
				<table class="w-full text-sm">
					<thead class="text-[#003D5B] font-semibold sticky top-0 bg-white">
						<tr>
							<th class="pb-2">Ticket #</th>
							<th class="pb-2">Status</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, i) in myTickets" :key="i" class="text-gray-700">
							<td class="py-1">{{ item.ticket }}</td>
							<td class="py-1">{{ item.status }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</template>

<script setup>
	import { ref } from 'vue'

	const activeTab = ref('queued')

	// Dummy data
	const queuedTickets = ref([
		{ queue: '01', ticket: 'T-1001', status: 'Pending' },
		{ queue: '02', ticket: 'T-1002', status: 'In Progress' },
		{ queue: '03', ticket: 'T-1003', status: 'Pending' },
		{ queue: '04', ticket: 'T-1004', status: 'In Progress' },
		{ queue: '05', ticket: 'T-1005', status: 'Pending' },
		{ queue: '06', ticket: 'T-1006', status: 'In Progress' },
		{ queue: '07', ticket: 'T-1007', status: 'Pending' },
		{ queue: '08', ticket: 'T-1008', status: 'In Progress' },
		{ queue: '09', ticket: 'T-1009', status: 'Pending' },
		{ queue: '10', ticket: 'T-1010', status: 'In Progress' },
		{ queue: '11', ticket: 'T-1011', status: 'Pending' },
	])

	const myTickets = ref([
		{ ticket: 'T-2001', status: 'Completed' },
		{ ticket: 'T-2002', status: 'Pending' },
		{ ticket: 'T-2003', status: 'Completed' },
		{ ticket: 'T-2004', status: 'Pending' },
		{ ticket: 'T-2005', status: 'Completed' },
		{ ticket: 'T-2006', status: 'Pending' },
		{ ticket: 'T-2007', status: 'Completed' },
		{ ticket: 'T-2008', status: 'Pending' },
		{ ticket: 'T-2009', status: 'Completed' },
		{ ticket: 'T-2010', status: 'Pending' },
		{ ticket: 'T-2011', status: 'Completed' },
	])
</script>
