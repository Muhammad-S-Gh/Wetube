<x-form-section submit="updatePassword"
                class="bg-white dark:bg-[#141414] border border-gray-200 dark:border-[#222] rounded-3xl shadow-xl p-8">
    <x-slot name="title">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>
    </x-slot>

    <x-slot name="description">
        {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="current_password" value="{{ __('Current Password') }}" />
            <x-input id="current_password" type="password" class="mt-1 block w-full" wire:model="state.current_password" autocomplete="current-password" />
            <x-input-error for="current_password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password" value="{{ __('New Password') }}" />
            <x-input id="password" type="password" class="mt-1 block w-full" wire:model="state.password" autocomplete="new-password" />
            <x-input-error for="password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
            <x-input id="password_confirmation" type="password" class="mt-1 block w-full" wire:model="state.password_confirmation" autocomplete="new-password" />
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="flex items-center w-full justify-end">
            <x-action-message class="me-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button class="bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-400 ml-auto">
                {{ __('Save') }}
            </x-button>
        </div>
    </x-slot>
</x-form-section>
