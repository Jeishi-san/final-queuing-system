<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            🎫 Tickets List
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @php($status = request('status'))

        {{-- ✅ Status Filters --}}
        <div class="mb-4 flex items-center space-x-2">
            <a href="{{ route('tickets.index') }}"
               class="px-3 py-2 rounded {{ !$status ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-gray-200' }}">
               All
            </a>
            <a href="{{ route('tickets.index', ['status' => 'pending']) }}"
               class="px-3 py-2 rounded {{ $status === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-gray-200' }}">
               Pending
            </a>
            <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}"
               class="px-3 py-2 rounded {{ $status === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-gray-200' }}">
               In Progress
            </a>
            <a href="{{ route('tickets.index', ['status' => 'resolved']) }}"
               class="px-3 py-2 rounded {{ $status === 'resolved' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-gray-200' }}">
               Resolved
            </a>
        </div>

        {{-- ✅ Tickets Table --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-medium text-gray-700 dark:text-gray-200">
                        <th class="p-3">Ticket #</th>
                        <th class="p-3">Agent</th>
                        <th class="p-3">Team Leader</th>
                        <th class="p-3">Component</th>
                        <th class="p-3">IT Personnel</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($tickets as $ticket)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{-- Ticket Number --}}
                            <td class="p-3 font-medium text-gray-800 dark:text-gray-100">
                                {{ $ticket->ticket_number }}
                            </td>

                            {{-- Agent --}}
                            <td class="p-3">
                                {{ $ticket->agent?->name ?? '—' }}
                                @if($ticket->agent?->email)
                                    <br><span class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->agent->email }}</span>
                                @endif
                            </td>

                            {{-- Team Leader --}}
                            <td class="p-3">
                                {{ $ticket->teamLeader?->name ?? '—' }}
                            </td>

                            {{-- Component --}}
                            <td class="p-3">
                                {{ $ticket->component?->name ?? '—' }}
                            </td>

                            {{-- IT Personnel --}}
                            <td class="p-3">
                                {{ $ticket->itPersonnel?->name ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="p-3">
                                <span class="
                                    px-2 py-1 rounded text-xs font-semibold
                                    @if($ticket->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-700
                                    @else bg-green-100 text-green-700
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_',' ', $ticket->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                No tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- ✅ Pagination --}}
            <div class="mt-6">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
