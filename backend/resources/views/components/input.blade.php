@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => "block w-full mt-1 px-4 py-2 rounded-lg bg-gray-100 dark:bg-[#0f0f0f]
            text-gray-900 dark:text-gray-100 border-gray-200 dark:border-[#222]
            border focus:border-red-500 focus:ring-red-500 dark:focus:border-red-500
            dark:focus:ring-red-500 placeholder-gray-400 dark:placeholder-gray-500 transition"
       ]) !!}>
