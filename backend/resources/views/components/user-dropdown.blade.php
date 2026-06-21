<!-- DROPDOWN -->
<div x-data="{ open: false }" class="relative">

    <!-- BUTTON -->
    <button @click="open = !open" class="flex items-center focus:outline-none">
        <img class="w-14 h-14 rounded-full object-cover"
                src="{{ Auth::user()->profile_photo_url }}"
                alt="profile-image">
    </button>

    <!-- MENU -->
    <div x-cloak
        x-show="open"
        x-transition
        @click.outside="open = false"
        class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }}
                mt-3 min-w-[260px] max-w-md
                rounded-xl border shadow-lg z-50
                bg-white border-gray-200
                dark:bg-[#181818] dark:border-[#262626]
                {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">

        <!-- USER -->
        <div class="p-4 border-b border-gray-200 dark:border-[#262626]">
            <div class="flex items-center gap-3 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                <img src="{{ Auth::user()->profile_photo_url }}"
                        class="w-10 h-10 rounded-full object-cover flex-shrink-0">

                <div class="min-w-0 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                    <div class="font-semibold break-words">
                        {{ Auth::user()->name }}
                    </div>

                    <div class="text-sm text-gray-500 dark:text-gray-400 break-words">
                        {{ Auth::user()->email }}
                    </div>
                </div>
            </div>
        </div>

        <!-- LINKS -->
        <div class="mt-2 pt-2">
            <a href="{{ route('profile.show') }}"
                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]
                    text-gray-700 dark:text-gray-300 gap-3
                    {{ app()->getLocale() === 'ar' ? 'flex-row-reverse text-right' : 'text-left' }}">
                <i class="fas fa-user"></i>
                {{ __('Profile') }}
            </a>

            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                <a href="{{ route('api-tokens.index') }}"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]
                    text-gray-700 dark:text-gray-300 gap-3
                    {{ app()->getLocale() === 'ar' ? 'flex-row-reverse text-right' : 'text-left' }}">
                <i class="fas fa-key"></i>
                    {{ __('API Tokens') }}
                </a>
            @endif

            <!-- Theme Toggle -->
            <div x-data="{
                dark: localStorage.getItem('theme') === 'dark',
                init() {
                    this.applyTheme()
                },
                toggle() {
                    this.dark = !this.dark
                    localStorage.setItem('theme', this.dark ? 'dark' : 'light')
                    this.applyTheme()
                },
                applyTheme() {
                    document.documentElement.classList.toggle('dark', this.dark)
                }
            }" x-init="init()">

                <button
                    @click="toggle"
                    class="w-full px-4 py-2 flex items-center gap-2
                        text-gray-700 dark:text-gray-300
                        hover:bg-gray-100 dark:hover:bg-[#222]
                        transition
                        {{ app()->getLocale() === 'ar'
                            ? 'justify-start text-right'
                            : 'justify-start text-left'
                        }}">

                    <!-- ICON -->
                    <i x-show="!dark" class="fas fa-moon"></i>
                    <i x-show="dark" class="fas fa-sun"></i>

                    <!-- LABEL -->
                    <span x-text="dark ? '{{ __('site.light_mode') }}' : '{{ __('site.dark_mode') }}'"></span>

                </button>
            </div>

            <!-- Language Switcher -->
            <button
                @click="window.location = '{{ route('lang.switch', app()->getLocale() === 'en' ? 'ar' : 'en') }}'"
                class="w-full px-4 py-2
                        text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#222]
                        transition {{ app()->getLocale() === 'ar' ? 'flex-row-reverse text-right' : 'text-left' }}">
                <i class="fas fa-language text-base"></i>
                {{ app()->getLocale() === 'en' ? 'العربية (AR)' : 'English (EN)' }}
            </button>

            <a href="{{ route('about') }}"
                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]
                text-gray-700 dark:text-gray-300 gap-3
                {{ app()->getLocale() === 'ar' ? 'flex-row-reverse text-right' : 'text-left' }}">
                <i class="fas fa-circle-info"></i>
                {{  __('site.about') }}
            </a>

            <a href="{{ route('policy') }}"
                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]
                text-gray-700 dark:text-gray-300 gap-3
                {{ app()->getLocale() === 'ar' ? 'flex-row-reverse text-right' : 'text-left' }}">
                <i class="fas fa-shield-halved"></i>
                {{ __('site.privacy') }}
            </a>

            <a href="{{ route('terms') }}"
                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]
                text-gray-700 dark:text-gray-300 gap-3
                {{ app()->getLocale() === 'ar' ? 'flex-row-reverse text-right' : 'text-left' }}">
                <i class="fas fa-file-contract"></i>
                {{ __('site.terms') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full px-4 py-2 text-red-500 transition
                        hover:bg-gray-100 dark:hover:bg-[#222] hover:text-red-600
                        {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} gap-3">
                <i class="fas fa-sign-out-alt"></i>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>

        <!-- TEAMS -->
        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()?->currentTeam)
            <div class="mt-2 pt-2">

                <div class="px-4 py-2 text-xs text-gray-500 uppercase">
                    {{ __('Manage Team') }}
                </div>

                <a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]">
                    {{ __('Team Settings') }}
                </a>

                @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                    <a href="{{ route('teams.create') }}"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]">
                        {{ __('Create New Team') }}
                    </a>
                @endcan

                @foreach (Auth::user()->allTeams() as $team)
                    <x-switchable-team :team="$team"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#222]" />
                @endforeach

            </div>
        @endif
    </div>
</div>
