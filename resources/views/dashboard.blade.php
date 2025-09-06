@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <!-- 🔹 Ticket Stats Panel -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-5 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-yellow-800">Pending</h3>
            <p class="text-3xl font-bold text-yellow-700">{{ $pendingCount }}</p>
        </div>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-5 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-blue-800">In Progress</h3>
            <p class="text-3xl font-bold text-blue-700">{{ $inProgressCount }}</p>
        </div>
        <div class="bg-green-50 border-l-4 border-green-500 p-5 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-green-800">Resolved</h3>
            <p class="text-3xl font-bold text-green-700">{{ $resolvedCount }}</p>
        </div>
        <div class="bg-gray-50 border-l-4 border-gray-500 p-5 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-800">Total</h3>
            <p class="text-3xl font-bold text-gray-700">
                {{ $pendingCount + $inProgressCount + $resolvedCount }}
            </p>
        </div>
    </div>

    <!-- 🔹 Filter Tabs -->
    <div class="flex space-x-3 mb-6">
        @php
            $tabs = [
                'All' => null,
                'Pending' => 'pending',
                'In Progress' => 'in_progress',
                'Resolved' => 'resolved',
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
                            <th class="border px-4 py-2">Team Leader</th>
                            <th class="border px-4 py-2">Component</th>
                            <th class="border px-4 py-2">Issue</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="border px-4 py-2">Assigned IT Personnel</th>
                            <th class="border px-4 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="border px-4 py-2 font-medium text-gray-800">{{ $ticket->ticket_number }}</td>
                                <td class="border px-4 py-2">{{ $ticket->agent->name ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $ticket->teamLeader->name ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $ticket->component->name ?? '-' }}</td>
                                <td class="border px-4 py-2 text-gray-700">{{ $ticket->issue_description }}</td>

                                <!-- Status with badges -->
                                <td class="border px-4 py-2">
                                    @if ($ticket->status === 'pending')
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                                    @elseif ($ticket->status === 'in_progress')
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">In Progress</span>
                                    @elseif ($ticket->status === 'resolved')
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Resolved</span>
                                    @endif
                                </td>

                                <!-- IT Personnel -->
                                <td class="border px-4 py-2">
                                    <span class="{{ $ticket->it_personnel_name ? 'text-gray-800' : 'italic text-gray-500' }}">
                                        {{ $ticket->it_personnel_name ?? 'Unassigned' }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="border px-4 py-2 text-center">
                                    <button 
                                        onclick="openModal({{ $ticket->id }}, '{{ $ticket->status }}', @json($ticket->it_personnel_name))"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow transition">
                                        ✏️ Update
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-500 italic">No tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 🔹 Update Ticket Modal -->
    <div id="updateModal" 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 transform transition-all scale-95 opacity-0"
             id="modalContent">
            <h2 class="text-lg font-bold mb-4">Update Ticket</h2>

            <form id="updateForm" method="POST">
                @csrf
                @method('PATCH')

                <!-- Status -->
                <label class="block mb-2 font-semibold">Status</label>
                <input type="text" 
                       name="status" 
                       id="statusField"
                       class="w-full border rounded px-3 py-2 mb-4"
                       placeholder="Enter status (pending, in_progress, resolved)">

                <!-- IT Personnel Name -->
                <label class="block mb-2 font-semibold">Assigned IT Personnel</label>
                <input type="text" 
                       name="it_personnel_name" 
                       id="itPersonnelField"
                       class="w-full border rounded px-3 py-2 mb-4"
                       placeholder="Enter IT Personnel Name">

                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full shadow transition">
                    💾 Save Changes
                </button>
            </form>

            <button onclick="closeModal()" 
                    class="mt-3 text-gray-600 hover:text-gray-800 w-full text-center">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- 🔹 Modal Script -->
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
</script>
@endsection
