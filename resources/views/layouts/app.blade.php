<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ICM System') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- App Styles -->
    <link rel="stylesheet" href="{{ asset('css/hospital-clean.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-pages.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contract-countdown.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/master-data.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/ruang-lingkup.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/roles.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/kerjasama-list.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/ruang-lingkup-form.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/master-pages.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/delete-modal.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/searchable-select.css') }}?v={{ time() }}">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

</head>

<body>
    <script>
        // Instant Sidebar State Recovery (Pre-render)
        (function () {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (collapsed) {
                document.documentElement.classList.add('sidebar-is-collapsed');
            }
        })();
    </script>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>

    <div class="app-container">
        @include('partials.sidebar')

        <div class="main-content">
            @include('partials.navbar')

            <!-- Floating Toast Notification -->
            <div id="toast-notification" class="toast-container"></div>

            <main class="content-area">
                {{ $slot }}
            </main>
        </div>

        {{-- Inject page meta (title & description) into navbar via JS after slot renders --}}
        @stack('navbar-meta')
    </div>

    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');

            if (!sidebar || !mainContent) return;

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');

            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (sidebar && overlay) {
                sidebar.classList.toggle('mobile-active');
                overlay.classList.toggle('active');
            }
        }

        // Submenu toggle functionality
        function toggleSubmenu(event, element) {
            event.preventDefault();

            const submenu = element.nextElementSibling;
            const isOpen = submenu.classList.contains('open');

            // Toggle current submenu (allow multiple submenus to be open)
            if (isOpen) {
                submenu.classList.remove('open');
                element.classList.remove('active');
            } else {
                submenu.classList.add('open');
                element.classList.add('active');
            }

            // Save state to localStorage
            const submenuId = element.getAttribute('title');
            if (submenu.classList.contains('open')) {
                localStorage.setItem('submenu_' + submenuId, 'open');
            } else {
                localStorage.removeItem('submenu_' + submenuId);
            }
        }

        // Initialize sidebar state from localStorage
        document.addEventListener('DOMContentLoaded', function () {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            if (isCollapsed && sidebar && mainContent) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
            }

            // Mark layout as ready — reveal the page
            document.documentElement.classList.add('layout-ready');

            // Restore submenu state from localStorage
            document.querySelectorAll('.nav-link-parent').forEach(parentLink => {
                const submenuId = parentLink.getAttribute('title');
                const isOpen = localStorage.getItem('submenu_' + submenuId) === 'open';

                if (isOpen) {
                    const submenu = parentLink.nextElementSibling;
                    if (submenu && submenu.classList.contains('nav-submenu')) {
                        submenu.classList.add('open');
                        parentLink.classList.add('active');
                    }
                }
            });

            // Restore Sidebar Scroll Position
            const sidebarNav = document.querySelector('.sidebar-nav');
            if (sidebarNav) {
                const restoreScroll = () => {
                    const savedScrollTop = sessionStorage.getItem('sidebarScrollPos');
                    if (savedScrollTop) {
                        sidebarNav.scrollTop = parseInt(savedScrollTop, 10);
                    }
                };

                // Restore immediately
                restoreScroll();

                // Aggressively restore after slight delays to beat browser reflows
                setTimeout(restoreScroll, 10);
                setTimeout(restoreScroll, 50);
                setTimeout(restoreScroll, 100);

                // Save scroll position continually (debounced)
                let scrollTimeout;
                sidebarNav.addEventListener('scroll', () => {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        sessionStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
                    }, 50);
                });
            }

            // Remove no-transition after one paint so future toggles animate
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    document.documentElement.classList.remove('no-transition');
                    document.documentElement.classList.remove('sidebar-is-collapsed');
                });
            });
        });

        // Close mobile menu when clicking overlay
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('sidebar-overlay')) {
                toggleMobileMenu();
            }
        });

        // Close mobile menu when clicking a nav link (mobile only)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    const sidebar = document.querySelector('.sidebar');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (sidebar && overlay) {
                        sidebar.classList.remove('mobile-active');
                        overlay.classList.remove('active');
                    }
                }
            });
        });

        /**
         * Floating Toast Notification
         */
        function showToast(message, type) {
            const toast = document.getElementById('toast-notification');
            if (!toast) return;
            toast.textContent = message;
            toast.className = 'toast-container ' + type + ' active';
            setTimeout(() => {
                toast.classList.remove('active');
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @elseif(session('error'))
                showToast("{{ session('error') }}", 'error');
            @elseif(session('info'))
                showToast("{{ session('info') }}", 'success');
            @elseif($errors->any())
                showToast("{{ collect($errors->all())->implode(' • ') }}", 'error');
            @endif
        });

        /**
         * Real-time Day Countdown Engine
         * Updates all elements with [data-expiration] to "[X] Hari" format
         * HANYA untuk elemen yang memang harus menampilkan countdown (bukan status badge di filter all/aktif)
         */
        function updateDayCountdowns() {
            const elements = document.querySelectorAll('[data-expiration]');
            const now = new Date();

            // Normalize current date to avoid mid-day inaccuracies
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

            elements.forEach(el => {
                const expStr = el.getAttribute('data-expiration');
                if (!expStr) return;

                // SKIP jika ini adalah status badge yang seharusnya menampilkan "Aktif"/"Tidak Aktif"
                // Cek apakah badge ini sudah menampilkan "Aktif" atau "Tidak Aktif"
                const currentText = el.textContent.trim();
                if (currentText === 'Aktif' || currentText === 'Tidak Aktif') {
                    return; // Skip, jangan ubah
                }

                const expDate = new Date(expStr);
                if (isNaN(expDate.getTime())) return;

                // Normalize expiration date
                const target = new Date(expDate.getFullYear(), expDate.getMonth(), expDate.getDate());

                const diffTime = target - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                let newText = "";
                if (diffDays > 0) {
                    newText = diffDays + " Hari";
                } else if (diffDays === 0) {
                    newText = "0 Hari";
                } else {
                    newText = "Berakhir";
                }

                // Update text only if different
                if (el.textContent.trim() !== newText) {
                    el.textContent = newText;

                    // Update classes dynamically if they have status classes
                    if (el.classList.contains('status-badge') || el.classList.contains('remaining') || el.classList.contains('ks-meta-value')) {
                        if (diffDays <= 0) {
                            el.className = el.className.replace(/status-(active|warning-badge|info)/g, 'status-expired status-critical');
                        } else if (diffDays <= 30) {
                            el.className = el.className.replace(/status-(active|info)/g, 'status-critical');
                        } else if (diffDays <= 90) {
                            el.className = el.className.replace(/status-(active|info)/g, 'status-warning-badge');
                        }
                    }
                }
            });
        }

        // Initialize countdowns
        document.addEventListener('DOMContentLoaded', () => {
            updateDayCountdowns();
            setInterval(updateDayCountdowns, 60000); // Check every minute
        });
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>