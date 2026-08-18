<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
