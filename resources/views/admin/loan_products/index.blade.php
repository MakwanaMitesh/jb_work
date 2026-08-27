<x-admin-layout title="Loan Products" :white-bg="true">
    <div class="mb-6">
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.loan-products.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Loan Products</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">List</span>
        </nav>

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Loan Products</h1>
            @can('loan_products.create')
                <a href="{{ route('admin.loan-products.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    <span>New Product</span>
                </a>
            @endcan
        </div>
    </div>

    <x-datatable-card tableId="loan-products-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                <select data-filter-column="2" data-filter-type="regex" class="form-select w-full border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Code</th>
            <th data-col="status" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Order</th>
            <th class="px-6 py-3.5 text-right w-16"></th>
        </x-slot:thead>

        @forelse ($products as $product)
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-150">
                <td class="px-6 py-4.5 whitespace-nowrap">
                    <a href="{{ route('admin.loan-products.show', $product) }}" class="font-semibold text-primary-600 hover:underline text-sm">{{ $product->name }}</a>
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap">
                    <span class="text-sm font-mono text-slate-650 dark:text-slate-350">{{ $product->code }}</span>
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap" data-col="status">
                    @if ($product->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 dark:border-emerald-900/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                            Inactive
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap">
                    <span class="text-sm text-slate-500">{{ $product->sort_order }}</span>
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap text-right">
                    <div class="relative inline-block text-left" data-kebab-container>
                        <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 flex items-center justify-center transition" type="button" data-kebab-btn>
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                        </button>
                        <div class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-1 hidden z-40" data-kebab-menu>
                            @can('loan_products.edit')
                                <a href="{{ route('admin.loan-products.edit', $product) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md no-underline">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Edit</span>
                                </a>
                                <a href="{{ route('admin.loan-products.config.edit', $product) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md no-underline">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>Configure Constitutions</span>
                                </a>
                                <form method="POST" action="{{ route('admin.loan-products.toggle-status', $product) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md text-left">
                                        @if ($product->status === 'active')
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            <span>Deactivate</span>
                                        @else
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Activate</span>
                                        @endif
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-450">No loan products found.</td>
            </tr>
        @endforelse
    </x-datatable-card>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</x-admin-layout>
