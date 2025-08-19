@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">📌 Pending Tickets</h1>

    @if($tickets->count())
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Ticket #</th>
                    <th class="py-3 px-6 text-left">Agent Name</th>
                    <th class="py-3 px-6 text-left">Agent Email</th>
                    <th class="py-3 px-6 text-left">Issue</th>
                    <th class="py-3 px-6 text-left">Status</th>
                    <th class="py-3 px-6 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm font-light">
                @foreach($tickets as $ticket)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6">{{ $ticket->ticket_number }}</td>
                    <td class="py-3 px-6">{{ $ticket->agent_name }}</td>
                    <td class="py-3 px-6">{{ $ticket->agent_email }}</td>
                    <td class="py-3 px-6">{{ $ticket->issue_description }}</td>
                    <td class="py-3 px-6">
                        <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <form action="{{ route('tickets.updateStatus', $ticket->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">
                                Mark as Resolved
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">No pending tickets 🎉</p>
    @endif
</div>
@endsection
