<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md">

        <!-- 🔹 Login/Register Bar -->
        <div class="flex justify-end space-x-4 mb-6">
            @auth
                <span class="text-sm">Welcome, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-blue-500 hover:underline">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Login</a>
                <a href="{{ route('register') }}" class="text-blue-500 hover:underline">Register</a>
            @endauth
        </div>

        <!-- 🔹 Flash Messages -->
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- 🔹 Ticket Form -->
        <h1 class="text-xl font-bold mb-4">Submit a Ticket</h1>

        <form method="POST" action="{{ route('tickets.store') }}" class="space-y-4">
            @csrf

            <!-- Ticket Number -->
            <div>
                <label for="ticket_number" class="block text-sm font-medium">Ticket Number</label>
                <input type="text" name="ticket_number" id="ticket_number" 
                       class="w-full border-gray-300 rounded-md shadow-sm"
                       value="{{ old('ticket_number') }}" required>
                @error('ticket_number')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Agent Name -->
            <div>
                <label for="agent_name" class="block text-sm font-medium">Agent Name</label>
                <input type="text" name="agent_name" id="agent_name" 
                       class="w-full border-gray-300 rounded-md shadow-sm"
                       value="{{ old('agent_name') }}" required>
                @error('agent_name')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Agent Email -->
            <div>
                <label for="agent_email" class="block text-sm font-medium">Agent Email</label>
                <input type="email" name="agent_email" id="agent_email" 
                       class="w-full border-gray-300 rounded-md shadow-sm"
                       value="{{ old('agent_email') }}" required>
                @error('agent_email')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Team Leader -->
            <div>
                <label for="team_leader_name" class="block text-sm font-medium">Team Leader</label>
                <input type="text" name="team_leader_name" id="team_leader_name" 
                       class="w-full border-gray-300 rounded-md shadow-sm"
                       value="{{ old('team_leader_name') }}" required>
                @error('team_leader_name')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Component -->
            <div>
                <label for="component" class="block text-sm font-medium">Component</label>
                <input type="text" name="component" id="component" 
                       class="w-full border-gray-300 rounded-md shadow-sm"
                       value="{{ old('component') }}" required>
                @error('component')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Issue Description -->
            <div>
                <label for="issue_description" class="block text-sm font-medium">Issue Description</label>
                <textarea name="issue_description" id="issue_description" rows="4" 
                          class="w-full border-gray-300 rounded-md shadow-sm"
                          required>{{ old('issue_description') }}</textarea>
                @error('issue_description')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</body>
</html>
