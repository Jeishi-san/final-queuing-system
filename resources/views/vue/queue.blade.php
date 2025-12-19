@extends('vue.wrapper')

@section('content')

    {{-- Check the specific ROLE of the logged-in user --}}
    @if(Auth::user()->role === 'it_staff')

        <div id="admin-queue-app" class="w-full h-screen bg-gradient-to-br from-[#003D5B]/15 to-[#003D5B]/75"></div>

    @else

        <div id="queue-app" class="w-full bg-gradient-to-br from-[#003D5B]/15 to-[#003D5B]/75"></div>

    @endif

@endsection
