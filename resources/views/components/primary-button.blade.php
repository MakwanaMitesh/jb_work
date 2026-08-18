<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 active:bg-amber-700 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
