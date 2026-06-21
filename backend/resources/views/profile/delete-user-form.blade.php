<x-action-section class="bg-white dark:bg-[#141414] border border-gray-200 dark:border-[#222] rounded-3xl shadow-xl p-8">
    <x-slot name="title">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('site.delete_account') }}
        </h2>
    </x-slot>

    <x-slot name="description">
        {{ __('site.delete_Permanently') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
            {{ __('site.delete_note') }}
        </div>

        <div class="mt-5 bg-gray-50 dark:bg-[#181818] border-t border-gray-200 dark:border-[#222] p-4 rounded-lg">
            <x-button class="bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-400" wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                {{ __('site.delete_account') }}
            </x-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                {{ __('site.delete_account') }}
            </x-slot>

            <x-slot name="content">
                {{ __('site.delete_confirm') }}

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                                autocomplete="current-password"
                                placeholder="{{ __('Password') }}"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="deleteUser" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    {{ __('site.delete_reject') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('site.delete_account') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
