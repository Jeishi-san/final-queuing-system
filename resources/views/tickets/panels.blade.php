<div class="space-y-6">

    {{-- ✅ Stats Section --}}
    <div id="statsPanel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($stats as $stat)
            <div
                class="bg-{{ $stat['color'] }}-50 border-l-4 border-{{ $stat['color'] }}-500 
                       p-5 rounded-lg shadow hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-{{ $stat['color'] }}-800">
                        {!! $stat['icon'] !!} {{ $stat['label'] }}
                    </h3>
                </div>

                <p class="text-3xl font-bold text-{{ $stat['color'] }}-700 mt-2">
                    {{ $stat['count'] }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- ✅ Tickets List --}}
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
            🎫 Tickets
        </h2>

        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($tickets as $ticket)
                <li class="py-3">
                    <div class="flex justify-between items-center">

                        {{-- Ticket Info --}}
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-100">
                                {{ $ticket->ticket_number }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $ticket->issue_description }}
                            </p>
                        </div>

                        {{-- Status Badge --}}
                        <span class="px-3 py-1 rounded-full text-sm
                            @if($ticket->status === 'pending') bg-yellow-100 text-yellow-700
                            @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-700
                            @else bg-green-100 text-green-700 @endif">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                </li>
            @empty
                <li class="py-3 text-gray-500 dark:text-gray-400">
                    No tickets found.
                </li>
            @endforelse
        </ul>
    </div>
</div>
