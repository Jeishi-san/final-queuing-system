{{-- ✅ Stats Section --}}
<div id="statsPanel"
     class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    @foreach ($stats as $stat)
        <div
            class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border-l-4 border-{{ $stat['color'] }}-500 transition-all duration-300 hover:shadow-xl">

            {{-- ✅ Icon & Label --}}
            <div class="flex items-center justify-between">
                <h3 class="flex items-center space-x-2 text-lg font-semibold
                           text-{{ $stat['color'] }}-800 dark:text-{{ $stat['color'] }}-300">
                    <span class="text-3xl">{!! $stat['icon'] !!}</span>
                    <span>{{ $stat['label'] }}</span>
                </h3>
            </div>

            {{-- ✅ Count --}}
            <p class="mt-4 text-4xl font-extrabold tracking-tight
                      text-{{ $stat['color'] }}-700 dark:text-{{ $stat['color'] }}-400">
                {{ $stat['count'] }}
            </p>

            {{-- ✅ Optional Description --}}
            @if (!empty($stat['description']))
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-snug">
                    {{ $stat['description'] }}
                </p>
            @endif

            {{-- ✅ Hover Accent Bar (Optional Decorative) --}}
            <span class="absolute inset-x-0 bottom-0 h-1 bg-{{ $stat['color'] }}-500
                         scale-x-0 group-hover:scale-x-100 transform origin-left transition-transform duration-300"></span>
        </div>
    @endforeach
</div>
