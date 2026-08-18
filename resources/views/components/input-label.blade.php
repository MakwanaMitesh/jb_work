@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-red-600 dark:text-red-500 font-semibold">*</span>
    @endif
</label>
