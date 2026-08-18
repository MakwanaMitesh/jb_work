<x-guest-layout>
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Forgot password?</h1>
        <p class="jb-auth-subtitle mb-0">
            {{ __('No problem. Enter your email and we will send you a password reset link.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-100 py-2">
            {{ __('Email Password Reset Link') }}
        </x-primary-button>

        <p class="text-center small jb-auth-subtitle mt-4 mb-0">
            <a class="jb-link" href="{{ route('login') }}">{{ __('Back to login') }}</a>
        </p>
    </form>
</x-guest-layout>
