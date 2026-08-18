<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 active:bg-red-700 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
