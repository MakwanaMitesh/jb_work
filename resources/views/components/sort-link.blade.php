@props(['field', 'sort', 'direction'])

@php
    $isActive = $sort === $field;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
    $pointsUp = $isActive && $direction === 'asc';
@endphp

<a href="{{ request()->fullUrlWithQuery(['sort' => $field, 'direction' => $nextDirection]) }}"
    class="jb-sort-link @if ($isActive) jb-sort-link--active @endif">
    {{ $slot }}
    <svg class="jb-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="{{ $pointsUp ? 'transform: rotate(180deg);' : '' }}">
        <path d="m6 9 6 6 6-6"/>
    </svg>
</a>
