<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">🎫 Tickets List</h2>

        {{-- ✅ Filters --}}
        <form method="GET" action="{{ route('dashboard') }}" class="flex space-x-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search tickets..."
                   class="border rounded px-3 py-1 text-sm dark:bg-gray-700 dark:text-white">

            <select name="status"
                    class="border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:text-white">
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

    {{-- ✅ Tickets Table --}}
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-700 text-left">
                <th class="border px-4 py-2">#</th>
                <th class="border px-4 py-2">Ticket No.</th>
                <th class="border px-4 py-2">Issue</th>
                <th class="border px-4 py-2">Component</th>
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

                    <td class="border px-4 py-2">
                        {{ $ticket->component->name ?? '—' }}
                    </td>

                    <td class="border px-4 py-2">
                        {{ $ticket->agent->name ?? '—' }}
                        @if(!empty($ticket->agent?->email))
                            <br><span class="text-xs text-gray-500">{{ $ticket->agent->email }}</span>
                        @endif
                    </td>

                    <td class="border px-4 py-2">
                        {{ $ticket->teamLeader->name ?? '—' }}
                    </td>

                    <td class="ticket-it border px-4 py-2">
                        {{ $ticket->itPersonnel->name ?? '—' }}
                    </td>

                    <td class="ticket-status border px-4 py-2">
                        {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                    </td>

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
                    <td colspan="9" class="text-center p-4">No tickets yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
