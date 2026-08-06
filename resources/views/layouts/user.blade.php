@php
    $isLegal = auth()->user() && auth()->user()->role && auth()->user()->role->key === 'legal';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'ICM System') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- App Styles -->
    @if($isLegal)
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
    @else
    <link rel="stylesheet" href="{{ asset('css/hospital-clean.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-pages.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contract-countdown.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/master-data.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/kerjasama-list.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/ruang-lingkup.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/master-pages.css') }}?v={{ time() }}">
    @endif

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    @if($isLegal)
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @endif
</head>

<body>
    @if($isLegal)
    <script>
        (function () {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (collapsed) {
                document.documentElement.classList.add('sidebar-is-collapsed');
            }
        })();
    </script>
    <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>

    <div class="app-container">
        @include('partials.sidebar')

        <div class="main-content">
            @include('partials.navbar')

            <div id="toast-notification" class="toast-container"></div>

            <main class="content-area">
                {{ $slot }}
            </main>
        </div>

        @stack('navbar-meta')
    </div>
    @else
    <div class="user-layout">
        <header class="user-navbar">
            <div class="user-navbar-left">
                <a href="{{ route('user.kerjasama.index') }}" class="user-navbar-logo">
                    <img src="{{ asset('images/azra-logo.png') }}" alt="AZRA Logo">
                    <div class="user-navbar-brand">
                        <span>Monitoring</span>
                        <span>Asuransi</span>
                    </div>
                </a>

                <div class="user-navbar-divider"></div>

                <div class="user-navbar-page-info">
                    <h1 class="user-navbar-page-title" id="navbarPageTitle">
                        {{ $title ?? 'Sistem Monitoring' }}
                    </h1>
                    <p class="user-navbar-page-desc" id="navbarPageDesc"></p>
                </div>
            </div>

            <div class="user-navbar-right">
                <div class="user-navbar-user">
                    <div class="user-avatar-mini">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="user-name-text">{{ auth()->user()->name ?? auth()->user()->role->name ?? 'User' }}</span>
                </div>

                <div class="action-divider"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn-minimal" title="Keluar">
                        <x-icon name="arrow-right" class="icon-16" />
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <div id="toast-notification" class="toast-container"></div>

        <main class="user-content">
            {{ $slot }}
        </main>

        @stack('navbar-meta')
    </div>
    @endif

    <script>
        function updateDayCountdowns() {
            const elements = document.querySelectorAll('[data-expiration]');
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

            elements.forEach(el => {
                const expStr = el.getAttribute('data-expiration');
                if (!expStr) return;

                const currentText = el.textContent.trim();
                if (currentText === 'Aktif' || currentText === 'Tidak Aktif') return;

                const expDate = new Date(expStr);
                if (isNaN(expDate.getTime())) return;

                const target = new Date(expDate.getFullYear(), expDate.getMonth(), expDate.getDate());
                const diffTime = target - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                let newText = diffDays > 0 ? diffDays + ' Hari' : (diffDays === 0 ? '0 Hari' : 'Berakhir');

                if (el.textContent.trim() !== newText) {
                    el.textContent = newText;
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

        function showToast(message, type) {
            const toast = document.getElementById('toast-notification');
            if (!toast) return;
            toast.textContent = message;
            toast.className = 'toast-container ' + type + ' active';
            setTimeout(() => {
                toast.classList.remove('active');
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @elseif(session('error'))
                showToast("{{ session('error') }}", 'error');
            @elseif(session('info'))
                showToast("{{ session('info') }}", 'success');
            @endif

            updateDayCountdowns();
            setInterval(updateDayCountdowns, 60000);
        });
    </script>

    @if($isLegal)
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            if (!sidebar || !mainContent) return;
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('mobile-active');
                overlay.classList.toggle('active');
            }
        }

        function toggleSubmenu(event, element) {
            event.preventDefault();
            const submenu = element.nextElementSibling;
            const isOpen = submenu.classList.contains('open');
            if (isOpen) {
                submenu.classList.remove('open');
                element.classList.remove('active');
            } else {
                submenu.classList.add('open');
                element.classList.add('active');
            }
            const submenuId = element.getAttribute('title');
            if (submenu.classList.contains('open')) {
                localStorage.setItem('submenu_' + submenuId, 'open');
            } else {
                localStorage.removeItem('submenu_' + submenuId);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            if (isCollapsed && sidebar && mainContent) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
            }

            document.documentElement.classList.add('layout-ready');

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

            const sidebarNav = document.querySelector('.sidebar-nav');
            if (sidebarNav) {
                const restoreScroll = () => {
                    const savedScrollTop = sessionStorage.getItem('sidebarScrollPos');
                    if (savedScrollTop) {
                        sidebarNav.scrollTop = parseInt(savedScrollTop, 10);
                    }
                };
                restoreScroll();
                setTimeout(restoreScroll, 10);
                setTimeout(restoreScroll, 50);
                setTimeout(restoreScroll, 100);

                let scrollTimeout;
                sidebarNav.addEventListener('scroll', () => {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        sessionStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
                    }, 50);
                });
            }

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    document.documentElement.classList.remove('no-transition');
                    document.documentElement.classList.remove('sidebar-is-collapsed');
                });
            });
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('sidebar-overlay')) {
                toggleMobileMenu();
            }
        });

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
    </script>
    @endif
</body>
</html>