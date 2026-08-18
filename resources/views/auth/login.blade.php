<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">Welcome back</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sign in to continue to your dashboard</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded-lg border border-slate-200 dark:border-slate-700 text-primary-600 focus:ring-primary-500/20 w-4.5 h-4.5 cursor-pointer" name="remember">
                <span class="ml-2 text-slate-600 dark:text-slate-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="font-semibold text-primary-600 hover:text-primary-500 no-underline" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full h-11">
            {{ __('Log in') }}
        </x-primary-button>
    </form>
</x-guest-layout>
