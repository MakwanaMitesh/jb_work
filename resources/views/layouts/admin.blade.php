<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- jQuery & DataTables.net Bootstrap 5 -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>

        <!-- Select2 & Select2 Bootstrap 5 Theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div class="jb-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="jbSidebar">
            @include('admin.partials.sidebar')
        </div>

        <div class="jb-content-wrap @if ($whiteBg) jb-content-wrap--white @endif">
            <header class="jb-topbar">
                <button class="jb-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#jbSidebar" aria-controls="jbSidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>

                <div class="jb-search-pill d-none d-sm-flex">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" placeholder="Search here...">
                </div>

                <div class="d-flex align-items-center ms-auto gap-2">
                    <button type="button" class="jb-icon-btn" title="Notifications">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                    </button>

                    <div class="dropdown">
                        <button class="btn d-flex align-items-center gap-2 border-0 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if (auth()->user()->profilePhotoUrl())
                                <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="" class="jb-avatar-circle" style="object-fit: cover;">
                            @else
                                <span class="jb-avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                            <span class="text-start d-none d-md-block">
                                <span class="d-block small fw-semibold lh-1">{{ auth()->user()->name }}</span>
                                <span class="d-block text-muted lh-1" style="font-size:.72rem;">{{ auth()->user()->roles->first()->name ?? 'No role' }}</span>
                            </span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted d-none d-md-block"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">{{ auth()->user()->email }}</h6></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="container-fluid px-4 py-4">
                @if (session('success'))
                    <div class="d-none" data-toast="success" data-message="{{ session('success') }}"></div>
                @endif
                @if (session('error'))
                    <div class="d-none" data-toast="error" data-message="{{ session('error') }}"></div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-toast]').forEach((el) => {
                    window.jbToast(el.dataset.toast, el.dataset.message);
                });

                if (window.jQuery && $.fn.select2) {
                    window.initSelect2 = function(selector) {
                        const target = selector ? $(selector) : $('select.form-select, select.select2').not('.dt-input');
                        target.each(function() {
                            const $el = $(this);
                            if ($el.hasClass('select2-hidden-accessible') || $el.hasClass('dt-input')) return;

                            const isInsideDropdown = $el.closest('.dropdown-menu').length > 0;
                            $el.select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                dropdownParent: isInsideDropdown ? $el.closest('.dropdown-menu') : $(document.body)
                            });
                        });
                    };

                    window.initSelect2();
                }
            });
        </script>
    </body>
</html>
