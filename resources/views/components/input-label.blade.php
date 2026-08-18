@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'form-label fw-semibold mb-1.5']) }} style="font-size: 0.875rem; color: #374151;">
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-danger" style="color: #dc2626 !important; font-weight: 600;">*</span>
    @endif
</label>
