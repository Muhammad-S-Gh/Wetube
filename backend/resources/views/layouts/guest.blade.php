<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- FavIcon --}}
        <link rel="icon" href="{{ asset("images/icon.png") }}" />

        <!-- Fonts -->
        <link href="https://fonts.cdnfonts.com/css/la-raimunda" rel="stylesheet">
        <link href="https://fonts.cdnfonts.com/css/lora" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Lalezar:wght@200..1000&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches))
            {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        </script>

        <!-- Styles -->
        @livewireStyles
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body x-data="{ dark: localStorage.theme === 'dark' }"
            x-init="$watch('dark', value => {
                document.documentElement.classList.toggle('dark', value)
                localStorage.theme = value ? 'dark' : 'light'
            })"
            class="bg-gray-50 text-gray-900 min-h-screen antialiased overflow-x-hidden
                dark:bg-[#0f0f0f] dark:text-gray-100">

        <div class="min-h-screen justify-center py-12
                    bg-gray-50 text-gray-900
                    dark:bg-[#0f0f0f] dark:text-gray-100 overflow-x-hidden">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
