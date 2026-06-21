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
            {{ __("policy.title") }}
        </h1>

        <!-- Last Updated -->
        <p class="text-center text-sm mb-12
                  text-gray-500 dark:text-gray-400">
            {{ __("policy.last_updated", ['date' => now()->format('F d, Y')]) }}
        </p>

        <!-- Sections -->
        <div class="space-y-10 text-lg leading-relaxed
                    text-gray-700 dark:text-gray-300">

            @foreach (__('policy.sections') as $section)
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
            @endforeach

        </div>

    </section>

</x-guest-layout>
