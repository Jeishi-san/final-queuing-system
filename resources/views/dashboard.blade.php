@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 space-y-8">

    {{-- ✅ Stats Panel --}}
    @include('tickets.stats', ['stats' => $stats])

    {{-- ✅ Tickets Table --}}
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">🎫 Tickets List</h2>
            <form method="GET" action="{{ route('dashboard') }}" class="flex space-x-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search tickets..."
                    class="border rounded px-3 py-1 text-sm dark:bg-gray-700 dark:text-white">
                <select name="status" class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:text-white">
                    <option value="">All</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="resolved" {{ request('status')=='resolved'?'selected':'' }}>Resolved</option>
                </select>
                <button type="submit"
                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                    Filter
                </button>
            </form>
        </div>

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
            <tbody>
                @forelse($tickets as $ticket)
                    <tr id="ticket-row-{{ $ticket->id }}">
                        <td class="border px-4 py-2">{{ $ticket->id }}</td>
                        <td class="border px-4 py-2">{{ $ticket->ticket_number }}</td>
                        <td class="ticket-issue border px-4 py-2">{{ $ticket->issue_description }}</td>
                        <td class="border px-4 py-2">{{ $ticket->agent_name }}</td>
                        <td class="border px-4 py-2">{{ $ticket->team_leader_name }}</td>
                        <td class="ticket-it border px-4 py-2">
                            {{ $ticket->it_personnel_name ?? '—' }}
                        </td>
                        <td class="ticket-status border px-4 py-2">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            <button 
                                type="button"
                                class="open-ticket px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                                data-id="{{ $ticket->id }}"
                                data-ticket='@json($ticket)'>
                                View/Edit
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

    {{-- ✅ Floating Panel --}}
    <div id="ticketPanel" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-2xl shadow-lg relative">
        <button id="ticketPanelClose" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-lg">✖</button>

        <h2 class="text-2xl font-bold mb-4">📝 Ticket Details</h2>

        <form id="updateTicketForm" method="POST">
          @csrf
          @method('PATCH')

          <input type="hidden" name="ticket_id" id="ticket_id">

          <div class="mb-3">
            <label class="block text-sm font-medium">Ticket Number</label>
            <input type="text" id="panel_ticket_number" disabled
              class="w-full border rounded p-2 bg-gray-100 dark:bg-gray-700">
          </div>

          <div class="mb-3">
            <label class="block text-sm font-medium">Issue</label>
            <textarea id="panel_issue" disabled rows="3"
              class="w-full border rounded p-2 bg-gray-100 dark:bg-gray-700"></textarea>
          </div>

          <div class="mb-3">
            <label class="block text-sm font-medium">Status</label>
            <select name="status" id="panel_status"
              class="w-full border rounded p-2 bg-white dark:bg-gray-700">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="block text-sm font-medium">Assign IT Personnel</label>
            <input type="text" name="it_personnel_name" id="panel_it_personnel"
              class="w-full border rounded p-2 bg-white dark:bg-gray-700">
          </div>

          <div class="flex justify-end space-x-3 mt-4">
            <button type="button" id="panelCancel"
              class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</button>
            <button type="submit" id="panelSave"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Update</button>
          </div>
        </form>
      </div>
    </div>
</div>

{{-- ✅ Panel Logic --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const panel = document.getElementById('ticketPanel');
  const updateForm = document.getElementById('updateTicketForm');
  const closeBtn = document.getElementById('ticketPanelClose');
  const cancelBtn = document.getElementById('panelCancel');

  function formatStatus(status) {
    return status ? status.split('_').map(w => w[0].toUpperCase() + w.slice(1)).join(' ') : '';
  }

  function openTicketPanel(ticket) {
    panel.classList.remove('hidden');
    panel.classList.add('flex');

    document.getElementById('ticket_id').value = ticket.id || '';
    document.getElementById('panel_ticket_number').value = ticket.ticket_number || '';
    document.getElementById('panel_issue').value = ticket.issue_description || '';
    document.getElementById('panel_status').value = ticket.status || 'pending';
    document.getElementById('panel_it_personnel').value = ticket.it_personnel_name || '';

    updateForm.action = `/tickets/${ticket.id}`;
  }

  function closeTicketPanel() {
    panel.classList.add('hidden');
    panel.classList.remove('flex');
  }

  // Open panel when clicking "View/Edit"
  document.addEventListener('click', (e) => {
    if (e.target.closest('.open-ticket')) {
      const btn = e.target.closest('.open-ticket');
      const ticket = JSON.parse(btn.dataset.ticket);
      openTicketPanel(ticket);
    }
  });

  closeBtn.addEventListener('click', closeTicketPanel);
  cancelBtn.addEventListener('click', closeTicketPanel);

  // Handle form submit (AJAX update)
  updateForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(updateForm);
    if (!formData.has('_method')) formData.append('_method', 'PATCH');

    const token = updateForm.querySelector('[name="_token"]').value;

    try {
      const resp = await fetch(updateForm.action, {
        method: 'POST', // Laravel requires POST + _method=PATCH
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: formData
      });

      const data = await resp.json();

      if (resp.ok && data.success) {
        const row = document.querySelector(`#ticket-row-${data.ticket.id}`);
        if (row) {
          row.querySelector('.ticket-status').textContent = formatStatus(data.ticket.status);
          row.querySelector('.ticket-it').textContent = data.ticket.it_personnel_name ?? '—';
        }

        // refresh button dataset so next edit opens updated info
        const button = document.querySelector(`.open-ticket[data-id='${data.ticket.id}']`);
        if (button) button.dataset.ticket = JSON.stringify(data.ticket);

        closeTicketPanel();
      } else {
        alert('❌ ' + (data.message || 'Update failed'));
      }
    } catch (err) {
      console.error('Update error:', err);
      alert('⚠️ Error while updating — check console.');
    }
  });
});
</script>
@endsection
