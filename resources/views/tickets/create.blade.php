<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<header class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">🎫 Ticketing System</h1>
    
    <div class="space-x-3">
        @guest
            <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-500 text-white rounded">Login</a>
            <a href="{{ route('register') }}" class="px-4 py-2 bg-green-500 text-white rounded">Register</a>
        @else
            <span>Hi, {{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded">Logout</button>
            </form>
        @endguest
    </div>
</header>

<div class="max-w-5xl mx-auto py-10 grid grid-cols-1 lg:grid-cols-2 gap-8">

    <!-- 🔹 Ticket Submission Form -->
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6">📝 Submit a Ticket</h2>

        <div id="formFeedback" class="hidden mb-4 p-3 rounded text-sm"></div>

        <form id="createTicketForm" action="{{ route('tickets.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Ticket Number -->
            <div>
                <label for="ticket_number" class="block text-sm font-medium mb-1">Ticket Number</label>
                <input type="text" name="ticket_number" id="ticket_number" required
                       placeholder="Enter a unique ticket number"
                       class="w-full border rounded p-2 bg-white dark:bg-gray-700">
            </div>

            <!-- Agent Info -->
            <div>
                <label for="agent_name" class="block text-sm font-medium mb-1">Agent Name</label>
                <input type="text" name="agent_name" id="agent_name" class="w-full border rounded p-2 bg-white dark:bg-gray-700">
            </div>

            <div>
                <label for="agent_email" class="block text-sm font-medium mb-1">Agent Email</label>
                <input type="email" name="agent_email" id="agent_email" class="w-full border rounded p-2 bg-white dark:bg-gray-700">
            </div>

            <!-- Team Leader Info -->
            <div>
                <label for="team_leader_name" class="block text-sm font-medium mb-1">Team Leader Name</label>
                <input type="text" name="team_leader_name" id="team_leader_name" class="w-full border rounded p-2 bg-white dark:bg-gray-700">
            </div>

            <div>
                <label for="team_leader_email" class="block text-sm font-medium mb-1">Team Leader Email</label>
                <input type="email" name="team_leader_email" id="team_leader_email" class="w-full border rounded p-2 bg-white dark:bg-gray-700">
            </div>

            <!-- Component -->
            <div>
                <label for="component_name" class="block text-sm font-medium mb-1">Component</label>
                <input type="text" name="component_name" id="component_name" class="w-full border rounded p-2 bg-white dark:bg-gray-700">
            </div>

            <!-- Issue Description -->
            <div>
                <label for="issue_description" class="block text-sm font-medium mb-1">Issue Description</label>
                <textarea name="issue_description" id="issue_description" rows="4" required
                          class="w-full border rounded p-2 bg-white dark:bg-gray-700"></textarea>
            </div>        

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>

    <!-- 🔹 Ticket Status Panels (stats + list for IT, stats-only for agents) -->
    <div id="ticketPanels" class="space-y-6">
        @auth
            @include('tickets.panels', ['stats' => $stats, 'tickets' => $tickets])
        @else
            @include('tickets.stats', ['stats' => $stats])
        @endauth
    </div>
</div>

<script>
document.getElementById('createTicketForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const feedback = document.getElementById('formFeedback');

    try {
        const response = await fetch(form.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": form.querySelector('[name="_token"]').value,
                "Accept": "application/json",
            },
            body: formData
        });

        const data = await response.json();
        if (!response.ok) throw data;

        feedback.className = "mb-4 p-3 rounded bg-green-100 text-green-700";
        feedback.innerText = data.message || "✅ Ticket created successfully!";
        feedback.classList.remove("hidden");

        form.reset();
        refreshPanels();
    } catch (err) {
        console.error(err);
        feedback.className = "mb-4 p-3 rounded bg-red-100 text-red-700";

        if (err?.errors) {
            feedback.innerText = Object.values(err.errors).flat().join(" ");
        } else {
            feedback.innerText = err?.message || "❌ Failed to create ticket.";
        }

        feedback.classList.remove("hidden");
    }
});

async function refreshPanels() {
    const res = await fetch("{{ route('tickets.panels') }}");
    document.getElementById('ticketPanels').innerHTML = await res.text();
}
</script>
</body>
</html>
