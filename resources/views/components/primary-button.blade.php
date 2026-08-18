<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center h-10 px-5 bg-primary-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 active:bg-primary-700 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
