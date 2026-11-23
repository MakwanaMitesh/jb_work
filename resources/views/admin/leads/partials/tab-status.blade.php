{{-- Completion dot on a Lead tab button, for the tabs the lead's current workflow stage must complete. Expects $tabKey and $tabProgress (from _form). --}}
@if (isset($tabProgress) && $tabProgress->has($tabKey))
    @php
        $tabState = $tabProgress[$tabKey]['status'];
        [$tabDot, $tabStateLabel] = match ($tabState) {
            'completed' => ['jb-dot-emerald', 'Completed'],
            'in_progress' => ['jb-dot-amber', 'In Progress'],
            default => ['jb-dot-rose', 'Pending'],
        };
    @endphp
    <span class="jb-dot {{ $tabDot }}" title="{{ $tabStateLabel }}" aria-label="{{ $tabStateLabel }}"></span>
@endif
