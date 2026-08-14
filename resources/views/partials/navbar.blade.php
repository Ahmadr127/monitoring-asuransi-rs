<!-- Navbar -->
<header class="navbar">
    <div class="navbar-left">
        <!-- Sidebar Toggle (Hamburger) -->
        <button class="sidebar-toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Page Info (Title & Description) -->
        <div class="navbar-page-info">
            <h1 class="navbar-page-title" id="navbarPageTitle">
                {{ str_replace([' • Panel Admin', ' - Panel Admin'], '', $pageTitle ?? ($title ?? 'Sistem Monitoring')) }}
            </h1>
            <p class="navbar-page-desc" id="navbarPageDesc"></p>
        </div>

        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div class="navbar-actions">
        <!-- Notification Bell -->
        <div class="navbar-notif-wrapper" id="notifWrapper">
            <a href="#" class="notif-bell" id="notifBell" onclick="return toggleNotifDropdown(event)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 01-3.46 0" />
                </svg>
                <span class="notif-badge" id="notifBadge" style="display:none">0</span>
            </a>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-dropdown-header">
                    <span>Notifikasi</span>
                    <button type="button" class="notif-mark-all-btn" id="notifMarkAll" onclick="markAllRead()">Tandai Dibaca</button>
                </div>
                <div class="notif-dropdown-body" id="notifDropdownBody">
                    <div class="notif-dropdown-empty">Tidak ada notifikasi</div>
                </div>
                <div class="notif-dropdown-footer">
                    <a href="{{ route('admin.notifications.index') }}" class="notif-view-all">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="navbar-user">
            <span class="user-name">👋 Hi, {{ auth()->user()->name ?? 'User' }}</span>
        </div>

        <div class="action-divider"></div>

        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button type="submit" class="logout-btn-minimal" title="Keluar">
                <x-icon name="arrow-right" class="icon-16" />
                <span>Keluar</span>
            </button>
        </form>
    </div>

    <script>
        (function() {
            let notifData = { unread_count: 0, notifications: [] };
            let notifWrapper = null;
            let notifDropdown = null;
            let notifBadge = null;
            let notifBody = null;

            function init() {
                notifWrapper = document.getElementById('notifWrapper');
                notifDropdown = document.getElementById('notifDropdown');
                notifBadge = document.getElementById('notifBadge');
                notifBody = document.getElementById('notifDropdownBody');

                fetchNotifications();
                setInterval(fetchNotifications, 30000);
            }

            window.fetchNotifications = function() {
                fetch('{{ route("admin.notifications.dropdown") }}')
                    .then(r => r.json())
                    .then(data => {
                        notifData = data;
                        renderBadge();
                        renderDropdown();
                    })
                    .catch(() => {});
            };

            function renderBadge() {
                if (!notifBadge) return;
                if (notifData.unread_count > 0) {
                    notifBadge.style.display = 'flex';
                    notifBadge.textContent = notifData.unread_count > 99 ? '99+' : notifData.unread_count;
                } else {
                    notifBadge.style.display = 'none';
                }
            }

            function renderDropdown() {
                if (!notifBody) return;
                if (!notifData.notifications || notifData.notifications.length === 0) {
                    notifBody.innerHTML = '<div class="notif-dropdown-empty">Tidak ada notifikasi</div>';
                    return;
                }
                let html = '';
                notifData.notifications.forEach(function(n) {
                    const unreadClass = n.is_read ? '' : ' notif-unread';
                    const iconMap = { warning: 'warning', success: 'success', info: 'info' };
                    const iconType = iconMap[n.type] || 'info';
                    const iconSvg = getIconSvg(n.icon, iconType);
                    html += '<a href="' + n.read_url + '" class="notif-dropdown-item' + unreadClass + '">';
                    html += '<div class="notif-dropdown-icon ' + iconType + '">' + iconSvg + '</div>';
                    html += '<div class="notif-dropdown-content">';
                    html += '<div class="notif-dropdown-title">' + escapeHtml(n.title) + '</div>';
                    html += '<div class="notif-dropdown-msg">' + escapeHtml(n.message || '') + '</div>';
                    html += '<div class="notif-dropdown-time">' + n.time + '</div>';
                    html += '</div></a>';
                });
                notifBody.innerHTML = html;
            }

            function getIconSvg(icon, type) {
                if (icon === 'bell') {
                    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>';
                }
                if (type === 'warning') {
                    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M10.29 3.86l-8.2 14.2A1 1 0 003 20h18a1 1 0 00.86-1.49l-8.2-14.2a1 1 0 00-1.72 0z"/></svg>';
                }
                if (type === 'success') {
                    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                }
                return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            }

            function escapeHtml(text) {
                if (!text) return '';
                var d = document.createElement('div');
                d.textContent = text;
                return d.innerHTML;
            }

            window.toggleNotifDropdown = function(e) {
                e.preventDefault();
                const isOpen = notifDropdown.classList.contains('notif-dropdown-open');
                closeAllNotifDropdowns();
                if (!isOpen) {
                    notifDropdown.classList.add('notif-dropdown-open');
                    fetchNotifications();
                }
                return false;
            };

            window.closeAllNotifDropdowns = function() {
                document.querySelectorAll('.notif-dropdown').forEach(function(el) {
                    el.classList.remove('notif-dropdown-open');
                });
            };

            window.markAllRead = function() {
                fetch('{{ route("admin.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: '_token={{ csrf_token() }}'
                }).then(function() {
                    fetchNotifications();
                    closeAllNotifDropdowns();
                }).catch(function() {});
            };

            document.addEventListener('click', function(e) {
                if (notifWrapper && !notifWrapper.contains(e.target)) {
                    closeAllNotifDropdowns();
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
</header>
