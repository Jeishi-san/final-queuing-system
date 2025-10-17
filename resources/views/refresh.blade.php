<div id="dashboard-stats">
    @include('tickets.stats', ['stats' => $stats])
</div>

<div id="ticket-table">
    @include('tickets.tables', ['tickets' => $tickets])
</div>
