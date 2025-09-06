<nav class="bg-gray-800 text-white p-4 flex justify-between items-center">
    <!-- Left side -->
    <div class="flex space-x-4">
        @auth
            <a href="{{ route('dashboard') }}" class="hover:underline">Ticket Dashboard</a>
        @endauth
    </div>

    <!-- Right side -->
    <div class="flex space-x-4 items-center">
        @auth
            <span class="text-sm">Hi, {{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="hover:underline">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:underline">Login</a>
            <a href="{{ route('register') }}" class="hover:underline">Register</a>
        @endauth
    </div>
</nav>
