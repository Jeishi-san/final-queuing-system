{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="fixed top-0 left-0 right-0 bg-gray-800 text-white p-4 flex justify-between items-center shadow-md z-50">
    <!-- Add CSRF token for JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Left side -->
    <div class="flex space-x-6">
        @auth
            <a href="{{ route('dashboard') }}" 
                class="hover:text-blue-400 font-semibold transition">
                🎟️ Ticket Dashboard
            </a>
        @endauth
    </div>

    <!-- Right side -->
    <div class="flex items-center space-x-6 relative">
        @auth
            <!-- 🔔 Notification Bell with Real-time Updates -->
            <div class="relative" id="notificationWrapper">
                <button id="notificationButton" class="relative text-white hover:text-blue-300 focus:outline-none transition-transform hover:scale-110">
                    <span class="text-2xl">🔔</span>
                    <span id="unreadCount" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center hidden transition-all duration-300">
                        0
                    </span>
                </button>

                {{-- Dropdown --}}
                <div id="notificationDropdown" 
                    class="hidden absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 transform transition-all duration-300 ease-in-out">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <span>🔔</span> Notifications
                        </h3>
                        <div class="flex space-x-3">
                            <button id="refreshNotifications" class="text-gray-500 hover:text-blue-600 transition-colors" title="Refresh notifications">
                                🔄
                            </button>
                            <form method="POST" action="{{ route('notifications.markAsRead') }}" id="markAllReadForm" class="hidden">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 transition-colors font-medium" id="markAllReadBtn">
                                    Mark all read
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="notificationList" class="max-h-80 overflow-y-auto">
                        <!-- Notifications will be loaded dynamically -->
                        <div class="p-6 text-center text-gray-500 text-sm">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto mb-2"></div>
                            Loading notifications...
                        </div>
                    </div>
                    
                    {{-- View All link --}}
                    <div class="p-3 border-t border-gray-200 text-center bg-gray-50 rounded-b-lg">
                        <a href="{{ route('notifications.index') }}" 
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
                            View All Notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- 👤 User Profile -->
            <a href="{{ route('profile') }}" class="hover:text-blue-400 text-sm font-medium transition-colors">
                {{ Auth::user()->name }}
            </a>

            <!-- 🚪 Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="hover:text-blue-400 text-sm font-medium transition-colors">
                    Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:text-blue-400 transition-colors">Login</a>
            <a href="{{ route('register') }}" class="hover:text-blue-400 transition-colors">Register</a>
        @endauth
    </div>
</nav>

{{-- Include the external JavaScript file using Vite --}}
@vite(['resources/js/notification-manager.js'])

{{-- Initialize Notification Manager --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if notification elements exist on the page
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationButton && notificationDropdown) {
        console.log('🎯 Notification system elements found, initializing...');
        
        // The NotificationManager will auto-initialize via the DOMContentLoaded event
        // in the notification-manager.js file
    } else {
        console.log('ℹ️ Notification system not available on this page');
    }
});

// Global function to manually trigger notification refresh
window.refreshNotifications = function() {
    if (window.notificationManager) {
        window.notificationManager.loadNotifications();
    }
};

// Global function to show notification dropdown
window.showNotifications = function() {
    if (window.notificationManager) {
        window.notificationManager.toggleDropdown();
    }
};
</script>