{{-- resources/views/tickets/assign.blade.php --}}
<div class="p-6">

    {{-- ✅ Header --}}
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">
        📝 Assign / Update Ticket
    </h2>

    {{-- ✅ Ticket Details (Read-only) --}}
    <div class="mb-6 space-y-2 bg-gray-50 dark:bg-gray-800 rounded p-4 border border-gray-200 dark:border-gray-700">
        <p><strong>Ticket No:</strong> {{ $ticket->ticket_number }}</p>
        <p><strong>Issue:</strong> {{ $ticket->issue_description }}</p>
        <p><strong>Component:</strong> {{ $ticket->component->name ?? '—' }}</p>
        <p><strong>Agent:</strong> {{ $ticket->agent->name ?? '—' }}</p>
        <p><strong>Team Leader:</strong> {{ $ticket->teamLeader->name ?? '—' }}</p>
        <p><strong>IT Personnel:</strong> {{ $ticket->itPersonnel->name ?? 'Unassigned' }}</p>
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_',' ',$ticket->status)) }}</p>
        <p><strong>Created At:</strong> {{ $ticket->created_at->format('M d, Y h:i A') }}</p>
    </div>

    {{-- ✅ Update Form --}}
    <form id="updateTicketForm" method="POST" action="{{ route('tickets.update', $ticket->id) }}">
        @csrf
        @method('PATCH')

        {{-- Status --}}
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Status
            </label>
            <select id="status" name="status"
                class="w-full border rounded p-2 mt-1 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="pending"     {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved"    {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
        </div>

        {{-- IT Personnel --}}
        <div class="mb-4">
            <label for="it_personnel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Assign IT Personnel
            </label>
            <select id="it_personnel_id" name="it_personnel_id"
                class="w-full border rounded p-2 mt-1 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select IT Personnel --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $ticket->it_personnel_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-end space-x-3 mt-6">
            <button type="submit"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                Save
            </button>
        </div>
    </form>
</div>

{{-- ✅ Script --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form      = document.getElementById('updateTicketForm');
    const cancelBtn = document.getElementById('assignCancelBtn');
    const modal     = document.getElementById('assignModal');
    const toast     = document.getElementById('toast');

    function showToast(message, type='success') {
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white z-50 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        if (!formData.has('_method')) formData.append('_method', 'PATCH');

        try {
            const resp = await fetch(form.action, {
                method: 'POST', // Laravel PATCH via POST
                headers: { 'Accept': 'application/json' },
                body: formData
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                showToast('✅ Ticket updated successfully');

                modal.style.display = 'none';

                // ✅ Refresh the tickets table in dashboard
                const panelResp = await fetch(`{{ route('dashboard.ticketsTable') }}`);
                if (panelResp.ok) {
                    const html = await panelResp.text();
                    const temp = document.createElement('div');
                    temp.innerHTML = html;

                    const newTickets = temp.querySelector('#ticketTableContainer');
                    if (newTickets) {
                        document.getElementById('ticketTableContainer').innerHTML = newTickets.innerHTML;
                    }
                }
            } else {
                showToast(data.message || '⚠️ Update failed', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('⚠️ Error submitting form', 'error');
        }
    });
});
</script>
