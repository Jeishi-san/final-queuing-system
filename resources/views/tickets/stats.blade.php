{{-- ✅ Ticket Statistics Overview --}}
<div id="statsPanel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @php
        $totalTickets = collect($stats)->sum('count');
        $colorClasses = [
            'Pending' => 'yellow',
            'In Progress' => 'blue',
            'Resolved' => 'green',
            'Overdue' => 'red',
        ];
    @endphp

    @foreach ($stats as $stat)
        @php
            $label = $stat['label'];
            $count = $stat['count'];
            $color = $colorClasses[$label] ?? 'gray';
            $icon = $stat['icon'] ?? '';
            $percentage = $totalTickets > 0 ? round(($count / $totalTickets) * 100) : 0;

            // Tailwind-safe classes for dynamic colors
            $bgLight = "bg-{$color}-100 dark:bg-{$color}-900/30";
            $bg = "bg-{$color}-500";
            $textHover = "group-hover:text-{$color}-600 dark:group-hover:text-{$color}-400";
        @endphp

        <div class="group relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                    rounded-2xl shadow-md p-6 hover:shadow-xl transition-all duration-300 
                    hover:scale-105">

            {{-- Background Gradient --}}
            <div class="absolute inset-0 opacity-5">
                <div class="{{ $bg }} rounded-2xl"></div>
            </div>

            {{-- Icon + Label --}}
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 {{ $bgLight }} rounded-xl flex items-center justify-center text-2xl">
                        {!! $icon !!}
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ $label }}</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $percentage }}%</span>
                    </div>
                </div>
            </div>

            {{-- Count --}}
            <div class="mb-2 relative z-10">
                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-colors {{ $textHover }}">
                    {{ $count }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ Str::plural('ticket', $count) }}</p>
            </div>

            {{-- Progress Bar --}}
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2 overflow-hidden relative z-10" aria-label="{{ $label }} progress bar">
                <div class="{{ $bg }} h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $percentage }}%"></div>
            </div>

            {{-- Description --}}
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-2 relative z-10">
                @switch($label)
                    @case('Pending')
                        Awaiting assignment or IT review
                        @break
                    @case('In Progress')
                        Currently being worked on by IT staff
                        @break
                    @case('Resolved')
                        Successfully resolved tickets
                        @break
                    @case('Overdue')
                        Tickets pending for more than 24 hours
                        @break
                    @default
                        General system tracking
                @endswitch
            </div>
        </div>
    @endforeach
</div>
