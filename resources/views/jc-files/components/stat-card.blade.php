@props(['title', 'count', 'color' => 'gray'])

@php
    $colors = [
        'yellow' => 'bg-yellow-100 border-yellow-500 text-yellow-700',
        'blue'   => 'bg-blue-100 border-blue-500 text-blue-700',
        'green'  => 'bg-green-100 border-green-500 text-green-700',
        'gray'   => 'bg-gray-100 border-gray-500 text-gray-700',
    ];

    $classes = $colors[$color] ?? $colors['gray'];
@endphp

<div class="{{ $classes }} border-l-4 p-5 rounded-lg shadow">
    <h3 class="text-lg font-semibold">{{ $title }}</h3>
    <p class="text-3xl font-bold">{{ $count }}</p>
</div>
