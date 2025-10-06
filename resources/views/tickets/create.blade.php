<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<!-- ✅ Header -->
<header class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">🎫 Ticketing System</h1>

    <div class="space-x-3">
        @guest
            <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Login</a>
            <a href="{{ route('register') }}" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Register</a>
        @else
            <span class="font-medium">Hi, {{ Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Logout
                </button>
            </form>
        @endguest
    </div>
</header>

<main class="max-w-5xl mx-auto py-10 grid grid-cols-1 lg:grid-cols-2 gap-8">

    <!-- 🔹 Ticket Submission Form -->
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6">📝 Submit a Ticket</h2>

        <div id="formFeedback" class="hidden mb-4 p-3 rounded text-sm"></div>

        <form id="createTicketForm" action="{{ route('tickets.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Ticket Number -->
            <div>
                <label class="block text-sm font-medium mb-1">Ticket Number (Optional)</label>
                <input type="text" name="ticket_number"
                       placeholder="Leave blank to auto-generate"
                       class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Agent Info -->
            <div>
                <label class="block text-sm font-medium mb-1">Agent Name *</label>
                <input type="text" name="agent_name" required
                       class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Agent Email *</label>
                <input type="email" name="agent_email" required
                       class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Team Leader Info -->
            <div>
                <label class="block text-sm font-medium mb-1">Team Leader Name</label>
                <input type="text" name="team_leader_name"
                       class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Team Leader Email</label>
                <input type="email" name="team_leader_email"
                       class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Component -->
            <div>
                <label class="block text-sm font-medium mb-1">Component</label>
                <input type="text" name="component_name" placeholder="e.g., Keyboard, Monitor"
                       class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Issue Description -->
            <div>
                <label class="block text-sm font-medium mb-1">Issue Description *</label>
                <textarea name="issue_description" rows="4" required
                          placeholder="Describe the issue in detail..."
                          class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-3">
                <button type="reset" class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Reset
                </button>
                <button type="submit" id="submitBtn"
                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center">
                    <span id="submitText">Submit Ticket</span>
                    <svg id="submitSpinner" class="hidden w-4 h-4 ml-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4zm2 5.3A8 8 0 014 12H0c0 3 1.1 5.8 3 7.9l3-2.6z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- 🔹 Ticket Panels -->
    <div id="ticketPanels" class="space-y-6">
        @auth
            @include('tickets.panels', ['stats'=>$stats,'tickets'=>$tickets])
        @else
            @include('tickets.stats', ['stats'=>$stats])
        @endauth
    </div>
</main>

<!-- ✅ Toast -->
<div id="toast" class="fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white hidden z-50"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form           = document.getElementById('createTicketForm');
    const feedback       = document.getElementById('formFeedback');
    const submitBtn      = document.getElementById('submitBtn');
    const submitText     = document.getElementById('submitText');
    const submitSpinner  = document.getElementById('submitSpinner');
    const toast          = document.getElementById('toast');

    /** ✅ Toast helper */
    function showToast(message, type='success') {
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white z-50 ${
            type==='success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    function setLoading(loading){
        submitBtn.disabled = loading;
        submitText.textContent = loading ? 'Submitting...' : 'Submit Ticket';
        submitSpinner.classList.toggle('hidden', !loading);
    }

    /** ✅ Refresh dashboard ticket panels */
    async function refreshPanels(){
        try {
            const res = await fetch("{{ route('tickets.panels') }}");
            if(res.ok){
                document.getElementById('ticketPanels').innerHTML = await res.text();
            } else {
                console.error('Panel refresh failed:', res.statusText);
            }
        } catch(err){
            console.error('Panel refresh error:', err);
        }
    }

    /** ✅ Submit handler */
    form.addEventListener('submit', async e=>{
        e.preventDefault();
        setLoading(true);
        feedback.classList.add('hidden');

        const formData = new FormData(form);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await res.json();
            if(!res.ok) throw data;

            showToast('✅ Ticket submitted successfully','success');
            feedback.className = 'mb-4 p-3 rounded bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300';
            feedback.innerHTML = `<strong>Success!</strong> Ticket #${data.ticket?.ticket_number || ''} has been created.`;
            feedback.classList.remove('hidden');

            form.reset();
            await refreshPanels(); // 🔑 refresh panels automatically

        } catch(err){
            console.error('Submission error:', err);
            feedback.className = 'mb-4 p-3 rounded bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300';

            if(err?.errors){
                const messages = Object.values(err.errors).flat();
                feedback.innerHTML = `
                    <strong>Please fix the following errors:</strong>
                    <ul class="mt-1 list-disc list-inside">
                        ${messages.map(m=>`<li>${m}</li>`).join('')}
                    </ul>`;
            } else {
                feedback.innerHTML = `<strong>Error:</strong> ${err?.message || 'Failed to create ticket. Please try again.'}`;
            }
            feedback.classList.remove('hidden');
            showToast('❌ Failed to create ticket','error');

        } finally {
            setLoading(false);
        }
    });

    // ✅ Simple inline validation
    form.querySelectorAll('[required]').forEach(field=>{
        field.addEventListener('blur', ()=>{
            field.classList.toggle('border-red-500', !field.value.trim());
        });
    });
});
</script>

</body>
</html>
