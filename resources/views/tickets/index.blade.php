<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Tickets List
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @php($status = request('status'))
        <div class="mb-4 flex items-center space-x-2">
            <a href="{{ route('tickets.index') }}"
               class="px-3 py-2 rounded {{ !$status ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">All</a>
            <a href="{{ route('tickets.index', ['status' => 'Pending']) }}"
               class="px-3 py-2 rounded {{ $status === 'Pending' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Pending</a>
            <a href="{{ route('tickets.index', ['status' => 'In Progress']) }}"
               class="px-3 py-2 rounded {{ $status === 'In Progress' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">In Progress</a>
            <a href="{{ route('tickets.index', ['status' => 'Resolved']) }}"
               class="px-3 py-2 rounded {{ $status === 'Resolved' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Resolved</a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="p-3 text-left">Ticket #</th>
                        <th class="p-3 text-left">Agent</th>
                        <th class="p-3 text-left">Team Leader</th>
                        <th class="p-3 text-left">Component</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="border-b">
                            <td class="p-3">{{ $ticket->ticket_number }}</td>
                            <td class="p-3">{{ $ticket->agent_name }}<br><span class="text-xs text-gray-500">{{ $ticket->agent_email }}</span></td>
                            <td class="p-3">{{ $ticket->team_leader_name }}</td>
                            <td class="p-3">{{ $ticket->component }}</td>
                            <td class="p-3">{{ $ticket->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-3 text-center text-gray-500">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
