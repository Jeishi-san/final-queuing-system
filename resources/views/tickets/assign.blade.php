{{-- resources/views/tickets/assign.blade.php --}}

{{-- ✅ Panel Container --}}
<div id="ticketPanel"
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-2xl shadow-lg">

    {{-- Header --}}
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">
      📝 Assign / Update Ticket
    </h2>

    {{-- Form --}}
    <form id="updateTicketForm" method="POST" action="{{ route('tickets.update', $ticket->id) }}">
      @csrf
      @method('PATCH')

      <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

      {{-- Ticket Number --}}
      <div class="mb-4">
        <label class="block text-sm font-medium">Ticket Number</label>
        <input type="text"
               value="{{ $ticket->ticket_number }}"
               disabled
               class="w-full border rounded p-2 bg-gray-100 dark:bg-gray-700">
      </div>

      {{-- Issue --}}
      <div class="mb-4">
        <label class="block text-sm font-medium">Issue</label>
        <textarea rows="3" disabled
                  class="w-full border rounded p-2 bg-gray-100 dark:bg-gray-700">{{ $ticket->issue_description }}</textarea>
      </div>

      {{-- Status --}}
      <div class="mb-4">
        <label class="block text-sm font-medium">Status</label>
        <select name="status"
                class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white">
          <option value="pending"     {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
          <option value="resolved"    {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
        </select>
      </div>

      {{-- IT Personnel --}}
      <div class="mb-4">
        <label class="block text-sm font-medium">Assign IT Personnel</label>
        <select name="it_personnel_id"
                class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:text-white">
          <option value="">-- Select IT Personnel --</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}"
                    {{ $ticket->it_personnel_id == $user->id ? 'selected' : '' }}>
              {{ $user->name }} ({{ $user->email }})
            </option>
          @endforeach
        </select>
      </div>

      {{-- Buttons --}}
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" id="panelCancel"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
          Cancel
        </button>
        <button type="submit"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
          Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ✅ Toast Notification --}}
<div id="toast"
     class="fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white hidden z-50">
</div>

{{-- ✅ JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const panel = document.getElementById('ticketPanel');
  const cancelBtn = document.getElementById('panelCancel');
  const form = document.getElementById('updateTicketForm');
  const toast = document.getElementById('toast');

  function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white z-50 ${
      type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
  }

  function closePanel() {
    panel.style.display = 'none';
  }

  function openPanel() {
    panel.style.display = 'flex';
  }

  // Close actions
  cancelBtn?.addEventListener('click', closePanel);

  // Close when clicking outside the panel
  panel?.addEventListener('click', (e) => {
    if (e.target === panel) {
      closePanel();
    }
  });

  // Close with Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && panel.style.display === 'flex') {
      closePanel();
    }
  });

  // Submit via AJAX
  form?.addEventListener('submit', async (e) => {
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
        closePanel();

        // Optionally refresh dashboard
        if (window.location.pathname.includes('dashboard')) {
          setTimeout(() => window.location.reload(), 1000);
        }
      } else {
        showToast(data.message || 'Update failed', 'error');
      }
    } catch (err) {
      console.error('Error:', err);
      showToast('⚠️ Error while updating ticket', 'error');
    }
  });

  // Initialize panel as hidden
  closePanel();
});
</script>