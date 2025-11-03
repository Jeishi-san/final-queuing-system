{{-- ✅ Ticket Statistics Overview --}}
<div id="statsPanel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @php
        $totalTickets = $stats['total'] ?? 0;
        
        // Define stats in the expected format for the blade template
        $formattedStats = [
            [
                'label' => 'Pending',
                'count' => $stats['pending'] ?? 0,
                'color' => 'yellow',
                'icon' => '⏳'
            ],
            [
                'label' => 'In Progress', 
                'count' => $stats['in_progress'] ?? 0,
                'color' => 'blue',
                'icon' => '🔄'
            ],
            [
                'label' => 'Resolved',
                'count' => $stats['resolved'] ?? 0, 
                'color' => 'green',
                'icon' => '✅'
            ],
            [
                'label' => 'Overdue',
                'count' => $stats['overdue'] ?? 0,
                'color' => 'red', 
                'icon' => '⚠️'
            ]
        ];
        
        $colorClasses = [
            'Pending' => 'yellow',
            'In Progress' => 'blue',
            'Resolved' => 'green',
            'Overdue' => 'red',
        ];
    @endphp

    @foreach ($formattedStats as $stat)
        @php
            $label = $stat['label'];
            $count = $stat['count'];
            $color = $stat['color'];
            $icon = $stat['icon'];
            $percentage = $totalTickets > 0 ? round(($count / $totalTickets) * 100) : 0;

            // Safe color classes - using static classes instead of dynamic strings
            $bgLight = [
                'yellow' => 'bg-yellow-100 dark:bg-yellow-900/30',
                'blue' => 'bg-blue-100 dark:bg-blue-900/30', 
                'green' => 'bg-green-100 dark:bg-green-900/30',
                'red' => 'bg-red-100 dark:bg-red-900/30'
            ][$color] ?? 'bg-gray-100 dark:bg-gray-900/30';
            
            $bgColor = [
                'yellow' => 'bg-yellow-500',
                'blue' => 'bg-blue-500',
                'green' => 'bg-green-500', 
                'red' => 'bg-red-500'
            ][$color] ?? 'bg-gray-500';
            
            $textHover = [
                'yellow' => 'group-hover:text-yellow-600 dark:group-hover:text-yellow-400',
                'blue' => 'group-hover:text-blue-600 dark:group-hover:text-blue-400',
                'green' => 'group-hover:text-green-600 dark:group-hover:text-green-400',
                'red' => 'group-hover:text-red-600 dark:group-hover:text-red-400'
            ][$color] ?? 'group-hover:text-gray-600 dark:group-hover:text-gray-400';
        @endphp

        <div class="group relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 
                    rounded-2xl shadow-md p-6 hover:shadow-xl transition-all duration-300 
                    hover:scale-105">

            {{-- Background Gradient --}}
            <div class="absolute inset-0 opacity-5">
                <div class="{{ $bgColor }} rounded-2xl"></div>
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
                <div class="{{ $bgColor }} h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $percentage }}%"></div>
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