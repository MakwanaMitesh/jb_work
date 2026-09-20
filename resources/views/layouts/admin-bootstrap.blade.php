<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Supports both the component API (:title="...") and @section('title'). --}}
        <title>@hasSection('title')@yield('title')@else{{ $title ?? config('app.name', 'Laravel') }}@endif</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Bootstrap 5.3 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

        <!-- jQuery & DataTables.net -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

        <!-- Select2 & Select2 Bootstrap 5 Theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- Theme layer mapping the existing design tokens onto Bootstrap -->
        <link rel="stylesheet" href="{{ asset('css/jb-tokens.css') }}">
        <link rel="stylesheet" href="{{ asset('css/bootstrap-theme.css') }}">

        {{-- app.js only: app.css is Tailwind and is deliberately not loaded here --}}
        @vite(['resources/js/app.js'])
    </head>
    <body>
        <div id="sidebarBackdrop" class="jb-backdrop jb-hidden jb-lg-hidden"></div>

        <aside id="sidebar" class="jb-sidebar">
            @include('admin.partials.sidebar-bootstrap')
        </aside>

        <div class="jb-content">
            <header class="jb-topbar d-flex align-items-center px-4 gap-3">
                <button id="sidebarToggle" class="jb-icon-btn border jb-lg-hidden" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-link d-flex align-items-center gap-2 p-1 px-2 rounded text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            @if (auth()->user()->profilePhotoUrl())
                                <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="" class="rounded-circle object-fit-cover" width="32" height="32">
                            @else
                                <span class="jb-brand-mark rounded-circle text-uppercase fw-semibold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="text-start d-none d-md-block">
                                <span class="jb-user-name">{{ auth()->user()->name }}</span>
                                <span class="jb-user-role">{{ auth()->user()->roles->first()->name ?? 'No role' }}</span>
                            </span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end" style="width:12rem">
                            <li class="px-3 py-2 border-bottom">
                                <span class="jb-dropdown-meta d-block">Signed in as</span>
                                <span class="jb-dropdown-email d-block text-truncate mt-1">{{ auth()->user()->email }}</span>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item dropdown-item-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="jb-main">
                @if (session('success'))
                    <div class="jb-hidden" data-toast="success" data-message="{{ session('success') }}"></div>
                @endif
                @if (session('error'))
                    <div class="jb-hidden" data-toast="error" data-message="{{ session('error') }}"></div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Toast handler (unchanged behaviour, SweetAlert2 via app.js)
                document.querySelectorAll('[data-toast]').forEach((el) => {
                    if (window.jbToast) {
                        window.jbToast(el.dataset.toast, el.dataset.message);
                    }
                });

                // Mobile sidebar
                const sidebar = document.getElementById('sidebar');
                const backdrop = document.getElementById('sidebarBackdrop');
                const toggleBtn = document.getElementById('sidebarToggle');

                if (toggleBtn && sidebar && backdrop) {
                    const toggleSidebar = () => {
                        sidebar.classList.toggle('is-open');
                        backdrop.classList.toggle('jb-hidden');
                    };
                    toggleBtn.addEventListener('click', toggleSidebar);
                    backdrop.addEventListener('click', toggleSidebar);
                }

                // Global modal close handler — same contract as the Tailwind layout.
                // x-confirmation-modal-bs is not a Bootstrap .modal, so it needs this.
                document.addEventListener('click', (e) => {
                    const closeTrigger = e.target.closest('[data-modal-close]');
                    if (closeTrigger) {
                        const modalId = closeTrigger.getAttribute('data-modal-close');
                        const modal = document.getElementById(modalId);
                        if (modal) modal.classList.add('jb-hidden');
                    }
                });

                // Select2 initializer (same contract as the Tailwind layout)
                if (window.jQuery && $.fn.select2) {
                    window.initSelect2 = function (selector) {
                        const target = selector ? $(selector) : $('select.select2').not('.dt-input');
                        target.each(function () {
                            const $el = $(this);
                            if ($el.hasClass('select2-hidden-accessible') || $el.hasClass('dt-input')) return;

                            const isInsideDropdown = $el.closest('.dropdown-menu').length > 0;
                            const dialogParent = $el.closest('dialog');
                            $el.select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                dropdownParent: dialogParent.length > 0 ? dialogParent : (isInsideDropdown ? $el.closest('.dropdown-menu') : $(document.body))
                            });
                        });
                    };

                    window.initSelect2();
                }
            });
        </script>
    </body>
</html>
