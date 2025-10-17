document.addEventListener('DOMContentLoaded', () => {
    const assignForm = document.getElementById('assignForm');
    const closeBtn = document.getElementById('closeAssignModal');
    const assignModal = document.getElementById('assignModal');

    if (!assignForm) return;

    // ✅ Handle form submit via AJAX
    assignForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(assignForm);
        const ticketId = formData.get('ticket_id');
        const url = `/tickets/${ticketId}`; // ✅ Standard RESTful route
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        try {
            const response = await fetch(url, {
                method: 'POST', // Laravel accepts POST for update with _method override
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json().catch(() => null);

            if (response.ok) {
                console.log('✅ Ticket updated successfully:', result);
                closeAssignModal();

                // ✅ Refresh only panels and stats (not full page)
                await refreshPanelsAndStats();

                showToast('✅ Ticket updated successfully!');
            } else {
                console.error('❌ Update failed:', result);
                showToast('⚠️ Failed to update ticket. Please try again.', true);
            }
        } catch (error) {
            console.error('⚠️ AJAX error:', error);
            showToast('⚠️ An unexpected error occurred.', true);
        }
    });

    // ✅ Close modal handler
    if (closeBtn) {
        closeBtn.addEventListener('click', closeAssignModal);
    }

    function closeAssignModal() {
        if (assignModal) assignModal.classList.add('hidden');
    }

    // ✅ Dynamically refresh ticket panels and stats
    async function refreshPanelsAndStats() {
        try {
            const response = await fetch('/refresh');
            const result = await response.json();

            if (result.success) {
                const dashboard = document.getElementById('dashboard-panels');
                const statsContainer = document.getElementById('stats-container');

                if (dashboard) dashboard.innerHTML = result.html;
                if (statsContainer && result.stats) {
                    statsContainer.innerHTML = `
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-blue-100 p-4 rounded-xl text-center shadow">
                                <h3 class="text-lg font-bold">Pending</h3>
                                <p class="text-2xl">${result.stats.pending}</p>
                            </div>
                            <div class="bg-yellow-100 p-4 rounded-xl text-center shadow">
                                <h3 class="text-lg font-bold">In Progress</h3>
                                <p class="text-2xl">${result.stats.in_progress}</p>
                            </div>
                            <div class="bg-green-100 p-4 rounded-xl text-center shadow">
                                <h3 class="text-lg font-bold">Resolved</h3>
                                <p class="text-2xl">${result.stats.resolved}</p>
                            </div>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('⚠️ Failed to refresh panels:', error);
        }
    }

    // ✅ Toast Notification
    function showToast(message, isError = false) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = `fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-white text-sm transition-opacity duration-300 ${
            isError ? 'bg-red-500' : 'bg-green-600'
        }`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
});
