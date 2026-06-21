<button {{ $attributes->merge([
    'type' => 'button',
      'class' => " inline-flex items-center justify-center px-5 py-2.5
                bg-gray-100 text-gray-800 border border-gray-300
                font-medium rounded-lg hover:bg-gray-200
                focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                dark:bg-[#1f1f1f] dark:text-gray-200 dark:border-[#333]
                dark:hover:bg-[#2a2a2a] dark:focus:ring-[#444]
                disabled:opacity-50 disabled:cursor-not-allowed transition"]
            ) }}>
    {{ $slot }}
</button>
