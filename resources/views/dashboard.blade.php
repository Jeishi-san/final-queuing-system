@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 space-y-8">

    {{-- ✅ Panels Wrapper --}}
    <div id="dashboardPanels">
        {{-- ✅ Stats --}}
        @include('tickets.stats', ['stats' => $stats])

        {{-- ✅ Tickets Table --}}
        <div id="ticketTableContainer">
            @include('tickets.tables', ['tickets' => $tickets])
        </div>
    </div>

    {{-- ✅ Assign Modal --}}
    <div id="assignModal"
         class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-2xl shadow-lg relative">
            <button id="assignModalClose"
                    class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-lg">✖</button>
            <div id="assignFormContainer"><!-- Loaded dynamically --></div>
        </div>
    </div>
</div>

{{-- ✅ Toast --}}
<div id="toast"
     class="fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white hidden z-50"></div>

{{-- ✅ JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const assignModal     = document.getElementById('assignModal');
    const assignClose     = document.getElementById('assignModalClose');
    const assignContainer = document.getElementById('assignFormContainer');
    const toast           = document.getElementById('toast');

    /** ✅ Toast helper */
    function showToast(message, type='success') {
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white z-50 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    /** ✅ Refresh dashboard panels (stats + table) */
    async function refreshDashboard() {
        try {
            const resp = await fetch(`{{ route('dashboard.ticketsTable') }}`);
            if (resp.ok) {
                // Replace ONLY the table part
                document.getElementById('ticketTableContainer').innerHTML = await resp.text();
            } else {
                console.error('Refresh failed:', resp.statusText);
            }
        } catch (err) {
            console.error('❌ Refresh failed:', err);
        }
    }

    /** ✅ Triggered after ticket creation */
    window.addEventListener('ticket:created', () => {
        refreshDashboard();
        showToast('✅ New ticket added!', 'success');
    });

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

    /** ✅ Close modal */
    function closeAssignModal() {
        assignModal.style.display = 'none';
        assignContainer.innerHTML = '';
    }

    /** ✅ Handle Assign form submit */
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
                    showToast(data.message || 'Update failed', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('⚠️ Error while updating ticket', 'error');
            }
        });
    }

    /** ✅ Event listeners for modal triggers and close */
    document.addEventListener('click', (e) => {
        if (e.target.closest('.open-assign')) {
            const id = e.target.closest('.open-assign').dataset.id;
            openAssignModal(id);
        }
    });

    assignClose.addEventListener('click', closeAssignModal);
    assignModal.addEventListener('click', (e) => {
        if (e.target === assignModal) closeAssignModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && assignModal.style.display === 'flex') closeAssignModal();
    });
});
</script>
@endsection
