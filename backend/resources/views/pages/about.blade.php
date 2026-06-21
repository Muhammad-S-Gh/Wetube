<x-guest-layout>

    <section class="max-w-4xl mx-auto px-6 py-20">
        <div class="flex justify-center mb-8">
            <div class="w-20 h-20">
                <x-authentication-card-logo class="w-full h-full object-contain" />
            </div>
        </div>
        <!-- Title -->
        <h1 class="text-4xl font-bold mb-10 text-center
                   text-red-700">
            {{ __("about.title") }}
        </h1>
        <!-- Paragraphs -->
        <div class="space-y-6 text-lg leading-relaxed
                    text-gray-600 dark:text-gray-400">
            <p>
                {{ __("about.p1") }}
            </p>
            <p>
                {{ __("about.p2") }}
            </p>
            <p>
                {{ __("about.p3") }}
            </p>
            <p>
                {{ __("about.p4") }}
            </p>
        </div>
    </section>

</x-guest-layout>
