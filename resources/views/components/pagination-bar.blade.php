@props(['paginator', 'label' => 'items'])

<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
    <!-- Left: Showing stats -->
    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
        @if ($paginator->total() > 0)
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        @else
            No {{ $label }} found
        @endif
    </div>

    <!-- Center: Per page select -->
    @if ($paginator->total() > 0)
        <div class="flex items-center justify-center">
            <form method="GET" class="flex items-center gap-2 m-0">
                @foreach (request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Per page</span>
                <select name="per_page" class="rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-amber-500 focus:ring-amber-500/20 text-xs font-semibold h-8 py-1 pl-2.5 pr-8" onchange="this.form.submit()">
                    @foreach ([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <!-- Right: Pagination links -->
    <div class="flex items-center justify-end">
        {{ $paginator->onEachSide(1)->links() }}
    </div>
</div>
