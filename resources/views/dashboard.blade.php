@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 space-y-10">

    {{-- 📈 Stats Overview --}}
    @if(isset($stats))
    <div id="statsPanel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5" data-stat-type="Total Tickets">
            <h2 class="text-gray-500 dark:text-gray-400 text-sm">Total Tickets</h2>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5" data-stat-type="Pending">
            <h2 class="text-gray-500 dark:text-gray-400 text-sm">Pending</h2>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5" data-stat-type="Resolved">
            <h2 class="text-gray-500 dark:text-gray-400 text-sm">Resolved</h2>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['resolved'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5" data-stat-type="Overdue">
            <h2 class="text-gray-500 dark:text-gray-400 text-sm">Overdue</h2>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['overdue'] ?? 0 }}</p>
        </div>
    </div>
    @endif

    {{-- 🎯 Dashboard Header --}}
    <div class="flex justify-between items-center">
        {{-- 🔍 Search Form --}}
        <form id="ticketFilters" method="GET" class="flex items-center space-x-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search ticket, agent, leader..."
                   class="px-4 py-2 border rounded-lg w-64 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-700 dark:text-white">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                Search
            </button>
        </form>
    </div>

    {{-- 🧾 Ticket Table Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                📋 Ticket List
            </h2>
        </div>

        {{-- 🏷️ Additional Filters --}}
        <form id="extraFilters" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 items-end">
            {{-- 🏷 Status --}}
            <div>
                <select name="status" id="statusFilter"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending"     {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>🔄 In Progress</option>
                    <option value="resolved"    {{ request('status') == 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
                </select>
            </div>

            {{-- 👨‍💻 IT Personnel --}}
            <div>
                <select name="it_personnel_id" id="itPersonnelFilter"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                    <option value="">All IT Personnel</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('it_personnel_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ✅ Buttons --}}
            <div class="flex gap-2 md:col-span-2">
                <button type="submit" id="applyFiltersBtn"
                    class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    🔍 Filter
                </button>
                <a href="{{ route('dashboard') }}" id="clearFiltersBtn"
                   class="flex-1 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600 transition text-center">
                   ❌ Clear
                </a>
            </div>
        </form>

        {{-- 🧩 Ticket Table --}}
        <div id="ticketTableContainer">
            @include('tickets.tables', ['tickets' => $tickets])
        </div>
    </div>
</div>

{{-- 🧱 Assign Modal --}}
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full overflow-y-auto relative">
        <button id="assignModalClose" class="absolute top-4 right-4 text-gray-500 hover:text-red-600 dark:text-gray-400 text-xl">
            ✕
        </button>
        <div id="assignFormContainer" class="p-6 flex justify-center items-center min-h-[200px]">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
    </div>
</div>

{{-- 🔔 Toast --}}
<div id="toast" class="fixed bottom-5 right-5 px-6 py-3 rounded-xl shadow-lg text-white hidden z-50 max-w-sm"></div>

{{-- 📁 External JavaScript --}}
@push('scripts')
@vite('resources/js/dashboard.js')
@endpush
@endsection
