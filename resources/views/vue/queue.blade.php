@extends('vue.wrapper')

@section('content')

    @auth
        <div id="admin-queue-app" class="w-full bg-gradient-to-br from-[#003D5B]/15 to-[#003D5B]/75">
        </div>
    @else
        <div id="queue-app" class="w-full bg-gradient-to-br from-[#003D5B]/15 to-[#003D5B]/75">
        </div>
    @endauth

@endsection
