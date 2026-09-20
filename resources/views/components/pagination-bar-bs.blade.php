@props(['paginator', 'label' => 'items'])

{{--
    Bootstrap counterpart of x-pagination-bar.

    Renders the paginator with Laravel's bundled bootstrap-5 view explicitly,
    rather than calling Paginator::useBootstrapFive() globally — that would
    also change pagination on the pages still running Tailwind.
--}}
<div class="jb-pagination-bar d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
    <div class="jb-muted fw-medium">
        @if ($paginator->total() > 0)
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        @else
            No {{ $label }} found
        @endif
    </div>

    @if ($paginator->total() > 0)
        <div class="d-flex align-items-center justify-content-center">
            <form method="GET" class="d-flex align-items-center gap-2 m-0">
                @foreach (request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <span class="jb-muted fw-medium text-nowrap">Per page</span>
                <select name="per_page" class="form-select form-select-sm fw-semibold" style="width:auto" onchange="this.form.submit()">
                    @foreach ([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-end">
        {{ $paginator->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>
