@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10 space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            🔔 Your Notifications 
            <span class="text-blue-600">({{ $unreadCount ?? 0 }} unread)</span>
        </h1>
    </div>

    <div class="space-y-4">
        @if(isset($notifications) && $notifications->count() > 0)
            @foreach ($notifications as $notification)
                <div class="p-4 rounded-lg shadow-sm transition border
                    {{ $notification->read_at ? 'bg-gray-50 border-gray-200' : 'bg-blue-50 border-blue-300' }}">
                    
                    <div class="flex items-start justify-between">
                        <p class="text-gray-800 leading-relaxed">
                            {{ $notification->data['message'] ?? 'No message available.' }}
                        </p>

                        @if(!$notification->read_at)
                            <form 
                                action="{{ route('notifications.read', $notification->id) }}" 
                                method="POST" 
                                class="ml-3"
                            >
                                @csrf
                                <button 
                                    type="submit"
                                    class="text-xs text-blue-600 hover:underline font-medium"
                                >
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>

                    <small class="text-gray-500 block mt-1">
                        {{ $notification->created_at->diffForHumans() }}
                    </small>
                </div>
            @endforeach
        @else
            <p class="text-gray-600 text-center py-8">
                You have no notifications.
            </p>
        @endif
    </div>

    @if(isset($notifications) && $notifications->count() > 0)
        <form 
            action="{{ route('notifications.clear') }}" 
            method="POST" 
            class="text-center pt-6"
        >
            @csrf
            <button 
                type="submit"
                class="px-5 py-2.5 bg-red-500 text-white font-medium rounded-md hover:bg-red-600 transition"
            >
                Clear All Notifications
            </button>
        </form>
    @endif
</div>
@endsection
