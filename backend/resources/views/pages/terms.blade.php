<x-guest-layout>
    <section class="max-w-4xl mx-auto px-6 py-20">
        <div class="flex justify-center mb-8">
            <div class="w-20 h-20">
                <x-authentication-card-logo class="w-full h-full object-contain" />
            </div>
        </div>
        <!-- Title -->
        <h1 class="text-4xl font-bold mb-4 text-center
                   text-red-700">
            {{ __("terms.title") }}
        </h1>

        <!-- Last Updated -->
        <p class="text-center text-sm mb-12
                  text-gray-500 dark:text-gray-400">
            {{ __("terms.last_updated", ['date' => now()->toFormattedDateString()]) }}
        </p>

        <!-- Sections -->
        <div class="space-y-10 text-lg leading-relaxed
                    text-gray-700 dark:text-gray-300">

            @forelse (__('terms.sections') as $section)
                <div>
                    <h2 class="text-2xl font-semibold mb-3
                               text-gray-900 dark:text-white">
                        {{ $section['title'] }}
                    </h2>

                    <p>
                        {{ str_replace(
                            ':email',
                            config('mail.from.address'),
                            $section['content']
                        ) }}
                    </p>
                </div>
            @empty
                <p class="text-center text-gray-500">
                    {{ __("No terms available.") }}
                </p>
            @endforelse

        </div>

    </section>

</x-guest-layout>
