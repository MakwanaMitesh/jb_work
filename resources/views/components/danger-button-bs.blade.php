{{-- Bootstrap counterpart of x-danger-button. Shorter than the other two (px-4 py-2, rounded-lg). --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger']) }}>
    {{ $slot }}
</button>
