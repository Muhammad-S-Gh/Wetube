<button {{ $attributes->merge([
    'type' =>
        'submit',
        'class' => "
            inline-flex items-center justify-center px-5 py-2.5
            bg-red-600 text-white font-medium rounded-lg
            hover:bg-red-700
            focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2
            dark:bg-red-600 dark:hover:bg-red-700
            transition"
    ]) }}>
    {{ $slot }}
</button>
