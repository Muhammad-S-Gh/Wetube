<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WeTube') }}</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/icon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Fonts --}}
    <link href="https://fonts.cdnfonts.com/css/la-raimunda" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/lora" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lalezar:wght@200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Initial dark mode --}}
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    @livewireStyles
</head>

<body
    x-data="{ dark: localStorage.theme === 'dark' }"
    x-init="$watch('dark', value => {
        document.documentElement.classList.toggle('dark', value)
        localStorage.theme = value ? 'dark' : 'light'
    })"
    class="min-h-screen bg-gray-50 text-gray-900 antialiased
           dark:bg-[#0f0f0f] dark:text-gray-100">

<div class="min-h-screen flex flex-col">
    <!-- TOP BAR (Dashboard style) -->
    <header class="w-full border-b border-gray-200
                   bg-white dark:bg-[#0f0f0f] dark:border-[#222]">

        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}"
            class="flex flex-row items-center gap-3"
            dir="ltr">
                <img src="{{ asset('images/logo.svg') }}"
                    alt="WeTube Logo"
                    class="h-14 w-14 object-contain">
                <span class="text-4xl font-bold text-red-600 tracking-tight font-titleEn">
                    WeTube
                </span>
            </a>

            <!-- Right Side -->
            <div class="flex items-center gap-4">
                <!-- User dropdown -->
                @auth
                    <x-user-dropdown />
                @endauth
            </div>
        </div>
    </header>

    <!-- Jetstream Navigation -->
    <div class="border-b border-gray-200 dark:border-[#222]
                bg-white dark:bg-[#0f0f0f]">
        <div class="max-w-7xl mx-auto px-6">
            <livewire:navigation-menu />
        </div>
    </div>

    <!-- Page Content -->
    <main class="flex-1 w-full">
        <div class="max-w-7xl mx-auto px-6 py-8">

            {{-- Slot for Livewire pages --}}
            {{ $slot ?? '' }}

            {{-- Slot for Blade pages --}}
            @yield('content')
        </div>
    </main>
</div>

@stack('modals')
@livewireScripts

</body>
</html>
