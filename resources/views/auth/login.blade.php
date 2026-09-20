<x-guest-bootstrap-layout>
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Welcome back</h1>
        <p class="jb-auth-subtitle mb-0">Sign in to continue to your dashboard</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label-bs for="email" :value="__('Email')" />
            <x-text-input-bs id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error-bs :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label-bs for="password" :value="__('Password')" />
            <x-text-input-bs id="password"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error-bs :messages="$errors->get('password')" />
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4" style="font-size:var(--text-sm)">
            <label for="remember_me" class="d-inline-flex align-items-center">
                <input id="remember_me" type="checkbox" class="form-check-input me-2" name="remember">
                <span style="color:var(--slate-600)">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="jb-link" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-primary-button-bs class="w-100">
            {{ __('Log in') }}
        </x-primary-button-bs>
    </form>
</x-guest-bootstrap-layout>
