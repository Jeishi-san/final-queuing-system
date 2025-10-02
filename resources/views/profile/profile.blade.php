@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">

        {{-- ✅ Profile Header --}}
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
            Profile
        </h1>

        {{-- ✅ User Info --}}
        <div class="mb-8 space-y-2">
            <p class="text-gray-700 dark:text-gray-300">
                <strong>Name:</strong> {{ $user->name }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>Email:</strong> {{ $user->email }}
            </p>
        </div>

        {{-- ✅ Activity Logs --}}
        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Recent Activity
        </h2>

        @if($logs->count())
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($logs as $log)
                    <li class="py-4 flex justify-between items-start">

                        {{-- 🔹 Left Column: Action & Ticket Info --}}
                        <div>
                            {{-- Action --}}
                            <p class="text-gray-800 dark:text-gray-200">
                                {{ $log->action }}
                            </p>

                            {{-- Ticket Info --}}
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if($log->ticket)
                                    Ticket:
                                    {{-- ✅ Clickable linked ticket --}}
                                    <a href="{{ route('dashboard', ['highlight' => $log->ticket->id]) }}"
                                       class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        #{{ $log->ticket->id }} — {{ $log->ticket->ticket_number }}
                                    </a>

                                    {{-- ✅ Show IT Personnel if exists --}}
                                    @if($log->ticket->itPersonnel)
                                        • Handled by
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $log->ticket->itPersonnel->name }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400">No linked ticket</span>
                                @endif
                            </p>
                        </div>

                        {{-- 🔹 Right Column: Timestamp --}}
                        <span class="text-gray-500 dark:text-gray-400 text-sm">
                            {{ $log->performed_at?->diffForHumans() ?? $log->created_at->diffForHumans() }}
                        </span>
                    </li>
                @endforeach
            </ul>

            {{-- ✅ Pagination --}}
            <div class="mt-6">
                {{ $logs->links() }}
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">
                No activity logs yet.
            </p>
        @endif
    </div>
</div>

{{-- ✅ Optional Highlight Script --}}
@if(request()->has('highlight'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ticketId = '{{ request('highlight') }}';
        const targetRow = document.querySelector(`#ticket-row-${ticketId}`);
        if (targetRow) {
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetRow.classList.add('ring', 'ring-yellow-400');
            setTimeout(() => targetRow.classList.remove('ring', 'ring-yellow-400'), 3000);
        }
    });
</script>
@endif
@endsection
