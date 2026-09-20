<x-guest-bootstrap-layout>
    <div class="mb-4 jb-auth-subtitle">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="jb-alert jb-alert-success mb-4">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button-bs>
                {{ __('Resend Verification Email') }}
            </x-primary-button-bs>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link jb-link p-0" style="font-size:var(--text-sm)">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-bootstrap-layout>
