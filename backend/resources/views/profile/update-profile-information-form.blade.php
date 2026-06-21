<x-form-section submit="updateProfileInformation"
                class="bg-white dark:bg-[#141414] border border-gray-200 dark:border-[#222] rounded-3xl shadow-xl p-8">
    <x-slot name="title">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('site.profile_information') }}
        </h2>
    </x-slot>

    <x-slot name="description">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('site.profile_information_note') }}
        </p>
    </x-slot>

    <x-slot name="form">

        {{-- Profile Photo --}}
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}"
                 class="col-span-6 sm:col-span-4 flex items-center gap-6">

                {{-- Hidden File Input --}}
                <input type="file" id="photo" class="hidden"
                       wire:model.live="photo"
                       x-ref="photo"
                       x-on:change="
                           photoName = $refs.photo.files[0].name;
                           const reader = new FileReader();
                           reader.onload = (e) => {
                               photoPreview = e.target.result;
                           };
                           reader.readAsDataURL($refs.photo.files[0]);
                       " />

                {{-- Current Photo --}}
                <div class="relative">
                    <div x-show="! photoPreview" class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-300 dark:border-[#444]">
                        <img src="{{ $this->user->profile_photo_url }}"
                             alt="{{ $this->user->name }}"
                             class="w-full h-full rounded-full object-cover">
                    </div>

                    {{-- New Photo Preview --}}
                    <div x-show="photoPreview" style="display: none;" class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-300 dark:border-[#444]">
                        <span class="block w-full h-full bg-cover bg-no-repeat bg-center"
                              x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                        </span>
                    </div>
                </div>

                {{-- Buttons Side by Side --}}
                <div class="flex flex-col sm:flex-row gap-3">

                    <x-secondary-button type="button"
                                        class="px-4 py-2"
                                        x-on:click.prevent="$refs.photo.click()">
                        {{ __('site.profile_image_set') }}
                    </x-secondary-button>

                    @if ($this->user->profile_photo_path)
                        <x-secondary-button type="button"
                                            class="px-4 py-2"
                                            wire:click="deleteProfilePhoto">
                            {{ __('site.profile_image_delete') }}
                        </x-secondary-button>
                    @endif
                </div>

                <x-input-error for="photo" class="mt-2 w-full" />
            </div>
        @endif

        {{-- Name --}}
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('site.profile_name') }}" />
            <x-input id="name" type="text"
                     class="mt-1 block w-full"
                     wire:model.defer="state.name"
                     required autocomplete="name"/>
            <x-input-error for="name" class="mt-2" />
        </div>

        {{-- Email --}}
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('site.profile_email') }}" />
            <x-input id="email" type="email"
                     class="mt-1 block w-full"
                     wire:model.defer="state.email"
                     required autocomplete="username"/>
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-3 text-yellow-600 dark:text-yellow-400">
                    {{ __('site.email_unverified') }}
                    <button type="button"
                            class="underline text-sm text-red-600 hover:text-red-800"
                            wire:click.prevent="sendEmailVerification"
                            wire:loading.attr="disabled">
                        {{ __('site.re-send_verification_email') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('site.verification_link_sent') }}
                    </p>
                @endif
            @endif
        </div>

    </x-slot>

    <x-slot name="actions">
        <div class="flex w-full items-center gap-4 {{ app()->getLocale() === 'ar' ? 'justify-start' : 'justify-end' }}">
            <x-action-message class="text-green-600 dark:text-green-400" on="saved">
                {{ __('site.profile_saved') }}
            </x-action-message>

            <x-button class="bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-400" wire:loading.attr="disabled" wire:target="photo">
                {{ __('site.profile_save') }}
            </x-button>
        </div>
    </x-slot>

</x-form-section>
