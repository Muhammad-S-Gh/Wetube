<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('site.admin_panel') }} - {{ config('app.name', 'Wetube') }}</title>

    <link rel="icon" href="{{ asset('images/icon.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.cdnfonts.com/css/la-raimunda" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/lora" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lalezar:wght@200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased dark:bg-[#0f0f0f] dark:text-gray-100">
    <nav class="w-full border-b border-gray-200 bg-white dark:border-[#222] dark:bg-[#0f0f0f]">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" dir="ltr">
                <img src="{{ asset('images/logo.svg') }}" alt="WeTube" class="h-12 w-12 object-contain">
                <div>
                    <p class="text-2xl font-bold text-red-600 font-titleEn">WeTube</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('site.admin_panel') }}</p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <!-- Alerts bell removed (redundant) -->
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-[#1b1b1b]">{{ __('site.admin_dashboard') }}</a>
                    <a href="{{ route('admin.users') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-[#1b1b1b]">{{ __('site.admin_users') }}</a>
                    <a href="{{ route('admin.videos') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-[#1b1b1b]">{{ __('site.admin_videos') }}</a>
                    <a href="{{ route('admin.alerts') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-[#1b1b1b]">{{ __('site.admin_alerts') }}</a>
                @endauth

                <a href="{{ route('lang.switch', app()->getLocale() === 'en' ? 'ar' : 'en') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100 dark:border-[#333] dark:text-gray-300 dark:hover:bg-[#1b1b1b]">
                    {{ app()->getLocale() === 'en' ? 'العربية (AR)' : 'English (EN)' }}
                </a>

                <button
                    type="button"
                    onclick="document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100 dark:border-[#333] dark:text-gray-300 dark:hover:bg-[#1b1b1b]">
                    {{ __('site.toggle_theme') }}
                </button>

                @auth
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                            {{ __('site.logout') }}
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-8">
        @if (Session::has('success'))
            <div class="mx-auto mb-6 max-w-2xl rounded-xl bg-green-500 p-4 text-center text-white">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-auto mb-6 max-w-2xl rounded-xl bg-red-500 p-4 text-center text-white">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>

    @yield('script')
</body>
</html>
