<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ __('site.profile') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100 dark:bg-[#0a0a0a] rounded-3xl">
        <div class="max-w-5xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#181818] border border-gray-200 dark:border-[#222] rounded-3xl shadow-xl p-6">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('site.profile') }}</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('site.profile_overview') }}</p>
            </div>

            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                <div class="bg-white dark:bg-[#181818] border border-gray-200 dark:border-[#222] rounded-2xl shadow-sm p-6">
                    @livewire('profile.update-profile-information-form')
                </div>
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="bg-white dark:bg-[#181818] border border-gray-200 dark:border-[#222] rounded-2xl shadow-sm p-6">
                    @livewire('profile.update-password-form')
                </div>
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="bg-white dark:bg-[#181818] border border-gray-200 dark:border-[#222] rounded-2xl shadow-lg p-6">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="bg-white dark:bg-[#181818] border border-gray-200 dark:border-[#222] rounded-2xl shadow-sm p-6">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <div class="bg-white dark:bg-[#181818] border border-gray-200 dark:border-[#222] rounded-2xl shadow-sm p-6">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
