@props(['messages'])

{{-- Bootstrap counterpart of x-input-error. --}}
@if ($messages)
    <div {{ $attributes->merge(['class' => 'jb-form-error']) }}>
        @foreach ((array) $messages as $message)
            <div>{{ $message }}</div>
        @endforeach
    </div>
@endif
