<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- jQuery & DataTables.net -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

        <!-- Select2 & Select2 Bootstrap 5 Theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased text-slate-900 dark:text-slate-100">
        <!-- Sidebar Backdrop (Mobile Only) -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 z-40 hidden lg:hidden"></div>

        <!-- Sidebar (Desktop & Mobile Drawer) -->
        <aside id="sidebar" class="fixed top-0 bottom-0 left-0 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
            @include('admin.partials.sidebar')
        </aside>

        <!-- Main Content Area -->
        <div class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Topbar Header -->
            <header class="h-16 sticky top-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center px-6 gap-4 z-30 shadow-sm">
                <!-- Mobile Sidebar Toggle -->
                <button id="sidebarToggle" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500 lg:hidden" type="button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- Search Pill -->
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 border border-transparent focus-within:border-amber-500 w-full max-width-[320px] transition duration-150">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
                    <input type="search" placeholder="Search here..." class="bg-transparent border-0 outline-none text-sm w-full text-slate-900 dark:text-slate-100 placeholder-slate-400">
                </div>

                <div class="flex items-center gap-4 ms-auto">
                    <!-- Notifications -->
                    <button class="w-9 h-9 flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500" title="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button id="profileDropdownBtn" class="flex items-center gap-2.5 focus:outline-none py-1.5 px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition duration-150" type="button">
                            @if (auth()->user()->profilePhotoUrl())
                                <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-500 to-orange-600 flex items-center justify-center text-white text-sm font-semibold uppercase">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="text-left hidden md:block">
                                <span class="block text-xs font-semibold text-slate-900 dark:text-white leading-none">{{ auth()->user()->name }}</span>
                                <span class="block text-[10px] text-slate-400 leading-none mt-0.5">{{ auth()->user()->roles->first()->name ?? 'No role' }}</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400 hidden md:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-100 dark:border-slate-700 py-1 hidden z-50">
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="block text-xs text-slate-400">Signed in as</span>
                                <span class="block text-xs font-medium text-slate-900 dark:text-white truncate mt-0.5">{{ auth()->user()->email }}</span>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">Profile</a>
                            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Main Content -->
            <main class="flex-1 px-6 py-6 max-w-7xl w-full mx-auto">
                @if (session('success'))
                    <div class="hidden" data-toast="success" data-message="{{ session('success') }}"></div>
                @endif
                @if (session('error'))
                    <div class="hidden" data-toast="error" data-message="{{ session('error') }}"></div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <!-- Custom Layout Script (Sidebar & Profile dropdown toggles) -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Toast handler
                document.querySelectorAll('[data-toast]').forEach((el) => {
                    if (window.jbToast) {
                        window.jbToast(el.dataset.toast, el.dataset.message);
                    }
                });

                // Mobile Sidebar Toggles
                const sidebar = document.getElementById('sidebar');
                const backdrop = document.getElementById('sidebarBackdrop');
                const toggleBtn = document.getElementById('sidebarToggle');

                if (toggleBtn && sidebar && backdrop) {
                    const toggleSidebar = () => {
                        sidebar.classList.toggle('-translate-x-full');
                        backdrop.classList.toggle('hidden');
                    };
                    toggleBtn.addEventListener('click', toggleSidebar);
                    backdrop.addEventListener('click', toggleSidebar);
                }

                // Profile Dropdown Toggle
                const profileBtn = document.getElementById('profileDropdownBtn');
                const profileMenu = document.getElementById('profileDropdown');

                if (profileBtn && profileMenu) {
                    profileBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        profileMenu.classList.toggle('hidden');
                    });
                    document.addEventListener('click', () => {
                        profileMenu.classList.add('hidden');
                    });
                }

                // Global Select2 initializer
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
