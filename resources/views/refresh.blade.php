{{-- resources/views/dashboard/refresh.blade.php --}}

{{-- ✅ Dashboard Stats Panel --}}
<div id="dashboard-stats">
    {{-- Render initial stats --}}
    @include('tickets.stats', ['stats' => $stats])
</div>

{{-- ✅ Tickets Table Panel --}}
<div id="ticket-table" data-endpoint="{{ route('dashboard.ticketsTable') }}">
    {{-- Render initial ticket table --}}
    @include('tickets.tables', ['tickets' => $tickets])
</div>

{{-- ✅ AJAX Refresh Script --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statsPanel = document.getElementById('dashboard-stats');
    const tablePanel = document.getElementById('ticket-table');
    const tableEndpoint = tablePanel.dataset.endpoint;

    // 🔄 Auto-refresh dashboard stats every 60 seconds
    setInterval(async () => {
        try {
            const res = await fetch('{{ route('dashboard.ticketsStats') }}');
            if (!res.ok) throw new Error('Failed to fetch stats');
            const html = await res.text();
            statsPanel.innerHTML = html;
        } catch (err) {
            console.error('Stats refresh failed:', err);
        }
    }, 60000);

    // 🧭 AJAX pagination handling (prevents full reload)
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('#ticket-table .pagination a');
        if (!link) return;

        e.preventDefault();
        try {
            const res = await fetch(link.href);
            if (!res.ok) throw new Error('Failed to load tickets');
            const html = await res.text();
            tablePanel.innerHTML = html;
        } catch (err) {
            console.error('Pagination fetch failed:', err);
        }
    });

    // 🕵️ Optional: manual refresh trigger
    document.body.addEventListener('click', async (e) => {
        if (e.target.matches('#refreshDashboard')) {
            e.preventDefault();
            try {
                const [statsRes, tableRes] = await Promise.all([
                    fetch('{{ route('dashboard.ticketsStats') }}'),
                    fetch(tableEndpoint)
                ]);
                if (!statsRes.ok || !tableRes.ok) throw new Error('Refresh failed');
                statsPanel.innerHTML = await statsRes.text();
                tablePanel.innerHTML = await tableRes.text();
            } catch (err) {
                console.error('Manual refresh failed:', err);
            }
        }
    });
});
</script>
@endpush
