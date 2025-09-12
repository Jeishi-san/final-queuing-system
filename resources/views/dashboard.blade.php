@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <!-- 🔹 Ticket Stats Panel -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @php
            $stats = [
                ['label' => 'Pending', 'count' => $pendingCount, 'color' => 'yellow', 'icon' => '⏳'],
                ['label' => 'In Progress', 'count' => $inProgressCount, 'color' => 'blue', 'icon' => '⚙️'],
                ['label' => 'Resolved', 'count' => $resolvedCount, 'color' => 'green', 'icon' => '✅'],
                ['label' => 'Total', 'count' => $pendingCount + $inProgressCount + $resolvedCount, 'color' => 'gray', 'icon' => '📊'],
            ];
        @endphp

        @foreach ($stats as $stat)
            <div class="bg-{{ $stat['color'] }}-50 border-l-4 border-{{ $stat['color'] }}-500 p-5 rounded-lg shadow hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-{{ $stat['color'] }}-800">
                        {{ $stat['icon'] }} {{ $stat['label'] }}
                    </h3>
                </div>
                <p class="text-3xl font-bold text-{{ $stat['color'] }}-700 mt-2">{{ $stat['count'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- 🔹 Filter & Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-3 md:space-y-0">
        <!-- Filter Tabs -->
        <div class="flex space-x-3">
            @php
                $tabs = [
                    'All'        => null,
                    'Pending'    => 'pending',
                    'In Progress'=> 'in_progress',
                    'Resolved'   => 'resolved',
                ];
            @endphp

            @foreach ($tabs as $label => $value)
                <a href="{{ route('dashboard', $value ? ['status' => $value] : []) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition
                        {{ request('status') === $value || (is_null($value) && request('status') === null)
                            ? 'bg-blue-600 text-white shadow'
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Search -->
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="🔍 Search tickets..."
                   class="px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300 text-sm w-64">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- 🔹 Tickets Table -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">🎫 Tickets</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left text-gray-700">
                            <th class="border px-4 py-2">Ticket #</th>
                            <th class="border px-4 py-2">Agent</th>
                            <th class="border px-4 py-2">Agent Email</th>
                            <th class="border px-4 py-2">Team Leader</th>
                            <th class="border px-4 py-2">Component</th>
                            <th class="border px-4 py-2">Issue</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="border px-4 py-2">IT Personnel</th>
                            <th class="border px-4 py-2">Created</th>
                            <th class="border px-4 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="ticketsTable">
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition border-b">
                                <td class="px-4 py-2 font-medium">{{ $ticket->ticket_number }}</td>
                                <td class="px-4 py-2">{{ optional($ticket->agent)->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ optional($ticket->agent)->email ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ optional($ticket->teamLeader)->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ optional($ticket->component)->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ Str::limit($ticket->issue_description, 40) }}</td>

                                <!-- Status -->
                                <td class="px-4 py-2">
                                    @if ($ticket->status === 'pending')
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs">Pending</span>
                                    @elseif ($ticket->status === 'in_progress')
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">In Progress</span>
                                    @elseif ($ticket->status === 'resolved')
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">Resolved</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs">Unknown</span>
                                    @endif
                                </td>

                                <!-- IT Personnel -->
                                <td class="px-4 py-2">{{ $ticket->it_personnel_name ?? 'Unassigned' }}</td>

                                <!-- Created -->
                                <td class="px-4 py-2">{{ $ticket->created_at->format('Y-m-d H:i') }}</td>

                                <!-- Action -->
                                <td class="px-4 py-2 text-center">
                                    <button 
                                        onclick="openModal({{ $ticket->id }}, '{{ $ticket->status }}', @json($ticket->it_personnel_name))"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition">
                                        ✏️ Update
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-6 text-gray-500 italic">No tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 🔹 Update Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center hidden z-50">
    <div id="modalContent"
         class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md transform scale-95 opacity-0 transition-all duration-200">

        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Update Ticket</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">✖</button>
        </div>

        <!-- Update Form -->
        <form id="updateForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label for="statusField" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="statusField" name="status" class="w-full border rounded px-3 py-2">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="itPersonnelField" class="block text-sm font-medium text-gray-700">IT Personnel</label>
                <input type="text" id="itPersonnelField" name="it_personnel_name" class="w-full border rounded px-3 py-2" />
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-400 rounded text-white">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 rounded text-white">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- 🔹 Scripts -->
<script>
    function openModal(ticketId, currentStatus, itPersonnel) {
        const modal = document.getElementById('updateModal');
        const content = document.getElementById('modalContent');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 100);

        document.getElementById('updateForm').action = `/tickets/${ticketId}`;
        document.getElementById('statusField').value = currentStatus;
        document.getElementById('itPersonnelField').value = itPersonnel ?? '';
    }

    function closeModal() {
        const modal = document.getElementById('updateModal');
        const content = document.getElementById('modalContent');

        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // AJAX Update Form
    document.getElementById('updateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = this;

        fetch(form.action, {
            method: "POST", // Laravel requires POST + _method=PATCH
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Accept": "application/json",
            },
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeModal();
                refreshTickets();
            }
        })
        .catch(err => console.error(err));
    });

    // Refresh ticket table via AJAX
    function refreshTickets() {
        fetch("{{ route('dashboard') }}")
            .then(res => res.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, "text/html");
                let newTable = doc.querySelector("#ticketsTable").innerHTML;
                document.querySelector("#ticketsTable").innerHTML = newTable;
            })
            .catch(err => console.error(err));
    }
</script>
@endsection
