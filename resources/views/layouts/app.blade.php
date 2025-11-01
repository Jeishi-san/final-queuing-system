<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ticketing System') }}</title>

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Extra styles (per-view injection) --}}
    @stack('styles')
</head> 
<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- Navbar (pulled in from navigation.blade.php) --}}
    <header>
        @include('layouts.navigation')
    </header>

    {{-- Main Content with responsive top padding --}}
    <main class="flex-grow container mx-auto px-4 sm:px-6 py-6 mt-16"> {{-- ✅ mt-16 for navbar spacing --}}
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer (optional) --}}
    <footer class="bg-gray-800 text-white text-center p-4">
        <p class="text-sm">&copy; {{ date('Y') }} {{ config('app.name', 'Ticketing System') }}</p>
    </footer>

    {{-- Extra scripts (per-view injection) --}}
    @stack('scripts')
</body>
</html>