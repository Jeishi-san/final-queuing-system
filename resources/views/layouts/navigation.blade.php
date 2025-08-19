<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Queuing System</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <a href="{{ route('dashboard') }}" class="text-lg font-bold text-gray-800 dark:text-gray-200">
                Queuing System Dashboard
            </a>

            <div>
                @auth
                    <span class="mr-4 text-gray-700 dark:text-gray-300">
                        Welcome, {{ Auth::user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">Pending Tickets</h1>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Ticket Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Ticket #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Agent Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Component</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Issue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td class="px-6 py-4">{{ $ticket->ticket_number }}</td>
                            <td class="px-6 py-4">{{ $ticket->agent_name }}</td>
                            <td class="px-6 py-4">{{ $ticket->agent_email }}</td>
                            <td class="px-6 py-4">{{ $ticket->component }}</td>
                            <td class="px-6 py-4">{{ $ticket->issue_description }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 text-xs rounded">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No pending tickets.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
