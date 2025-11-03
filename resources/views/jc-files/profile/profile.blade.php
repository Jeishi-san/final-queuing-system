@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 space-y-10">

    {{-- ✅ Profile Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-8 transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                👤 Profile Overview
            </h1>
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-md shadow-sm transition">
                ✏️ Edit Profile
            </a>
        </div>

        <div class="flex flex-col sm:flex-row items-start gap-6">
            {{-- 🖼 Profile Picture --}}
            <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('images/default-avatar.png') }}"
                 alt="Profile Picture"
                 class="w-28 h-28 rounded-full object-cover border-4 border-gray-300 dark:border-gray-700 shadow-sm">

            {{-- 🧾 Profile Details --}}
            <div class="space-y-2">
                <p class="text-gray-700 dark:text-gray-300">
                    <strong>Name:</strong> {{ $user->name ?? 'N/A' }}
                </p>
                <p class="text-gray-700 dark:text-gray-300">
                    <strong>Email:</strong> {{ $user->email ?? 'N/A' }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Member since: {{ $user->created_at->format('F j, Y') }}
                </p>
            </div>
        </div>
    </div>

    {{-- ✅ Ticket Statistics --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-8 transition-all duration-300">
        <h2 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-gray-100 flex items-center gap-2">
            📊 Ticket Statistics
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-center">
            @php
                $stats = [
                    ['label' => 'Total Tickets', 'count' => $ticketStats['total'] ?? 0, 'color' => 'blue'],
                    ['label' => 'Pending', 'count' => $ticketStats['pending'] ?? 0, 'color' => 'yellow'],
                    ['label' => 'In Progress', 'count' => $ticketStats['in_progress'] ?? 0, 'color' => 'purple'],
                    ['label' => 'Resolved / Closed', 'count' => $ticketStats['resolved'] ?? 0, 'color' => 'green'],
                ];
            @endphp

            @foreach($stats as $stat)
                <div class="p-5 rounded-lg bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-900/40 shadow-sm hover:scale-[1.02] transition-transform duration-200">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-bold text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-300">
                        {{ $stat['count'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ✅ Activity Logs --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-8 transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                📝 Recent Activity
            </h2>

            {{-- 🔍 Filters --}}
            <form method="GET" action="{{ route('profile') }}" class="w-full sm:w-auto flex flex-wrap gap-3 items-end">
                {{-- Search --}}
                <div class="flex flex-col">
                    <label for="search" class="text-sm text-gray-600 dark:text-gray-300 mb-1">Search</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Search action or ticket..."
                        class="p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 text-sm"
                    >
                </div>

                {{-- Action Filter --}}
                <div class="flex flex-col">
                    <label for="action_type" class="text-sm text-gray-600 dark:text-gray-300 mb-1">Action</label>
                    <select name="action_type" id="action_type"
                        class="p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="">All</option>
                        @foreach(['Created','Updated','Assigned','Closed'] as $option)
                            <option value="{{ $option }}" {{ request('action_type') === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="flex flex-col">
                    <label for="status" class="text-sm text-gray-600 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" id="status"
                        class="p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="">All</option>
                        @foreach(['Pending','In Progress','Resolved','Closed'] as $option)
                            <option value="{{ $option }}" {{ request('status') === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date From --}}
                <div class="flex flex-col">
                    <label for="from_date" class="text-sm text-gray-600 dark:text-gray-300 mb-1">From</label>
                    <input type="date" name="from_date" id="from_date"
                           value="{{ request('from_date') }}"
                           class="p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 text-sm">
                </div>

                {{-- Date To --}}
                <div class="flex flex-col">
                    <label for="to_date" class="text-sm text-gray-600 dark:text-gray-300 mb-1">To</label>
                    <input type="date" name="to_date" id="to_date"
                           value="{{ request('to_date') }}"
                           class="p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 text-sm">
                </div>

                {{-- Buttons --}}
                <div class="flex gap-2 mt-2 sm:mt-0">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium shadow transition">
                        Filter
                    </button>
                    <a href="{{ route('profile') }}"
                       class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-md text-sm font-medium hover:bg-gray-400 dark:hover:bg-gray-600 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- 🧾 Logs List --}}
        @if($logs->count())
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($logs as $log)
                    @php
                        $badgeColor = match(true) {
                            str_contains($log->action, 'Assigned') => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            str_contains($log->action, 'Updated') => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            str_contains($log->action, 'Closed') => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            str_contains($log->action, 'Created') => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                        };
                    @endphp

                    <li class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2" id="log-{{ $log->id }}">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                    {{ Str::headline(Str::before($log->action, ' ')) }}
                                </span>
                                <p class="text-gray-800 dark:text-gray-200 font-medium">
                                    {{ $log->action ?? 'No action description' }}
                                </p>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
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

                        <span class="text-gray-500 dark:text-gray-400 text-sm whitespace-nowrap">
                            {{ optional($log->performed_at ?? $log->created_at)->diffForHumans() ?? 'No timestamp' }}
                        </span>
                    </li>
                @endforeach
            </ul>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="mt-6">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">
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
    const target = document.querySelector(`#ticket-row-${ticketId}, #log-${ticketId}, [data-ticket-id="${ticketId}"]`);
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        target.classList.add('ring-2', 'ring-yellow-400', 'ring-opacity-75', 'transition-all', 'duration-1000');
        setTimeout(() => target.classList.remove('ring-2', 'ring-yellow-400', 'ring-opacity-75'), 3000);
    }
});
</script>
@endif
@endsection
