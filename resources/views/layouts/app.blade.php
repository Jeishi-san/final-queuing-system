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

    {{-- Main Content --}}
    <main class="flex-grow container mx-auto p-6">
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
