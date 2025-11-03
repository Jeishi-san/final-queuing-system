<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>Create Ticket</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<!-- ✅ Header -->
<header class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">🎫 Ticketing System</h1>
    <nav class="space-x-3">
        @guest
            <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Login</a>
            <a href="{{ route('register') }}" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">Register</a>
        @else
            <span class="font-medium">Hi, {{ Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
                    Logout
                </button>
            </form>
        @endguest
    </nav>
</header>

<!-- ✅ Main -->
<main class="max-w-7xl mx-auto py-10 grid grid-cols-1 lg:grid-cols-2 gap-8 px-4">
    <!-- 🔹 Ticket Panel -->
<section id="ticketPanel">
    @include('tickets.panels', [
        'pendingTickets' => $pendingTickets ?? collect(),
        'inProgressTickets' => $inProgressTickets ?? collect(),
        'resolvedTickets' => $resolvedTickets ?? collect(),
    ])
</section>


    <!-- 🔹 Ticket Form -->
    <section class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">📝 Submit a Ticket</h2>

        <div id="formFeedback" class="hidden mb-4 p-3 rounded text-sm"></div>

        <form id="createTicketForm" action="{{ route('tickets.store') }}" method="POST" class="space-y-5">
            @csrf
            @php
                $inputClass = 'w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition';
                $labelClass = 'block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300';
            @endphp

            <div>
                <label class="{{ $labelClass }}">Ticket Number (Optional)</label>
                <input type="text" name="ticket_number" placeholder="Leave blank to auto-generate" class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Agent Name *</label>
                <input type="text" name="agent_name" required class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Agent Email *</label>
                <input type="email" name="agent_email" required class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Team Leader Name</label>
                <input type="text" name="team_leader_name" class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Team Leader Email</label>
                <input type="email" name="team_leader_email" class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Component</label>
                <input type="text" name="component_name" placeholder="e.g., Keyboard, Monitor, Printer" class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Issue Description *</label>
                <textarea name="issue_description" rows="4" required placeholder="Describe the issue..." class="{{ $inputClass }}"></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="reset" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-medium">Clear</button>
                <button type="submit" id="submitBtn" class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium flex items-center">
                    <span id="submitText">Submit Ticket</span>
                    <svg id="submitSpinner" class="hidden w-4 h-4 ml-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
                    </svg>
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button id="manualRefreshBtn" type="button"
                class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium flex items-center justify-center">
                <svg id="refreshSpinner" class="hidden w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
                </svg>
                <span id="refreshText">🔄 Refresh Ticket Panel</span>
            </button>
        </div>
    </section>
</main>

<div id="toast" class="fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white hidden z-50"></div>

<!-- ✅ JS Globals -->
<script>
    window.APP = {
        csrfToken: '{{ csrf_token() }}',
        refreshUrl: '{{ route('tickets.panels') }}',
        storeUrl: '{{ route('tickets.store') }}',
        checkTicketUrl: '{{ route('tickets.check') }}', // make sure this route exists
    };
</script>
@vite(['resources/js/create.js'])
</body>
</html>
