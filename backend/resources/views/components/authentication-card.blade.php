<div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0
            bg-gray-100 dark:bg-[#0f0f0f]">
    <div>
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4
                bg-white dark:bg-[#181818]
                text-gray-900 dark:text-gray-100
                shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
