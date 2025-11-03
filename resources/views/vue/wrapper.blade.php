<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ticketing System') }}</title>

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/vue.js'])

    {{-- Extra styles (per-view injection) --}}
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <div class="bg-[#007380] dark:bg-[#0a0a0a] text-[#1b1b18] flex items-center lg:justify-center min-h-screen flex-col">
        @yield('content')
    </div>

    {{-- Extra scripts (per-view injection) --}}
    @stack('scripts')
</body>
</html>
