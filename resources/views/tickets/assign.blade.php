{{-- resources/views/tickets/assign.blade.php --}}
<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-2xl w-full">

    {{-- ✅ Header --}}
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">
        📝 Assign / Update Ticket
    </h2>

    {{-- ✅ Ticket Details (Read-only) --}}
    <div class="mb-6 space-y-3 bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <strong class="text-gray-700 dark:text-gray-300">Ticket No:</strong>
                <span class="ml-2 font-mono text-blue-600 dark:text-blue-400">{{ $ticket->ticket_number }}</span>
            </div>
            <div>
                <strong class="text-gray-700 dark:text-gray-300">Status:</strong>
                <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium
                    @if($ticket->status === 'pending') bg-yellow-100 text-yellow-700
                    @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-700
                    @elseif($ticket->status === 'resolved') bg-green-100 text-green-700
                    @else bg-gray-200 text-gray-700 @endif">
                    {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                </span>
            </div>
            <div class="md:col-span-2">
                <strong class="text-gray-700 dark:text-gray-300">Issue:</strong>
                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ $ticket->issue_description }}</p>
            </div>
            <div>
                <strong class="text-gray-700 dark:text-gray-300">Component:</strong>
                <span class="ml-2">{{ $ticket->component->name ?? '—' }}</span>
            </div>
            <div>
                <strong class="text-gray-700 dark:text-gray-300">Agent:</strong>
                <span class="ml-2">{{ $ticket->agent->name ?? '—' }}</span>
            </div>
            <div>
                <strong class="text-gray-700 dark:text-gray-300">Team Leader:</strong>
                <span class="ml-2">{{ $ticket->teamLeader->name ?? '—' }}</span>
            </div>
            <div>
                <strong class="text-gray-700 dark:text-gray-300">IT Personnel:</strong>
                <span class="ml-2 {{ $ticket->itPersonnel ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $ticket->itPersonnel->name ?? 'Unassigned' }}
                </span>
            </div>
            <div>
                <strong class="text-gray-700 dark:text-gray-300">Created At:</strong>
                <span class="ml-2">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- ✅ Update Form --}}
    <form id="updateTicketForm" method="POST" action="{{ route('tickets.update', $ticket->id) }}">
        @csrf
        @method('PATCH')

        {{-- Status --}}
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Status
            </label>
            <select id="status" name="status"
                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <option value="pending"     {{ $ticket->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>🔄 In Progress</option>
                <option value="resolved"    {{ $ticket->status === 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
            </select>
        </div>

        {{-- IT Personnel --}}
        <div class="mb-6">
            <label for="it_personnel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Assign IT Personnel
            </label>
            <select id="it_personnel_id" name="it_personnel_id"
                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <option value="">-- Select IT Personnel --</option>
                @foreach($itPersonnels as $user)
                    <option value="{{ $user->id }}" {{ $ticket->it_personnel_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" id="panelCancel"
                class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">
                Cancel
            </button>
            <button type="submit" id="submitBtn"
                class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-medium flex items-center">
                <span id="submitText">Save Changes</span>
                <svg id="submitSpinner" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>

{{-- ✅ Script --}}
@push('scripts')
<script src="{{ asset('assign-modal.js') }}"></script>
@endpush
