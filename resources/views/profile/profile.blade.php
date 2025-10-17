@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 space-y-8">

    {{-- ✅ Profile Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
            👤 Profile
        </h1>

        <div class="space-y-2">
            <p class="text-gray-700 dark:text-gray-300">
                <strong>Name:</strong> {{ $user->name ?? 'N/A' }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>Email:</strong> {{ $user->email ?? 'N/A' }}
            </p>
        </div>
    </div>

    {{-- ✅ Activity Logs with Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                📝 Recent Activity
            </h2>

            {{-- 🔍 Filter Form --}}
            <form method="GET" action="{{ route('profile') }}" class="w-full sm:w-auto flex flex-wrap gap-3 items-end">
                {{-- Search --}}
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search action or ticket..."
                        class="w-full sm:w-48 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Action Type --}}
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Action</label>
                    <select name="action_type"
                        class="w-full sm:w-36 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">All</option>
                        <option value="Created" {{ request('action_type') == 'Created' ? 'selected' : '' }}>Created</option>
                        <option value="Updated" {{ request('action_type') == 'Updated' ? 'selected' : '' }}>Updated</option>
                        <option value="Assigned" {{ request('action_type') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="Closed" {{ request('action_type') == 'Closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Status</label>
                    <select name="status"
                        class="w-full sm:w-36 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">All</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                {{-- From Date --}}
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">From</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                        class="w-full sm:w-40 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- To Date --}}
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">To</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                        class="w-full sm:w-40 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Buttons --}}
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition">
                        Filter
                    </button>

                    <a href="{{ route('profile') }}"
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-md text-sm font-medium hover:bg-gray-400 dark:hover:bg-gray-600 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- 🧾 Activity Logs List --}}
        @if($logs->count())
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($logs as $log)
                    @php
                        // Identify action type for color badge
                        $badgeColor = match(true) {
                            str_contains($log->action, 'Assigned') => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            str_contains($log->action, 'Updated') => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            str_contains($log->action, 'Closed') => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            str_contains($log->action, 'Created') => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                        };
                    @endphp

                    <li class="py-4 flex justify-between items-start" id="log-{{ $log->id }}">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                    {{ Str::headline(Str::before($log->action, ' ')) }}
                                </span>
                                <p class="text-gray-800 dark:text-gray-200 font-medium">
                                    {{ $log->action ?? 'No action description' }}
                                </p>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if($log->ticket)
                                    Ticket:
                                    <a href="{{ route('dashboard', ['highlight' => $log->ticket->id]) }}"
                                       class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        #{{ $log->ticket->ticket_number ?? $log->ticket->id }}
                                    </a>
                                    @if($log->ticket->itPersonnel)
                                        • Handled by
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $log->ticket->itPersonnel->name }}
                                        </span>
                                    @else
                                        • <span class="text-gray-400">Unassigned</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">No linked ticket</span>
                                @endif
                            </p>
                        </div>

                        <span class="text-gray-500 dark:text-gray-400 text-sm">
                            @php $timestamp = $log->performed_at ?? $log->created_at; @endphp
                            {{ $timestamp ? $timestamp->diffForHumans() : 'No timestamp' }}
                        </span>
                    </li>
                @endforeach
            </ul>

            {{-- ✅ Pagination --}}
            @if($logs->hasPages())
                <div class="mt-6">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <p class="text-gray-500 dark:text-gray-400">
                No activity logs found.
            </p>
        @endif
    </div>
</div>

{{-- ✅ Highlight Script --}}
@if(request()->has('highlight'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ticketId = '{{ request('highlight') }}';
        const selectors = [`#ticket-row-${ticketId}`, `#log-${ticketId}`, `[data-ticket-id="${ticketId}"]`];
        let target = selectors.map(s => document.querySelector(s)).find(e => e);

        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.classList.add('ring-2', 'ring-yellow-400', 'ring-opacity-75', 'transition-all', 'duration-1000');
            setTimeout(() => target.classList.remove('ring-2', 'ring-yellow-400', 'ring-opacity-75'), 3000);
        }
    });
</script>
@endif
@endsection
