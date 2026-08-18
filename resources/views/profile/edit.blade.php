<x-admin-layout title="Profile">
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Profile</h1>
        <p class="text-muted small mb-0">Manage your account information and password.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="jb-card p-4 h-100">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
        <div class="col-lg-6">
            <div class="jb-card p-4 h-100">
                @include('profile.partials.update-password-form')
            </div>
        </div>
        <div class="col-12">
            <div class="jb-card p-4">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-admin-layout>
