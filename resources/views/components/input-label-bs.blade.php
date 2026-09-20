@props(['value', 'required' => false])

{{-- Bootstrap counterpart of x-input-label. --}}
<label {{ $attributes->merge(['class' => 'jb-field-label']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="jb-field-required">*</span>
    @endif
</label>
