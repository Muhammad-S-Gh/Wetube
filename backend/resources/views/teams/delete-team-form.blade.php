<x-action-section>
    <x-slot name="title">
        {{ __('site.teams_delete') }}
    </x-slot>

    <x-slot name="description">
        {{ __('site.teams_delete_Permanently') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('site.teams_delete_note') }}
        </div>

        <div class="mt-5">
            <x-danger-button wire:click="$toggle('confirmingTeamDeletion')" wire:loading.attr="disabled">
                {{ __('site.teams_delete') }}
            </x-danger-button>
        </div>

        <!-- Delete Team Confirmation Modal -->
        <x-confirmation-modal wire:model="confirmingTeamDeletion">
            <x-slot name="title">
                {{ __('site.teams_delete') }}
            </x-slot>

            <x-slot name="content">
                {{ __('site.teams_delete_confirm') }}
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingTeamDeletion')" wire:loading.attr="disabled">
                    {{ __('teams_delete_no') }}
                </x-secondary-button>

                <x-danger-button class="ml-2" wire:click="deleteTeam" wire:loading.attr="disabled">
                    {{ __('site.teams_delete') }}
                </x-danger-button>
            </x-slot>
        </x-confirmation-modal>
    </x-slot>
</x-action-section>