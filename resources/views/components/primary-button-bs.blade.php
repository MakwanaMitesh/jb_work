{{-- Bootstrap counterpart of x-primary-button. Same proportions: h-10, px-5, rounded-xl. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary jb-btn-tall']) }}>
    {{ $slot }}
</button>
