<x-guest-layout>
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Confirm password</h1>
        <p class="jb-auth-subtitle mb-0">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-100 py-2">
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
