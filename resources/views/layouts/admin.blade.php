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

                <div class="flex items-center gap-4 ms-auto">

                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button id="profileDropdownBtn" class="flex items-center gap-2.5 focus:outline-none py-1.5 px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition duration-150" type="button">
                            @if (auth()->user()->profilePhotoUrl())
                                <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary-500 to-orange-600 flex items-center justify-center text-white text-sm font-semibold uppercase">
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
                @php
                    $expiredInsurances = \App\Models\User::whereHas('roles', function($q) {
                            $q->where('name', 'Employee');
                        })
                        ->where('status', 'active')
                        ->whereNotNull('insurance_end_date')
                        ->where('insurance_end_date', '<', now()->toDateString())
                        ->get();
                @endphp

                @if ($expiredInsurances->isNotEmpty())
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/30 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-300">Insurance Policies Expired</h4>
                                <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">{{ $expiredInsurances->count() }} active employee(s) have expired insurance policies.</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('insuranceExpiryModal').classList.remove('hidden')" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            View Employees
                        </button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="hidden" data-toast="success" data-message="{{ session('success') }}"></div>
                @endif
                @if (session('error'))
                    <div class="hidden" data-toast="error" data-message="{{ session('error') }}"></div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <!-- Insurance Expiry Modal -->
        <div id="insuranceExpiryModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/40" onclick="document.getElementById('insuranceExpiryModal').classList.add('hidden')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Expired Insurance Policies</h3>
                        <button type="button" onclick="document.getElementById('insuranceExpiryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
                        @foreach ($expiredInsurances as $emp)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $emp->name }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Expired: {{ $emp->insurance_end_date->format('d M Y') }}</p>
                                </div>
                                <a href="{{ route('admin.employees.edit', $emp) }}" class="px-3 py-1.5 bg-primary-50 dark:bg-primary-950/20 text-primary-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/30 text-xs font-semibold rounded-lg transition no-underline">
                                    Renew / Edit
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
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

                // Global Dropdown Menu Toggles (Kebab Actions) with viewport-aware positioning
                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-kebab-btn]');
                    if (btn) {
                        e.stopPropagation();
                        const container = btn.closest('[data-kebab-container]');
                        const menu = container.querySelector('[data-kebab-menu]');
                        
                        // Hide all other menus
                        document.querySelectorAll('[data-kebab-menu]').forEach(m => {
                            if (m !== menu) {
                                m.classList.add('hidden');
                                m.style.position = '';
                                m.style.top = '';
                                m.style.left = '';
                            }
                        });
                        
                        const isHidden = menu.classList.contains('hidden');
                        if (isHidden) {
                            menu.classList.remove('hidden');
                            
                            // Calculate absolute viewport coordinates
                            const rect = btn.getBoundingClientRect();
                            const menuWidth = menu.offsetWidth || 176;
                            const menuHeight = menu.offsetHeight || 120;
                            
                            let left = rect.right - menuWidth;
                            if (left + menuWidth > window.innerWidth) {
                                left = Math.max(10, window.innerWidth - menuWidth - 10);
                            }
                            if (left < 10) {
                                left = 10;
                            }
                            
                            let top = rect.bottom + 4;
                            if (rect.bottom + menuHeight > window.innerHeight && rect.top - menuHeight > 0) {
                                top = rect.top - menuHeight - 4;
                            }
                            
                            menu.style.position = 'fixed';
                            menu.style.top = `${top}px`;
                            menu.style.left = `${left}px`;
                            menu.style.margin = '0';
                        } else {
                            menu.classList.add('hidden');
                            menu.style.position = '';
                            menu.style.top = '';
                            menu.style.left = '';
                        }
                    } else {
                        document.querySelectorAll('[data-kebab-menu]').forEach(m => {
                            m.classList.add('hidden');
                            m.style.position = '';
                            m.style.top = '';
                            m.style.left = '';
                        });
                    }
                });

                // Dismiss open kebab menus on page/table scroll
                window.addEventListener('scroll', () => {
                    document.querySelectorAll('[data-kebab-menu]').forEach(m => {
                        m.classList.add('hidden');
                        m.style.position = '';
                        m.style.top = '';
                        m.style.left = '';
                    });
                }, true);

                // Global modal close handler
                document.addEventListener('click', (e) => {
                    const closeTrigger = e.target.closest('[data-modal-close]');
                    if (closeTrigger) {
                        const modalId = closeTrigger.getAttribute('data-modal-close');
                        const modal = document.getElementById(modalId);
                        if (modal) modal.classList.add('hidden');
                    }
                });

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
