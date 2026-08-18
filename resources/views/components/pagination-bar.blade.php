@props(['paginator', 'label' => 'items'])

<div class="jb-pagination-bar d-flex align-items-center justify-content-between flex-wrap gap-3">
    <!-- Left: Showing stats -->
    <div class="text-muted small">
        @if ($paginator->total() > 0)
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        @else
            No {{ $label }} found
        @endif
    </div>

    <!-- Center: Per page select -->
    @if ($paginator->total() > 0)
        <div class="d-flex align-items-center justify-content-center">
            <form method="GET" class="d-flex align-items-center gap-2 m-0">
                @foreach (request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <span class="text-muted text-nowrap small">Per page</span>
                <select name="per_page" class="form-select form-select-sm jb-per-page-select" onchange="this.form.submit()" style="border-radius: 6px; padding: 0.35rem 1.75rem 0.35rem 0.75rem; min-height: auto; height: 32px;">
                    @foreach ([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <!-- Right: Pagination links -->
    <div class="d-flex align-items-center justify-content-end">
        {{ $paginator->onEachSide(1)->links() }}
    </div>
</div>
