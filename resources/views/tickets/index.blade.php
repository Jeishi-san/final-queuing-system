<x-app-layout>
    {{-- Top Navigation --}}
    <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Queuing System</h1>

    <div class="space-x-3">
        @guest
            <a href="{{ route('login') }}"
               class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-900">
                IT Personnel Login
            </a>
            <a href="{{ route('register') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-800">
                Register
            </a>
        @endguest

        @auth
            <span class="mr-3">Welcome, {{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-800">
                    Logout
                </button>
            </form>
        @endauth
    </div>
</div>



    {{-- Success Message --}}
    @if (session('success'))
        <div class="p-3 mb-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Ticket Form --}}
    <h2 class="text-2xl font-bold mb-4">Submit a New Ticket</h2>

    <form action="{{ route('tickets.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Agent --}}
        <div>
            <label for="agent_id" class="block font-semibold">Agent</label>
            <select name="agent_id" id="agent_id" class="w-full border rounded p-2" required>
                <option value="">-- Select Agent --</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Team Leader --}}
        <div>
            <label for="team_leader_id" class="block font-semibold">Team Leader</label>
            <select name="team_leader_id" id="team_leader_id" class="w-full border rounded p-2" required>
                <option value="">-- Select Team Leader --</option>
                @foreach ($teamLeaders as $leader)
                    <option value="{{ $leader->id }}">{{ $leader->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Component --}}
        <div>
            <label for="component" class="block font-semibold">Component</label>
            <select name="component" id="component" class="w-full border rounded p-2" required>
                <option value="">-- Select Component --</option>
                @foreach ($components as $component)
                    <option value="{{ $component->id }}">{{ $component->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Issue Description --}}
        <div>
            <label for="issue_description" class="block font-semibold">Issue Description</label>
            <textarea name="issue_description" id="issue_description" rows="4"
                class="w-full border rounded p-2" required></textarea>
        </div>

        {{-- Submit --}}
        <div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Submit Ticket
            </button>
        </div>
    </form>
</x-app-layout>
