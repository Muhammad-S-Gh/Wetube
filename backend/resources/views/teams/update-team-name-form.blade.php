<x-form-section submit="updateTeamName"
    class="bg-white dark:bg-[#141414] border border-gray-200 dark:border-[#222] rounded-3xl shadow-xl p-8">
    <x-slot name="title">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    {{ __('site.teams_name') }}
        </h2>
    </x-slot>

    <x-slot name="description">
        {{ __('site.team_and_members') }}
    </x-slot>

    <x-slot name="form">
        <!-- Team Owner Information -->
        <div class="col-span-6">
            <x-label value="{{ __('site.teams_own') }}" />

            <div class="flex items-center mt-2">
                <img class="w-12 h-12 rounded-full object-cover" src="{{ $team->owner->profile_photo_url }}" alt="{{ $team->owner->name }}">

                <div class="mr-4 ml-4 leading-tight">
                    <div>{{ $team->owner->name }}</div>
                    <div class="text-gray-700 dark:text-gray-300 text-sm">{{ $team->owner->email }}</div>
                </div>
            </div>
        </div>

        <!-- Team Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('site.teams_name') }}" />

            <x-input id="name"
                        type="text"
                        class="mt-1 block w-full"
                        wire:model.defer="state.name"
                        :disabled="! Gate::check('update', $team)" />

            <x-input-error for="name" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <div class="flex w-full">
                <x-action-message class="mr-3" on="saved">
                    {{ __('site.teams_saved') }}
                </x-action-message>

                <x-button>
                    {{ __('site.teams_save') }}
                </x-button>
            </div>
        </x-slot>
    @endif
</x-form-section>
