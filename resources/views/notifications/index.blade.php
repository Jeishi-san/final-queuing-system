@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10 space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">
            🔔 Your Notifications 
            <span class="text-blue-600">({{ $notifications->where('read_at', null)->count() }} unread)</span>
        </h1>
        
        @if($notifications->count() > 0)
            <div class="flex space-x-4">
                <form action="{{ route('notifications.markAsRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white font-medium rounded-md hover:bg-blue-600 transition text-sm">
                        Mark All as Read
                    </button>
                </form>
                
                <form action="{{ route('notifications.clearAll') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white font-medium rounded-md hover:bg-red-600 transition text-sm">
                        Clear All
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        @if($notifications->count() > 0)
            @foreach ($notifications as $notification)
                <div class="p-4 rounded-lg shadow-sm transition border
                    {{ $notification->read_at ? 'bg-gray-50 border-gray-200' : 'bg-blue-50 border-blue-300' }}">
                    
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-gray-800 leading-relaxed">
                                {{-- ✅ FIX: Handle different notification data structures --}}
                                @if(isset($notification->data['message']))
                                    {{ $notification->data['message'] }}
                                @elseif(isset($notification->data['title']))
                                    {{ $notification->data['title'] }}
                                @else
                                    {{-- Try to generate a message from available data --}}
                                    @php
                                        $ticketNumber = $notification->data['ticket_number'] ?? 'Unknown Ticket';
                                        $action = 'updated';
                                        if(isset($notification->data['changes'])) {
                                            if(isset($notification->data['changes']['it_personnel_id'])) {
                                                $action = 'assigned';
                                            } elseif(isset($notification->data['changes']['status'])) {
                                                $action = 'status changed';
                                            }
                                        }
                                        echo "Ticket {$ticketNumber} was {$action}";
                                    @endphp
                                @endif
                            </p>

                            <small class="text-gray-500 block mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                                
                                {{-- Show ticket link if available --}}
                                @if(isset($notification->data['ticket_id']))
                                    • <a href="{{ route('tickets.show', $notification->data['ticket_id']) }}" class="text-blue-600 hover:underline">View Ticket</a>
                                @endif
                            </small>
                        </div>

                        @if(!$notification->read_at)
                            <form 
                                action="{{ route('notifications.markAsReadSingle', $notification->id) }}" 
                                method="POST" 
                                class="ml-3"
                            >
                                @csrf
                                <button 
                                    type="submit"
                                    class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 font-medium transition-colors"
                                >
                                    ✓ Read
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400 ml-3">Read</span>
                        @endif
                    </div>

                    {{-- Show changes if available --}}
                    @if(isset($notification->data['changes']) && is_array($notification->data['changes']))
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Changes:</h4>
                            <div class="space-y-1">
                                @foreach($notification->data['changes'] as $field => $change)
                                    @php
                                        $fieldNames = [
                                            'status' => 'Status',
                                            'it_personnel_id' => 'Assignment',
                                            'component_id' => 'Component'
                                        ];
                                        $fieldName = $fieldNames[$field] ?? $field;
                                    @endphp
                                    <div class="text-xs text-gray-600">
                                        <span class="font-medium">{{ $fieldName }}:</span> 
                                        <span class="line-through text-red-500">{{ $change['from'] ?? 'None' }}</span> 
                                        → 
                                        <span class="text-green-600 font-medium">{{ $change['to'] ?? 'None' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
            
            {{-- Pagination --}}
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-4xl mb-4">🔕</div>
                <p class="text-gray-600 text-lg">You have no notifications.</p>
                <p class="text-gray-400 text-sm mt-2">When you get notifications, they'll appear here.</p>
                <a href="{{ route('dashboard') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    ← Back to Dashboard
                </a>
            </div>
        @endif
    </div>
</div>
@endsection