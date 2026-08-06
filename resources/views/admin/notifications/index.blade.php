@php
    $layout = auth()->user()->isAdmin() ? 'app-layout' : 'user-layout';
    $pageTitle = 'Pusat Notifikasi';
    $pageDescription = 'Daftar seluruh notifikasi sistem';
    $unreadCount = $notifications->whereNull('read_at')->count();
@endphp

<x-dynamic-component :component="$layout">
    <x-slot name="title">Notifikasi</x-slot>

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = '{{ $pageDescription }}'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush

    <div class="notif-container">
        <div class="notif-header">
            <div class="notif-header-left">
                <h1>Notifikasi</h1>
                <span class="notif-count">{{ $notifications->total() }} Total</span>
            </div>
            <div class="notif-header-right">
                <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="notif-form-inline">
                    @csrf
                    <button type="submit" class="btn-notif-mark-all">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        </div>

        <div class="notification-tabs">
            <button class="notification-tab-btn active" data-filter="all" onclick="filterNotif('all')">
                Semua
                <span class="tab-count">{{ $notifications->total() }}</span>
            </button>
            <button class="notification-tab-btn" data-filter="unread" onclick="filterNotif('unread')">
                Belum Dibaca
                <span class="tab-count">{{ $unreadCount }}</span>
            </button>
        </div>

        <div class="notifications-list-premium">
            @forelse($notifications as $notif)
                @php
                    $isUnread = !$notif->read_at;
                    $iconSvg = match($notif->icon) {
                        'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                        'plus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>',
                        'refresh' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
                        default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>',
                    };
                @endphp
                <div class="notif-item-premium {{ $notif->type }} {{ $isUnread ? 'notif-unread fade-in-up' : '' }}" data-read="{{ $isUnread ? '0' : '1' }}" style="{{ $isUnread ? 'animation-delay: ' . ($loop->index * 0.04) . 's;' : '' }}">
                    <div class="notif-icon-box {{ $notif->type }}">
                        {!! $iconSvg !!}
                    </div>
                    <div class="notif-content-box">
                        <div class="notif-header-line">
                            <span class="notif-title-text">{{ $notif->title }}</span>
                            <span class="notif-time-badge">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="notif-message-text">{{ $notif->message }}</p>
                    </div>
                    <div class="notif-action-col">
                        @if($isUnread)
                            <a href="{{ route('admin.notifications.read', $notif) }}" class="notif-action-btn" title="Tandai Dibaca">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </a>
                        @else
                            <span class="notif-action-btn marked" title="Sudah Dibaca">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                        @endif
                        @if($notif->url)
                            <a href="{{ $notif->url }}" class="notif-action-btn link-btn" title="Lihat Detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="premium-empty-state">
                    <div class="empty-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                    </div>
                    <h3>Tidak Ada Notifikasi</h3>
                    <p>Belum ada notifikasi sistem untuk ditampilkan.</p>
                </div>
            @endforelse
        </div>

        <div class="notif-pagination">
            {{ $notifications->links() }}
        </div>
    </div>

    <script>
        function filterNotif(filter) {
            document.querySelectorAll('.notification-tab-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });
            document.querySelector('.notification-tab-btn[data-filter="' + filter + '"]').classList.add('active');

            document.querySelectorAll('.notif-item-premium').forEach(function(item) {
                if (filter === 'all') {
                    item.classList.remove('notif-hidden');
                } else if (filter === 'unread') {
                    if (item.getAttribute('data-read') === '1') {
                        item.classList.add('notif-hidden');
                    } else {
                        item.classList.remove('notif-hidden');
                    }
                }
            });
        }
    </script>
</x-dynamic-component>
