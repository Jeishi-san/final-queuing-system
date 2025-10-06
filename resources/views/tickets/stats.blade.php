{{-- ✅ Stats Section --}}
<div id="statsPanel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach ($stats as $stat)
        <div
            class="relative bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-{{ $stat['color'] }}-500 hover:shadow-lg transition-shadow duration-200">

            {{-- ✅ Icon + Label --}}
            <div class="flex items-center justify-between">
                <h3 class="flex items-center space-x-2 text-lg font-semibold text-{{ $stat['color'] }}-800 dark:text-{{ $stat['color'] }}-300">
                    <span class="text-2xl">{!! $stat['icon'] !!}</span>
                    <span>{{ $stat['label'] }}</span>
                </h3>
            </div>

            {{-- ✅ Count --}}
            <p class="text-4xl font-extrabold text-{{ $stat['color'] }}-700 dark:text-{{ $stat['color'] }}-400 mt-3">
                {{ $stat['count'] }}
            </p>

            {{-- ✅ Optional Tooltip or Description --}}
            @if(!empty($stat['description']))
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $stat['description'] }}
                </p>
            @endif
        </div>
    @endforeach
</div>
