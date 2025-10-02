@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 space-y-8">

    {{-- ✅ Stats Panel --}}
    @include('tickets.stats', ['stats' => $stats])

    {{-- ✅ Tickets Table --}}
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">🎫 Tickets List</h2>

            {{-- ✅ Filters --}}
            <form method="GET" action="{{ route('dashboard') }}" class="flex space-x-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search tickets..."
                       class="border rounded px-3 py-1 text-sm dark:bg-gray-700 dark:text-white">

                <select name="status" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:text-white">
                    <option value="">All</option>
                    <option value="pending"     {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="resolved"    {{ request('status')=='resolved'?'selected':'' }}>Resolved</option>
                </select>

                <button type="submit"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                    Filter
                </button>
            </form>
        </div>

        {{-- ✅ Table --}}
        <table class="w-full border-collapse">
            <thead>
            <tr class="bg-gray-100 dark:bg-gray-700 text-left">
                <th class="border px-4 py-2">#</th>
                <th class="border px-4 py-2">Ticket No.</th>
                <th class="border px-4 py-2">Issue</th>
                <th class="border px-4 py-2">Agent</th>
                <th class="border px-4 py-2">Team Leader</th>
                <th class="border px-4 py-2">IT Personnel</th>
                <th class="border px-4 py-2">Status</th>
                <th class="border px-4 py-2 text-center">Actions</th>
            </tr>
            </thead>
            <tbody id="ticketTableBody">
            @forelse($tickets as $ticket)
                <tr id="ticket-row-{{ $ticket->id }}">
                    <td class="border px-4 py-2">{{ $ticket->id }}</td>
                    <td class="border px-4 py-2">{{ $ticket->ticket_number }}</td>
                    <td class="ticket-issue border px-4 py-2">{{ $ticket->issue_description }}</td>

                    {{-- ✅ Agent --}}
                    <td class="border px-4 py-2">
                        {{ $ticket->agent?->name ?? '—' }}
                        @if($ticket->agent?->email)
                            <br><span class="text-xs text-gray-500">{{ $ticket->agent->email }}</span>
                        @endif
                    </td>

                    {{-- ✅ Team Leader --}}
                    <td class="border px-4 py-2">
                        {{ $ticket->teamLeader?->name ?? '—' }}
                    </td>

                    {{-- ✅ IT Personnel --}}
                    <td class="ticket-it border px-4 py-2">
                        {{ $ticket->itPersonnel?->name ?? '—' }}
                    </td>

                    {{-- ✅ Status --}}
                    <td class="ticket-status border px-4 py-2">
                        {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                    </td>

                    {{-- ✅ Actions --}}
                    <td class="border px-4 py-2 text-center">
                        <button type="button"
                                class="open-assign px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
                                data-id="{{ $ticket->id }}">
                            Assign / Edit
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center p-4">No tickets yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{-- ✅ Pagination --}}
        <div class="mt-4">
            {{ $tickets->links() }}
        </div>
    </div>

    {{-- ✅ Assign Modal --}}
    <div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-2xl shadow-lg relative">

            {{-- Close Button --}}
            <button id="assignModalClose" 
                    class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-lg">
                ✖
            </button>

            {{-- Dynamic Content --}}
            <div id="assignFormContainer">
                {{-- Loaded dynamically from assign.blade.php --}}
            </div>
        </div>
    </div>
</div>

{{-- ✅ Toast Notification --}}
<div id="toast"
     class="fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white hidden z-50">
</div>

{{-- ✅ JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const assignModal      = document.getElementById('assignModal');
    const assignClose      = document.getElementById('assignModalClose');
    const assignContainer  = document.getElementById('assignFormContainer');
    const toast            = document.getElementById('toast');

    // ✅ Toast function
    function showToast(message, type = 'success') {
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white z-50 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // ✅ Open modal and load assign form
    async function openAssignModal(ticketId) {
        try {
            const resp = await fetch(`/tickets/${ticketId}/assign`);
            if (!resp.ok) throw new Error('Failed to load form');
            
            assignContainer.innerHTML = await resp.text();
            assignModal.style.display = 'flex';
            
            // Initialize the loaded form functionality
            initializeAssignForm();
        } catch (err) {
            console.error('❌ Failed to load the assign form:', err);
            showToast('⚠️ Unable to load the assign form.', 'error');
        }
    }

    // ✅ Close modal
    function closeAssignModal() {
        assignModal.style.display = 'none';
        assignContainer.innerHTML = ''; // Clear content
    }

    // ✅ Initialize the loaded assign form
    function initializeAssignForm() {
        const panel = document.getElementById('ticketPanel');
        const cancelBtn = document.getElementById('panelCancel');
        const form = document.getElementById('updateTicketForm');

        if (!panel || !cancelBtn || !form) return;

        function closePanel() {
            closeAssignModal(); // Close the main modal when panel closes
        }

        // Close panel actions
        cancelBtn.addEventListener('click', closePanel);

        // Close when clicking outside the panel
        panel.addEventListener('click', (e) => {
            if (e.target === panel) {
                closePanel();
            }
        });

        // Close with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && assignModal.style.display === 'flex') {
                closePanel();
            }
        });

        // Submit via AJAX
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            if (!formData.has('_method')) formData.append('_method', 'PATCH');

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                const data = await resp.json();

                if (resp.ok && data.success) {
                    showToast('✅ Ticket updated successfully', 'success');
                    closePanel();

                    // ✅ Update the table row with new data
                    updateTicketRow(data.ticket);
                    
                } else {
                    showToast(data.message || 'Update failed', 'error');
                }
            } catch (err) {
                console.error('Error:', err);
                showToast('⚠️ Error while updating ticket', 'error');
            }
        });
    }

    // ✅ Update table row with new ticket data
    function updateTicketRow(ticket) {
        const row = document.getElementById(`ticket-row-${ticket.id}`);
        if (!row) return;

        // ✅ Update IT Personnel (use relation object)
        const itCell = row.querySelector('.ticket-it');
        if (itCell) {
            itCell.textContent = ticket.it_personnel
                ? ticket.it_personnel.name
                : '—';
        }

        // ✅ Update Status
        const statusCell = row.querySelector('.ticket-status');
        if (statusCell) {
            statusCell.textContent = ticket.status
                ? ticket.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
                : '—';
        }
    }

    // ✅ Event delegation for opening modal
    document.addEventListener('click', (e) => {
        if (e.target.closest('.open-assign')) {
            const ticketId = e.target.closest('.open-assign').dataset.id;
            openAssignModal(ticketId);
        }
    });

    // ✅ Close modal on close button
    assignClose.addEventListener('click', closeAssignModal);

    // ✅ Close modal when clicking outside
    assignModal.addEventListener('click', (e) => {
        if (e.target === assignModal) {
            closeAssignModal();
        }
    });

    // ✅ Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && assignModal.style.display === 'flex') {
            closeAssignModal();
        }
    });
});
</script>
@endsection
