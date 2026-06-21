<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" dir="rtl">
            @csrf

            <div>
                <x-label for="email" value="{{ __('site.email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('site.password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('site.remember_me') }}</span>
                </label>
            </div>

            <div class="flex flex-col items-end mt-6 gap-4">
                <div class="flex items-center gap-4 text-sm justify-end">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                        class="text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500 transition">
                            {{ __('site.forgot_password') }}
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                        class="font-medium text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400 transition">
                            {{ __('site.register') }}
                        </a>
                    @endif

                </div>
                <x-button class="w-full justify-center bg-red-600 hover:bg-red-700 focus:ring-red-500">
                    {{ __('site.login') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
