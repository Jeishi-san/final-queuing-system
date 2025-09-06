<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<div class="max-w-7xl mx-auto py-10 grid grid-cols-1 lg:grid-cols-4 gap-8">

    <!-- 🔹 Left Section: Ticket Status Panels -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Pending Tickets -->
        <div class="p-4 bg-blue-50 dark:bg-blue-800/40 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
                ⏳ Pending Tickets 
                <span class="px-2 py-1 text-xs font-medium bg-blue-200 dark:bg-blue-700 text-blue-900 dark:text-blue-200 rounded-full">
                    {{ $pendingTickets->count() }}
                </span>
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-blue-200 dark:bg-blue-700">
                            <th class="border px-3 py-2 text-left">ID</th>
                            <th class="border px-3 py-2 text-left">Ticket #</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingTickets as $ticket)
                            <tr class="hover:bg-blue-100 dark:hover:bg-blue-700/40">
                                <td class="border px-3 py-2">{{ $ticket->id }}</td>
                                <td class="border px-3 py-2">{{ $ticket->ticket_number }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-3 text-gray-500">No pending tickets</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- In-Progress Tickets -->
        <div class="p-4 bg-yellow-50 dark:bg-yellow-800/40 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
                ⚙️ In Progress 
                <span class="px-2 py-1 text-xs font-medium bg-yellow-200 dark:bg-yellow-700 text-yellow-900 dark:text-yellow-200 rounded-full">
                    {{ $inProgressTickets->count() }}
                </span>
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-yellow-200 dark:bg-yellow-700">
                            <th class="border px-3 py-2">ID</th>
                            <th class="border px-3 py-2">Ticket #</th>
                            <th class="border px-3 py-2">IT Personnel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inProgressTickets as $ticket)
                            <tr class="hover:bg-yellow-100 dark:hover:bg-yellow-700/40">
                                <td class="border px-3 py-2">{{ $ticket->id }}</td>
                                <td class="border px-3 py-2">{{ $ticket->ticket_number }}</td>
                                <td class="border px-3 py-2">{{ $ticket->it_personnel_name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-gray-500">No in-progress tickets</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resolved Tickets -->
        <div class="p-4 bg-green-50 dark:bg-green-800/40 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
                ✅ Resolved 
                <span class="px-2 py-1 text-xs font-medium bg-green-200 dark:bg-green-700 text-green-900 dark:text-green-200 rounded-full">
                    {{ $resolvedTickets->count() }}
                </span>
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-green-200 dark:bg-green-700">
                            <th class="border px-3 py-2">ID</th>
                            <th class="border px-3 py-2">Ticket #</th>
                            <th class="border px-3 py-2">IT Personnel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resolvedTickets as $ticket)
                            <tr class="hover:bg-green-100 dark:hover:bg-green-700/40">
                                <td class="border px-3 py-2">{{ $ticket->id }}</td>
                                <td class="border px-3 py-2">{{ $ticket->ticket_number }}</td>
                                <td class="border px-3 py-2">{{ $ticket->it_personnel_name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-gray-500">No resolved tickets</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- 🔹 Right Section: Ticket Submission Form -->
    <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">

        <!-- Auth Bar -->
        <div class="flex justify-end space-x-4 mb-6 text-sm">
            @auth
                <span class="text-gray-600 dark:text-gray-300">👤 {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600">Login</a>
                <a href="{{ route('register') }}" class="px-3 py-1 bg-gray-500 text-white rounded-md hover:bg-gray-600">Register</a>
            @endauth
        </div>

        <!-- Flash Messages -->
        @foreach (['success' => 'green', 'error' => 'red'] as $msg => $color)
            @if(session($msg))
                <div class="mb-4 p-3 bg-{{ $color }}-100 text-{{ $color }}-700 rounded">
                    {{ session($msg) }}
                </div>
            @endif
        @endforeach

        <h1 class="text-2xl font-bold mb-6">📝 Submit a Ticket</h1>

        @php
            $inputClasses = "w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                            focus:ring focus:ring-blue-300 text-gray-900 dark:text-gray-100
                            bg-white dark:bg-gray-700 p-2";
        @endphp

        <form method="POST" action="{{ route('tickets.store') }}" class="space-y-5">
            @csrf

            <!-- Ticket Number -->
            <div>
                <label for="ticket_number" class="block text-sm font-medium mb-1">Ticket Number</label>
                <input type="text" name="ticket_number" id="ticket_number"
                       value="{{ old('ticket_number') }}" required
                       class="{{ $inputClasses }}">
            </div>

            <!-- Agent Name -->
            <div>
                <label for="agent_name" class="block text-sm font-medium mb-1">Agent Name</label>
                <input type="text" name="agent_name" id="agent_name"
                       value="{{ old('agent_name') }}" required
                       class="{{ $inputClasses }}">
            </div>

            <!-- Agent Email -->
            <div>
                <label for="agent_email" class="block text-sm font-medium mb-1">Agent Email</label>
                <input type="email" name="agent_email" id="agent_email"
                       value="{{ old('agent_email') }}" required
                       class="{{ $inputClasses }}">
            </div>

            <!-- Team Leader -->
            <div>
                <label for="team_leader_name" class="block text-sm font-medium mb-1">Team Leader</label>
                <input type="text" name="team_leader_name" id="team_leader_name"
                       value="{{ old('team_leader_name') }}" required
                       class="{{ $inputClasses }}">
            </div>

            <!-- Component -->
            <div>
                <label for="component" class="block text-sm font-medium mb-1">Component</label>
                <input type="text" name="component" id="component"
                       value="{{ old('component') }}" required
                       class="{{ $inputClasses }}">
            </div>

            <!-- Issue Description -->
            <div>
                <label for="issue_description" class="block text-sm font-medium mb-1">Issue Description</label>
                <textarea name="issue_description" id="issue_description" rows="4" required
                          class="{{ $inputClasses }}">{{ old('issue_description') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
