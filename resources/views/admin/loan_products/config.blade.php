<x-admin-layout :title="'Configure: ' . $loanProduct->name" :white-bg="true">
    <div class="mb-6">
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.loan-products.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Loan Products</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <a href="{{ route('admin.loan-products.show', $loanProduct) }}" class="hover:text-slate-600 dark:hover:text-slate-300">{{ $loanProduct->name }}</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">Configure</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Configure Supported Constitutions</h1>
        <p class="text-sm text-slate-500 mt-1">Select which customer constitutions are valid for <strong>{{ $loanProduct->name }}</strong>.</p>
    </div>

    <form method="POST" action="{{ route('admin.loan-products.config.update', $loanProduct) }}">
        @csrf

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm max-w-2xl">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4 border-b border-slate-100 dark:border-slate-850 pb-2">Customer Constitution Checklist</h3>

            <div class="space-y-4">
                @foreach ($constitutions as $const)
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-150 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-950/20 cursor-pointer transition select-none">
                        <input type="checkbox" name="constitutions[]" value="{{ $const->id }}"
                            @checked(in_array($const->id, $assignedConstitutionIds))
                            class="rounded border-slate-350 dark:border-slate-700 text-primary-600 focus:ring-primary-500/20 mt-1 w-4 h-4 shrink-0">
                        <div>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $const->name }}</span>
                            <span class="block text-xs text-slate-400 mt-0.5">Code: <span class="font-mono">{{ $const->code }}</span> @if($const->description) | {{ $const->description }} @endif</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3 max-w-2xl">
            <a href="{{ route('admin.loan-products.show', $loanProduct) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                Save Configurations
            </button>
        </div>
    </form>
</x-admin-layout>
