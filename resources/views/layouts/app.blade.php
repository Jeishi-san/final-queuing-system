<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticketing System</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
    {{-- Navigation --}}
    @include('layouts.navigation')

    {{-- Page Content --}}
    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>
