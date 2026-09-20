{{-- Bootstrap counterpart of x-secondary-button. Same proportions: h-10, px-5, rounded-xl. --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary jb-btn-tall']) }}>
    {{ $slot }}
</button>
