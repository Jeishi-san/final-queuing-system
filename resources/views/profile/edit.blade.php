@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-12 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg transition-all duration-300">
    <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white text-center">
        ✏️ Edit Your Profile
    </h2>

    {{-- ✅ Success Message --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6 text-center">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- 🖼 Profile Picture --}}
        <div class="flex items-center gap-6">
            <div class="relative">
                 <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : Vite::asset('resources/assets/img/profile.png') }}"
                     alt="Profile Picture"
                     class="w-28 h-28 rounded-full object-cover border-4 border-gray-300 dark:border-gray-600 shadow-sm">
                <div class="absolute bottom-1 right-1 bg-blue-600 text-white text-xs px-2 py-1 rounded-md shadow-sm">
                    Edit
                </div>
            </div>

            <div class="flex-1">
                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Upload New Picture</label>
                <input type="file" name="profile_picture"
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500"
                       accept="image/*">
                @error('profile_picture')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- 🧍 Name --}}
        <div>
            <label for="name" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- 📧 Email --}}
        <div>
            <label for="email" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- 🔒 Password Change (Optional Section) --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Change Password (Optional)</h3>
            
            <input type="password" name="password" placeholder="New Password"
                class="w-full p-3 mb-3 border rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password_confirmation" placeholder="Confirm New Password"
                class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500">
            
            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- 💾 Save Button --}}
        <div class="flex justify-end pt-6">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-md transition duration-300 focus:ring-2 focus:ring-blue-400">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
