    {{-- ✅ Stats Section --}}
    <div id="statsPanel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($stats as $stat)
            <div class="bg-{{ $stat['color'] }}-50 border-l-4 border-{{ $stat['color'] }}-500 p-5 rounded-lg shadow hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-{{ $stat['color'] }}-800">
                        {{ $stat['icon'] }} {{ $stat['label'] }}
                    </h3>
                </div>
                <p class="text-3xl font-bold text-{{ $stat['color'] }}-700 mt-2">
                    {{ $stat['count'] }}
                </p>
            </div>
        @endforeach
    </div>