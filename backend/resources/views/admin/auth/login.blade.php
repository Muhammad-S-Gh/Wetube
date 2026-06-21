<x-guest-layout>
    <div x-data="{
            dark: localStorage.getItem('theme') === 'dark',
            init() {
                document.documentElement.classList.toggle('dark', this.dark)
            },
            toggleTheme() {
                this.dark = !this.dark
                localStorage.setItem('theme', this.dark ? 'dark' : 'light')
                document.documentElement.classList.toggle('dark', this.dark)
            }
        }"
        x-init="init()"
        class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6 lg:px-8">

        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(239,68,68,0.18),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(17,24,39,0.08),transparent_32%),linear-gradient(180deg,rgba(250,250,250,1),rgba(243,244,246,1))] dark:bg-[radial-gradient(circle_at_top_left,rgba(239,68,68,0.16),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.05),transparent_32%),linear-gradient(180deg,rgba(12,12,12,1),rgba(18,18,18,1))]"></div>

        <div class="absolute left-10 top-10 h-40 w-40 rounded-full bg-red-500/10 blur-3xl dark:bg-red-500/15"></div>
        <div class="absolute bottom-8 right-8 h-56 w-56 rounded-full bg-black/5 blur-3xl dark:bg-white/5"></div>

        <div class="relative grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-white/60 bg-white/10 shadow-[0_30px_100px_-35px_rgba(0,0,0,0.45)] backdrop-blur-xl dark:border-white/10 dark:bg-[#131313]/90 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="hidden flex-col justify-between bg-white text-gray-900 dark:bg-[#111111] dark:text-white px-10 py-10 lg:flex">
                <div class="space-y-10">
                    <div class="flex items-center gap-3" dir="ltr">
                        <img src="{{ asset('images/logo.svg') }}" alt="WeTube Logo" class="h-14 w-14 object-contain">
                        <span class="text-4xl font-bold tracking-tight text-red-500 font-titleEn">WeTube</span>
                    </div>

                    <div class="max-w-md space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-red-500 dark:text-red-400">{{ __('site.admin_panel') }}</p>
                        <h2 class="text-4xl font-semibold leading-tight tracking-tight text-gray-900 dark:text-white">{{ __('site.admin_login_heading') }}</h2>
                        <p class="text-sm leading-7 text-gray-600 dark:text-gray-300">
                            {{ __('site.admin_back_to_site') }}
                        </p>
                    </div>
                </div>

                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-4 py-2">
                        <i class="fas fa-shield-halved text-red-500 dark:text-red-400"></i>
                        <span>{{ __('site.admin_login_heading') }}</span>
                    </div>
                    <p class="max-w-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('site.admin_login_description') }}</p>
                </div>
            </section>

            <section class="relative px-5 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <div class="mb-8 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3 lg:hidden" dir="ltr">
                        <img src="{{ asset('images/logo.svg') }}" alt="WeTube Logo" class="h-11 w-11 object-contain">
                        <span class="text-3xl font-bold tracking-tight text-red-600 font-titleEn">WeTube</span>
                    </div>

                    <div class="ml-auto flex items-center gap-2 rounded-full border border-gray-200 bg-white/90 p-1 shadow-sm dark:border-[#2a2a2a] dark:bg-[#171717]" dir="ltr">
                        <a href="{{ route('lang.switch', 'en') }}"
                           class="rounded-full px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition {{ app()->getLocale() === 'en' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }}">
                            EN
                        </a>
                        <a href="{{ route('lang.switch', 'ar') }}"
                           class="rounded-full px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition {{ app()->getLocale() === 'ar' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }}">
                            AR
                        </a>
                        <button type="button"
                                @click="toggleTheme()"
                                class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                            <i x-show="!dark" class="fas fa-moon"></i>
                            <i x-show="dark" class="fas fa-sun"></i>
                            <span>{{ __('site.toggle_theme') }}</span>
                        </button>
                    </div>
                </div>

                <div class="mb-8 space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-red-500">{{ __('site.admin_panel') }}</p>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100 sm:text-4xl">{{ __('site.admin_login_heading') }}</h1>
                    <p class="max-w-lg text-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('site.admin_back_to_site') }}</p>
                </div>

                <x-validation-errors class="mb-4" />

                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/10 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <x-label for="email" value="{{ __('site.email') }}" />
                        <x-input id="email" class="block w-full rounded-2xl border-gray-200 bg-white/90 px-4 py-3 shadow-sm transition focus:border-red-500 focus:ring-red-500 dark:border-[#2f2f2f] dark:bg-[#101010]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    </div>

                    <div class="space-y-2">
                        <x-label for="password" value="{{ __('site.password') }}" />
                        <x-input id="password" class="block w-full rounded-2xl border-gray-200 bg-white/90 px-4 py-3 shadow-sm transition focus:border-red-500 focus:ring-red-500 dark:border-[#2f2f2f] dark:bg-[#101010]" type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
                        <label for="remember_me" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <x-checkbox id="remember_me" name="remember" />
                            <span>{{ __('site.remember_me') }}</span>
                        </label>

                        <a href="/" class="font-medium text-gray-500 transition hover:text-red-600 dark:text-gray-400 dark:hover:text-red-500">{{ __('site.admin_back_to_site') }}</a>
                    </div>

                    <x-button class="flex w-full justify-center rounded-2xl bg-red-600 py-3.5 font-semibold text-white shadow-lg shadow-red-500/25 transition hover:bg-red-700 focus:ring-red-500">
                        {{ __('site.login') }}
                    </x-button>
                </form>
            </section>
        </div>
    </div>
</x-guest-layout>
