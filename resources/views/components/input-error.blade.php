@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'text-red-600 dark:text-red-400 text-xs mt-1']) }}>
        @foreach ((array) $messages as $message)
            <div>{{ $message }}</div>
        @endforeach
    </div>
@endif
