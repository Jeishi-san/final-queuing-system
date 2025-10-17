<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- 🟡 Pending --}}
    <div class="group bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/20 p-5 rounded-2xl shadow-sm border border-yellow-200 dark:border-yellow-700/40 hover:shadow-lg transition-all duration-300">
        <header class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-10 h-10 bg-yellow-100 dark:bg-yellow-800 rounded-lg">
                <span class="text-lg">⏳</span>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">Pending Queue</h2>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                    <span class="font-medium">{{ $pendingTickets->count() }}</span> ticket(s) awaiting response
                </p>
            </div>
        </header>

        <div class="space-y-3 max-h-80 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-yellow-300 dark:scrollbar-thumb-yellow-700">
            @forelse($pendingTickets as $ticket)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-yellow-200 dark:border-yellow-700 hover:shadow-md hover:scale-[1.02] transition-all duration-200">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                #{{ $ticket->ticket_number }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                ID:{{ $ticket->id }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->created_at->format('M d') }}</span>
                    </div>

                    <p class="text-sm text-gray-800 dark:text-gray-200 font-medium mb-2 line-clamp-2">
                        {{ $ticket->issue_description }}
                    </p>

                    <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                      clip-rule="evenodd"></path>
                            </svg>
                            {{ $ticket->created_at->diffForHumans() }}
                        </span>
                        @if($ticket->component)
                            <span class="bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-100 px-2 py-1 rounded">
                                {{ $ticket->component->name }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-yellow-600 dark:text-yellow-300">
                    <div class="w-12 h-12 mx-auto mb-3 bg-yellow-100 dark:bg-yellow-800 rounded-full flex items-center justify-center">
                        🎉
                    </div>
                    <p class="font-semibold">All clear!</p>
                    <p class="text-sm">No pending tickets</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 🔵 In Progress --}}
    <div class="group bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 p-5 rounded-2xl shadow-sm border border-blue-200 dark:border-blue-700/40 hover:shadow-lg transition-all duration-300">
        <header class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-lg">
                <span class="text-lg">🔄</span>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-blue-800 dark:text-blue-200">In Progress</h2>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    <span class="font-medium">{{ $inProgressTickets->count() }}</span> ticket(s) being resolved
                </p>
            </div>
        </header>

        <div class="space-y-3 max-h-80 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-blue-300 dark:scrollbar-thumb-blue-700">
            @forelse($inProgressTickets as $ticket)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-blue-200 dark:border-blue-700 hover:shadow-md hover:scale-[1.02] transition-all duration-200">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="bg-blue-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                #{{ $ticket->ticket_number }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                ID:{{ $ticket->id }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->created_at->format('M d') }}</span>
                    </div>

                    <p class="text-sm text-gray-800 dark:text-gray-200 font-medium mb-2 line-clamp-2">
                        {{ $ticket->issue_description }}
                    </p>

                    <div class="space-y-2">
                        @if($ticket->itPersonnel)
                            <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                                👨‍💻 <span class="font-medium">{{ $ticket->itPersonnel->name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                ⏱️ {{ $ticket->created_at->diffForHumans() }}
                            </span>
                            @if($ticket->component)
                                <span class="bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 px-2 py-1 rounded">
                                    {{ $ticket->component->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-blue-600 dark:text-blue-300">
                    <div class="w-12 h-12 mx-auto mb-3 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                        💤
                    </div>
                    <p class="font-semibold">Quiet period</p>
                    <p class="text-sm">No active tickets</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ✅ Resolved --}}
    <div class="group bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/20 p-5 rounded-2xl shadow-sm border border-green-200 dark:border-green-700/40 hover:shadow-lg transition-all duration-300">
        <header class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-800 rounded-lg">
                <span class="text-lg">✅</span>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-green-800 dark:text-green-200">Resolved</h2>
                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                    <span class="font-medium">{{ $resolvedTickets->count() }}</span> ticket(s) completed
                </p>
            </div>
        </header>

        <div class="space-y-3 max-h-80 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-green-300 dark:scrollbar-thumb-green-700">
            @forelse($resolvedTickets as $ticket)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-green-200 dark:border-green-700 hover:shadow-md hover:scale-[1.02] transition-all duration-200">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="bg-green-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                #{{ $ticket->ticket_number }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                ID:{{ $ticket->id }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->updated_at->format('M d') }}</span>
                    </div>

                    <p class="text-sm text-gray-800 dark:text-gray-200 font-medium mb-2 line-clamp-2">
                        {{ $ticket->issue_description }}
                    </p>

                    <div class="space-y-2">
                        @if($ticket->itPersonnel)
                            <div class="flex items-center gap-2 text-xs text-green-600 dark:text-green-400">
                                🧰 Resolved by <span class="font-medium">{{ $ticket->itPersonnel->name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                ⏱️ {{ $ticket->updated_at->diffForHumans() }}
                            </span>
                            @if($ticket->component)
                                <span class="bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 px-2 py-1 rounded">
                                    {{ $ticket->component->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-green-600 dark:text-green-300">
                    <div class="w-12 h-12 mx-auto mb-3 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                        📝
                    </div>
                    <p class="font-semibold">All done!</p>
                    <p class="text-sm">No resolved tickets yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
