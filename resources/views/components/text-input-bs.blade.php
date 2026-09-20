@props(['disabled' => false])

{{-- Bootstrap counterpart of x-text-input. Same proportions: h-10, px-3, rounded-xl. --}}
<input @disabled($disabled) {{ $attributes->merge(['class' => 'form-control jb-input']) }}>
