<section>
    <header class="mb-3">
        <h6 class="fw-semibold mb-1">{{ __('Profile Information') }}</h6>
        <p class="text-muted small mb-0">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label-bs for="name" :value="__('Name')" />
            <x-text-input-bs id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error-bs :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label-bs for="email" :value="__('Email')" />
            <x-text-input-bs id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error-bs :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 small">
                    {{ __('Your email address is unverified.') }}
                    <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success small mt-1 mb-0">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button-bs>{{ __('Save') }}</x-primary-button-bs>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">{{ __('Saved.') }}</span>
            @endif
        </div>
    </form>
</section>
