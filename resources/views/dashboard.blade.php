@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 space-y-10">

    {{-- ✅ Stats Section --}}
    <div id="statsPanel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($stats as $stat)
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-md border-l-4 border-{{ $stat['color'] }}-500 p-6 hover:shadow-xl transition duration-300">

                {{-- Label + Icon --}}
                <div class="flex items-center space-x-3">
                    <span class="text-3xl text-{{ $stat['color'] }}-600">{!! $stat['icon'] !!}</span>
                    <h3 class="text-lg font-semibold text-{{ $stat['color'] }}-700 dark:text-{{ $stat['color'] }}-300">
                        {{ $stat['label'] }}
                    </h3>
                </div>

                {{-- Count --}}
                <p class="mt-4 text-4xl font-bold text-{{ $stat['color'] }}-700 dark:text-{{ $stat['color'] }}-400">
                    {{ $stat['count'] }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- ✅ Tickets Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6 flex items-center text-blue-600 dark:text-blue-400">
            📋 Tickets List
        </h2>

        {{-- ✅ Filters --}}
        <form id="ticketFilters" method="GET" class="flex flex-wrap gap-4 mb-6">
            {{-- Search --}}
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search tickets..."
                   class="flex-1 border rounded px-3 py-2 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" />

            {{-- Status Filter --}}
            <select id="statusFilter" name="status"
                    class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending"     {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status')==='in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved"    {{ request('status')==='resolved' ? 'selected' : '' }}>Resolved</option>
            </select>

            {{-- IT Personnel Filter --}}
            <select id="itPersonnelFilter" name="it_personnel_id"
                    class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="">All IT Personnel</option>
                @foreach ($itPersonnels as $personnel)
                    <option value="{{ $personnel->id }}"
                        {{ request('it_personnel_id') == $personnel->id ? 'selected' : '' }}>
                        {{ $personnel->name }}
                    </option>
                @endforeach
            </select>

            {{-- Filter Button --}}
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Filter
            </button>
        </form>

        {{-- ✅ Tickets Table Container --}}
        <div id="ticketTableContainer">
            @include('tickets.tables', ['tickets' => $tickets, 'itPersonnels' => $itPersonnels])
        </div>
    </div>
</div>

{{-- ✅ Assign Modal --}}
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-lg w-full relative">
        <button id="assignModalClose" class="absolute top-2 right-2 text-gray-600 hover:text-gray-800">✖</button>
        <div id="assignFormContainer">Loading...</div>
    </div>
</div>

{{-- ✅ Toast --}}
<div id="toast" class="fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white hidden z-50"></div>

{{-- ✅ JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const assignModal     = document.getElementById('assignModal');
    const assignClose     = document.getElementById('assignModalClose');
    const assignContainer = document.getElementById('assignFormContainer');
    const toast           = document.getElementById('toast');

    /** ✅ Toast helper */
    function showToast(message, type = 'success') {
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white z-50 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    /** ✅ Refresh dashboard table */
    async function refreshDashboard() {
        const status      = document.getElementById('statusFilter')?.value || '';
        const itPersonnel = document.getElementById('itPersonnelFilter')?.value || '';

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (itPersonnel) params.append('it_personnel_id', itPersonnel);

        try {
            const resp = await fetch(`{{ route('dashboard.ticketsTable') }}?${params.toString()}`);
            if (resp.ok) {
                document.getElementById('ticketTableContainer').innerHTML = await resp.text();
                bindAssignButtons(); // Re-bind buttons after table refresh
            } else {
                console.error('Refresh failed:', resp.statusText);
            }
        } catch (err) {
            console.error('❌ Refresh failed:', err);
        }
    }

    /** ✅ Open Assign Modal */
    async function openAssignModal(ticketId) {
        try {
            const resp = await fetch(`/tickets/${ticketId}/assign`);
            if (!resp.ok) throw new Error('Failed to load assign form');

            assignContainer.innerHTML = await resp.text();
            assignModal.style.display = 'flex';
            initAssignForm();
        } catch (err) {
            console.error(err);
            showToast('⚠️ Unable to load assign form', 'error');
        }
    }

    /** ✅ Close Modal */
    function closeAssignModal() {
        assignModal.style.display = 'none';
        assignContainer.innerHTML = '';
    }

    /** ✅ Handle Assign Form Submit */
    function initAssignForm() {
        const form = document.getElementById('updateTicketForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            if (!formData.has('_method')) formData.append('_method', 'PATCH');

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                const data = await resp.json();

                if (resp.ok && data.success) {
                    showToast('✅ Ticket updated successfully', 'success');
                    closeAssignModal();
                    refreshDashboard();
                } else {
                    showToast(data.message || '⚠️ Update failed', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('⚠️ Error while updating ticket', 'error');
            }
        });
    }

    /** ✅ Bind Assign buttons */
    function bindAssignButtons() {
        document.querySelectorAll('.open-assign').forEach(button => {
            button.addEventListener('click', () => openAssignModal(button.dataset.id));
        });
    }

    /** ✅ Event Listeners */
    assignClose.addEventListener('click', closeAssignModal);
    assignModal.addEventListener('click', e => { if (e.target === assignModal) closeAssignModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && assignModal.style.display === 'flex') closeAssignModal(); });

    // Initial bind
    bindAssignButtons();

    // Auto-refresh when filters change
    document.getElementById('statusFilter').addEventListener('change', refreshDashboard);
    document.getElementById('itPersonnelFilter').addEventListener('change', refreshDashboard);

    // Custom event for new tickets
    window.addEventListener('ticket:created', () => {
        refreshDashboard();
        showToast('✅ New ticket added!', 'success');
    });
});
</script>
@endsection
