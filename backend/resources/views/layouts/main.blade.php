<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Wetube') }}</title>

    {{-- Icons --}}
    <link rel="icon" href="{{ asset("images/icon.png") }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Fonts --}}
    <link href="https://fonts.cdnfonts.com/css/la-raimunda" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/lora" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lalezar:wght@200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

    {{-- Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Dark Mode --}}
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased
            dark:bg-[#0f0f0f] dark:text-gray-100">

@php
    $navNotifications = collect();
    $navNotificationCount = 0;

    if (auth()->check()) {
        $navNotificationCount = auth()->user()->notifications()->count();
        $navNotifications = auth()->user()->notifications()->latest()->limit(8)->get();
    }
@endphp

<div>
    <!-- NAVBAR -->
    <nav class="w-full border-b border-gray-200
                bg-white dark:bg-[#0f0f0f] dark:border-[#222]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between py-4">

                <!-- LOGO -->
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

                <!-- CENTER LINKS -->
                @if (!auth()->check() || !auth()->user()?->is_admin)
                    <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                        <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-600 transition">
                            <i class="fas fa-home text-sm opacity-80"></i> {{__("site.home_page")}}
                        </a>

                        <a href="{{ route('explore') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-600 transition">
                            <i class="fas fa-compass text-sm opacity-80"></i> {{__("site.explore")}}
                        </a>

                        <a href="{{ route('history.index') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-600 transition">
                            <i class="fas fa-history text-sm opacity-80"></i> {{__("site.watch_history")}}
                        </a>

                        <a href="{{ route('videos.create') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-600 transition">
                            <i class="fas fa-upload text-sm opacity-80"></i> {{__("site.upload_video")}}
                        </a>

                        <a href="{{ route('videos.index') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-600 transition">
                            <i class="far fa-play-circle text-sm opacity-80"></i> {{__("site.my_videos")}}
                        </a>

                        <a href="{{ route('channels.index') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-600 transition">
                            <i class="fas fa-film text-sm opacity-80"></i> {{__("site.channels")}}
                        </a>
                    </div>
                @endif

                <!-- RIGHT SIDE -->
                <div class="flex items-center gap-4">

                    @guest
                        <a href="{{ route('login') }}" class="hover:text-red-600 transition">
                            {{ __('login') }}
                        </a>

                        <a href="{{ route('register') }}" class="hover:text-red-600 transition">
                            {{ __('register') }}
                        </a>
                    @else
                    @if (auth()->user()?->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400">
                            <i class="fas fa-shield-halved"></i>
                            {{ __('site.admin_dashboard') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    @else
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400"
                            aria-label="{{ __('site.notifications') }}">
                            <i class="fas fa-bell"></i>
                            @if ($navNotificationCount > 0)
                                <span class="absolute -right-1 -top-1 rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">
                                    {{ $navNotificationCount > 99 ? '99+' : $navNotificationCount }}
                                </span>
                            @endif
                        </button>

                        <div
                            x-show="open"
                            x-transition
                            @click.outside="open = false"
                            class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} z-50 mt-3 w-80 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-[#2a2a2a] dark:bg-[#151515]"
                            style="display: none;">
                            <div class="border-b border-gray-100 px-4 py-3 dark:border-[#2a2a2a]">
                                <p class="text-sm font-semibold">{{ __('site.notifications') }}</p>
                            </div>

                            @if ($navNotifications->isEmpty())
                                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('site.no_notifications') }}
                                </div>
                            @else
                                <div class="max-h-96 overflow-y-auto">
                                    @foreach ($navNotifications as $notification)
                                        @php
                                            $translatedMessage = __($notification->notification);
                                            $message = $translatedMessage === $notification->notification ? $notification->notification : $translatedMessage;
                                        @endphp
                                        <div class="flex gap-3 border-b border-gray-100 px-4 py-3 last:border-b-0 dark:border-[#222]">
                                            <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full text-xs {{ $notification->success ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' }}">
                                                <i class="fas {{ $notification->success ? 'fa-check' : 'fa-xmark' }}"></i>
                                            </span>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm leading-5 text-gray-800 dark:text-gray-100">{{ $message }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ optional($notification->created_at)->diffForHumans() }}</p>
                                            </div>

                                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="self-start" onsubmit="return confirm(@json(__('site.confirm_delete_notification')))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 text-xs text-gray-500 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-400 dark:hover:border-red-600 dark:hover:text-red-400" aria-label="{{ __('site.delete_notification') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                        <x-user-dropdown />
                    @endif

                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN -->
    <main class="max-w-7xl mx-auto px-6 py-8">
        @if (Session::has('success'))
            <div class="max-w-2xl mx-auto mb-6 p-4 rounded-xl bg-green-500 text-white text-center">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="max-w-2xl mx-auto mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-900/10 dark:text-rose-200">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

</div>

@yield('script')
</body>
</html>
