<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">

    {{-- ✅ Header --}}
    <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-3">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            🎫 Tickets List
        </h2>
    </div>

    {{-- ✅ Tickets Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    <th class="border px-4 py-2">#</th>
                    <th class="border px-4 py-2">Ticket No.</th>
                    <th class="border px-4 py-2">Issue</th>
                    <th class="border px-4 py-2">Component</th>
                    <th class="border px-4 py-2">Agent</th>
                    <th class="border px-4 py-2">Team Leader</th>
                    <th class="border px-4 py-2">IT Personnel</th>
                    <th class="border px-4 py-2">Status</th>
                    <th class="border px-4 py-2">Created At</th>
                    <th class="border px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>

            <tbody id="ticketTableBody">
                @forelse($tickets as $ticket)
                    <tr id="ticket-row-{{ $ticket->id }}"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">

                        {{-- ID --}}
                        <td class="border px-4 py-2">{{ $ticket->id }}</td>

                        {{-- Ticket Number --}}
                        <td class="border px-4 py-2 font-semibold">
                            {{ $ticket->ticket_number }}
                        </td>

                        {{-- Issue --}}
                        <td class="ticket-issue border px-4 py-2">
                            {{ $ticket->issue_description }}
                        </td>

                        {{-- Component --}}
                        <td class="border px-4 py-2">
                            {{ $ticket->component->name ?? '—' }}
                        </td>

                        {{-- Agent --}}
                        <td class="border px-4 py-2">
                            {{ $ticket->agent->name ?? '—' }}
                            @if(!empty($ticket->agent?->email))
                                <br><span class="text-xs text-gray-500">{{ $ticket->agent->email }}</span>
                            @endif
                        </td>

                        {{-- Team Leader --}}
                        <td class="border px-4 py-2">
                            {{ $ticket->teamLeader->name ?? '—' }}
                        </td>

                        {{-- IT Personnel --}}
                        <td class="ticket-it border px-4 py-2">
                            {{ $ticket->itPersonnel->name ?? '—' }}
                        </td>

                        {{-- Status --}}
                        <td class="ticket-status border px-4 py-2">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($ticket->status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-700
                                @elseif($ticket->status === 'resolved') bg-green-100 text-green-700
                                @else bg-gray-200 text-gray-700 @endif">
                                {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                            </span>
                        </td>

                        {{-- Created At --}}
                        <td class="border px-4 py-2 text-xs text-gray-600 dark:text-gray-400">
                            <span title="{{ $ticket->created_at->format('F j, Y \a\t g:i A') }}">
                                {{ $ticket->created_at->diffForHumans() }}
                            </span>
                            <br>
                            <span class="text-xs opacity-75">
                                {{ $ticket->created_at->format('M j, Y') }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="border px-4 py-2 text-center">
                            <button type="button"
                                    class="open-assign px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs transition"
                                    data-id="{{ $ticket->id }}">
                                Assign / Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center p-4 text-gray-500 dark:text-gray-400">
                            No tickets found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ✅ Pagination (works with paginate()) --}}
    @if($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $tickets->appends(request()->query())->links() }}
        </div>
    @endif

</div>
