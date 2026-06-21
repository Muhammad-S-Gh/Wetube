<div x-data="{ open: false }" class="w-full">
    <!-- Hamburger -->
    <div class="sm:hidden flex items-center justify-end px-6 py-3">
        <button @click="open = ! open"
            class="p-2 rounded-lg text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-[#1f1f1f] transition">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open"
         x-transition
         @click.outside="open = false"
         class="sm:hidden mx-4 mb-4 rounded-xl border
                border-gray-200 dark:border-[#222]
                bg-white dark:bg-[#181818]
                shadow-lg overflow-hidden">

        <div class="px-4 py-4 border-b border-gray-200 dark:border-[#222]">
            <div class="flex items-center gap-3">

                <img class="w-10 h-10 rounded-full object-cover"
                     src="{{ Auth::user()->profile_photo_url }}">

                <div>
                    <div class="font-semibold text-sm">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ Auth::user()->email }}
                    </div>
                </div>

            </div>
    </div>

    <!-- LINKS -->
    <div class="py-2 text-sm">

        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-2 px-4 py-2
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-100 dark:hover:bg-[#222] transition">

            <i class="fas fa-home text-xs"></i>
            {{ __('Dashboard') }}
        </a>

        <a href="{{ route('history.index') }}"
            class="flex items-center gap-2 px-4 py-2
                text-gray-700 dark:text-gray-300
                hover:bg-gray-100 dark:hover:bg-[#222] transition">

            <i class="fas fa-history text-xs"></i>
            {{ __('site.watch_history') }}
        </a>

        <a href="{{ route('profile.show') }}"
            class="flex items-center gap-2 px-4 py-2
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-100 dark:hover:bg-[#222] transition">

            <i class="fas fa-user text-xs"></i>
            {{ __('Profile') }}
        </a>
    </div>

    <!-- LOGOUT -->
    <div class="border-t border-gray-200 dark:border-[#222]">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full text-left px-4 py-2 text-red-500
                            hover:bg-gray-100 dark:hover:bg-[#222] transition">

                <i class="fas fa-sign-out-alt mr-2"></i>
                {{ __('Log Out') }}
            </button>
        </form>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('history.index') }}" :active="request()->routeIs('history.*')">
                    {{ __('site.watch_history') }}
                </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-gray-200"></div>

                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan

                    <!-- Team Switcher -->
                    @if (Auth::user()->allTeams()->count() > 1)
                        <div class="border-t border-gray-200"></div>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Switch Teams') }}
                        </div>

                        @foreach (Auth::user()->allTeams() as $team)
                            <x-switchable-team :team="$team" component="responsive-nav-link" />
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
