{{-- resources/views/tickets/tables.blade.php --}}
@if($tickets->count())
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
                @foreach($tickets as $ticket)
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
                            @if(!empty($ticket->teamLeader?->email))
                                <br><span class="text-xs text-gray-500">{{ $ticket->teamLeader->email }}</span>
                            @endif
                        </td>

                        {{-- IT Personnel --}}
                        <td class="ticket-it border px-4 py-2">
                            {{ $ticket->itPersonnel->name ?? '—' }}
                            @if(!empty($ticket->itPersonnel?->email))
                                <br><span class="text-xs text-gray-500">{{ $ticket->itPersonnel->email }}</span>
                            @endif
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
                                    class="openAssignModal px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs transition"
                                    data-ticket-id="{{ $ticket->id }}">
                                Assign / Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ✅ Pagination with corrected path --}}
    @if($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4 flex justify-center">
            <div class="pagination">
                {!! $tickets->appends(request()->query())->setPath(route('dashboard.ticketsTable'))->links() !!}
            </div>
        </div>
    @endif
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        No tickets found.
    </div>
@endif