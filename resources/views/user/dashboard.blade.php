<x-user-layout>
    <x-slot name="title">Monitoring Dashboard</x-slot>

    @php
        $pageTitle = 'Monitoring Dashboard';
        $pageDescription = 'Pantauan real-time status kontrak dan kerjasama asuransi';
    @endphp

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = '{{ $pageDescription }}'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush

    <div class="monitor-container">
        <section class="stats-monitoring-grid">
            <div class="stat-monitor-card success" onclick="filterKerjasama('aktif')">
                <div class="card-inner">
                    <div class="card-label">KONTRAK AKTIF</div>
                    <div class="card-main">
                        <span class="card-value">{{ $kerjasamaAktif->count() }}</span>
                        <span class="card-unit">Rekanan</span>
                    </div>
                    <div class="card-status-bar">
                        <div class="bar-fill bar-fill-full"></div>
                    </div>
                    <div class="card-footer">
                        <x-icon name="check-circle" class="icon-sm" />
                        <span>Status Operasional</span>
                    </div>
                </div>
            </div>

            <div class="stat-monitor-card warning" onclick="filterKerjasama('expiring90')">
                <div class="card-inner">
                    <div class="card-label">SEGERA BERAKHIR</div>
                    <div class="card-main">
                        <span class="card-value">{{ $expiringContracts90->count() }}</span>
                        <span class="card-unit">Kontrak</span>
                    </div>
                    <div class="card-status-bar">
                        <div class="bar-fill bar-fill-full"></div>
                    </div>
                    <div class="card-footer">
                        <x-icon name="exclamation-circle" class="icon-sm" />
                        <span>Perlu Tindakan Segera</span>
                    </div>
                </div>
            </div>

            <div class="stat-monitor-card danger" onclick="filterKerjasama('tidak_aktif')">
                <div class="card-inner">
                    <div class="card-label">KONTRAK BERAKHIR</div>
                    <div class="card-main">
                        <span class="card-value">{{ $kerjasamaTidakAktif->count() }}</span>
                        <span class="card-unit">Data</span>
                    </div>
                    <div class="card-status-bar">
                        <div class="bar-fill bar-fill-full"></div>
                    </div>
                    <div class="card-footer">
                        <x-icon name="close-solid" class="icon-sm" />
                        <span>Arsip / Non-Aktif</span>
                    </div>
                </div>
            </div>
        </section>

        <main class="monitoring-board">
            <div class="board-main">
                <div class="board-header">
                    <div class="header-left">
                        <h2 id="ksPanelTitle">Data Monitoring Kontrak</h2>
                        <span class="live-indicator" id="ksBadge">0 Kontrak Terpantau</span>
                    </div>
                    <div class="header-right">
                        <div class="monitoring-filters d-none" id="expiringFilterGroup">
                            <button class="filter-pill" id="filterBtn30" onclick="setExpiringFilter(30)">≤ 30 Hari</button>
                            <button class="filter-pill active" id="filterBtn90" onclick="setExpiringFilter(90)">≤ 90 Hari</button>
                        </div>

                        <div class="view-toggles">
                            <button class="v-pill active" id="viewList" onclick="setView('list')" title="Tampilan Tabel">
                                <x-icon name="list" class="icon-sm" />
                            </button>
                            <button class="v-pill" id="viewCard" onclick="setView('card')" title="Tampilan Kartu">
                                <x-icon name="grid" class="icon-sm" />
                            </button>
                        </div>

                        <a href="{{ route('user.kerjasama.index') }}" class="btn-manage">
                            <span>Lihat Semua Data</span>
                            <x-icon name="arrow-right" class="icon-sm" />
                        </a>
                    </div>
                </div>

                <div class="board-body" id="ksPanelBody">
                </div>
            </div>

            <aside class="board-sidebar">
                <div class="sidebar-block urgent-block">
                    <div class="block-header">
                        <div class="header-title">
                            <x-icon name="exclamation-circle" class="icon-sidebar" />
                            <h3>Tindakan Mendesak</h3>
                        </div>
                        <span class="urgent-count">{{ $expiringContracts30->count() }}</span>
                    </div>
                    <div class="urgent-list">
                        @forelse($expiringContracts30->take(5) as $urgent)
                            <a href="{{ route('user.kerjasama.show', $urgent) }}" class="urgent-item no-underline">
                                <div class="urgent-info">
                                    <strong>{{ $urgent->nama_asuransi }}</strong>
                                    <span>Berakhir dalam {{ floor($urgent->days_remaining) }} hari</span>
                                </div>
                                <div class="urgent-action">
                                    <x-icon name="chevron-right" class="icon-sm" />
                                </div>
                            </a>
                        @empty
                            <div class="empty-state-sidebar">
                                <p>Tidak ada kontrak kritis</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="sidebar-block activity-block">
                    <div class="block-header">
                        <div class="header-title">
                            <x-icon name="clock" class="icon-sidebar" />
                            <h3>Aktivitas Sistem</h3>
                        </div>
                    </div>
                    <div class="activity-timeline">
                        @php
                            $notifications = collect();
                            foreach ($expiringContracts90 as $contract) {
                                $notifications->push([
                                    'type' => 'warning',
                                    'title' => 'Kontrak Akan Berakhir',
                                    'message' => $contract->nama_asuransi.' akan berakhir dalam '.floor($contract->days_remaining).' hari',
                                    'time' => $contract->tanggal_berakhir->diffForHumans(),
                                    'icon' => 'clock',
                                ]);
                            }
                            foreach ($allKerjasama->where('created_at', '>=', now()->subDays(7))->sortByDesc('created_at') as $contract) {
                                $notifications->push([
                                    'type' => 'success',
                                    'title' => 'Kontrak Baru Ditambahkan',
                                    'message' => $contract->nama_asuransi.' telah ditambahkan',
                                    'time' => $contract->created_at->diffForHumans(),
                                    'icon' => 'plus',
                                ]);
                            }
                        @endphp
                        @foreach($notifications->take(5) as $notif)
                            <div class="timeline-item">
                                <div class="timeline-icon {{ $notif['type'] }}">
                                    @if($notif['icon'] == 'clock')
                                        <x-icon name="clock" class="icon-sm" />
                                    @elseif($notif['icon'] == 'plus')
                                        <x-icon name="plus" class="icon-sm" />
                                    @else
                                        <x-icon name="refresh" class="icon-sm" />
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <strong>{{ $notif['title'] }}</strong>
                                    <p>{{ $notif['message'] }}</p>
                                    <span class="time">{{ $notif['time'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </main>
    </div>

    <div id="detailModal" class="monitor-modal-overlay">
        <div class="monitor-modal-box">
            <header class="modal-header-premium">
                <div class="header-main">
                    <h2 id="detail_nama_asuransi">Nama Asuransi</h2>
                    <span id="detail_status" class="monitor-status-badge"></span>
                </div>
                <button onclick="closeDetailModal()" class="btn-dismiss" title="Tutup">
                    <x-icon name="close" class="icon-sm" />
                </button>
            </header>

            <div class="modal-body-premium">
                <div class="info-summary-grid">
                    <div class="summary-card">
                        <label>Masa Berlaku</label>
                        <div class="val" id="detail_periode"></div>
                    </div>
                    <div class="summary-card">
                        <label>Diskon Rekanan</label>
                        <div class="val highlight" id="detail_diskon"></div>
                    </div>
                    <div class="summary-card">
                        <label>Jangka Waktu</label>
                        <div class="val" id="detail_jangka_waktu"></div>
                    </div>
                    <div class="summary-card">
                        <label>Badan Usaha</label>
                        <div class="val" id="detail_badan_usaha"></div>
                    </div>
                </div>

                <div class="info-content-layout">
                    <div class="content-left">
                        <div class="data-group">
                            <h4>
                                <x-icon name="building" class="icon-sm" />
                                Jenis Layanan
                            </h4>
                            <div id="detail_jenis_layanan" class="pill-group"></div>
                        </div>
                        <div class="data-group">
                            <h4>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Deskripsi
                            </h4>
                            <div id="detail_deskripsi" class="text-xs color-slate-600"></div>
                        </div>
                        <div class="data-group">
                            <h4>
                                <x-icon name="check-circle" class="icon-sm" />
                                Benefit
                            </h4>
                            <ul id="detail_benefit" class="monitor-list"></ul>
                        </div>
                    </div>
                    <div class="content-right">
                        <div class="data-group">
                            <h4>
                                <x-icon name="user-group" class="icon-sm" />
                                PIC Asuransi
                            </h4>
                            <div class="detail-list">
                                <div class="detail-item">
                                    <span class="detail-label">Nama</span>
                                    <span class="detail-value" id="detail_pic_nama">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Kontak</span>
                                    <span class="detail-value" id="detail_pic_kontak">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value" id="detail_pic_email">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Portal</span>
                                    <span class="detail-value" id="detail_pic_portal">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="data-group">
                            <h4>
                                <x-icon name="building" class="icon-sm" />
                                Alamat Penagihan
                            </h4>
                            <div class="text-xs color-slate-600" id="detail_alamat_penagihan">-</div>
                        </div>
                        <div class="data-group">
                            <h4>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                                Sistem Tera
                            </h4>
                            <div class="alias-box">
                                <label>Alias Versi Tera</label>
                                <span id="detail_versi_tera">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="modal-footer-premium">
                <button onclick="closeDetailModal()" class="btn-close-premium">Tutup Monitoring</button>
            </footer>
        </div>
    </div>

    <script>
        const visibleColumns = @json($visibleColumns ?? []);

        const dashboardData = {
            all: @json($allKerjasama ?? []),
            aktif: @json($kerjasamaAktif ?? []),
            expiring30: @json($expiringContracts30 ?? []),
            expiring90: @json($expiringContracts90 ?? []),
            tidak_aktif: @json($kerjasamaTidakAktif ?? [])
        };

        const config = {
            all: { title: 'Semua Monitoring Kontrak', variant: 'all' },
            aktif: { title: 'Monitoring Kontrak Aktif', variant: 'success' },
            expiring30: { title: 'Peringatan Kontrak (30 Hari)', variant: 'warning' },
            expiring90: { title: 'Peringatan Kontrak (90 Hari)', variant: 'warning' },
            tidak_aktif: { title: 'Arsip Kontrak Berakhir', variant: 'danger' }
        };

        let currentFilter = 'all';
        let currentView = 'list';
        let currentPage = 1;
        const itemsPerPage = 10;

        document.addEventListener('DOMContentLoaded', function () {
            filterKerjasama('all');
        });

        function filterKerjasama(type) {
            currentFilter = type;
            currentPage = 1;
            const cfg = config[type] || config['all'];
            document.getElementById('ksPanelTitle').textContent = cfg.title;
            const data = dashboardData[type] || dashboardData['all'];
            document.getElementById('ksBadge').textContent = data.length + ' Kontrak Terpantau';
            document.getElementById('expiringFilterGroup').style.display = type.includes('expiring') ? 'flex' : 'none';
            render();
        }

        function setExpiringFilter(days) {
            document.getElementById('filterBtn30').classList.toggle('active', days === 30);
            document.getElementById('filterBtn90').classList.toggle('active', days === 90);
            filterKerjasama('expiring' + days);
        }

        function setView(view) {
            currentView = view;
            currentPage = 1;
            document.getElementById('viewList').classList.toggle('active', view === 'list');
            document.getElementById('viewCard').classList.toggle('active', view === 'card');
            render();
        }

        function render() {
            const container = document.getElementById('ksPanelBody');
            const data = dashboardData[currentFilter] || [];

            if (!data || data.length === 0) {
                container.innerHTML = `<div class="empty-state-main">
                    <svg class="empty-state-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="empty-state-title">Belum ada kontrak yang terdaftar</p>
                    <p class="empty-state-desc">Tambahkan data kontrak baru untuk mulai memantau status kerjasama.</p>
                </div>`;
                return;
            }

            const totalItems = data.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
            const paginatedData = data.slice(startIndex, endIndex);

            if (currentView === 'list') renderList(paginatedData, container, startIndex);
            else renderCards(paginatedData, container);

            renderPagination(totalPages, totalItems, startIndex, endIndex);
        }

        function renderList(data, container, startIndex) {
            let html = `
                <div class="table-wrap">
                    <table class="monitor-table">
                        <thead>
                            <tr>
                                <th class="th-no">NO</th>
                                <th>NAMA ASURANSI</th>
                                <th>PIC ASURANSI</th>
                                <th>ALAMAT PENAGIHAN</th>
                                <th>DESKRIPSI</th>
                                ${visibleColumns.includes('periode') ? '<th>PERIODE KONTRAK</th>' : ''}
                                ${visibleColumns.includes('jangka_waktu') ? '<th class="th-jangka">JANGKA WAKTU</th>' : ''}
                                ${visibleColumns.includes('status') ? '<th class="th-status">STATUS</th>' : ''}
                                <th class="th-aksi">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.forEach((item, index) => {
                const days = Math.floor(item.days_remaining ?? 0);
                const statusLabel = item.status === 'aktif' ? 'Aktif' : 'Arsip';
                const sisaLabel = item.status === 'aktif'
                    ? (days <= 30 ? `Tersisa: ${days} Hari` : days <= 90 ? `Tersisa: ${days} Hari` : `Tersisa: ${days} Hari`)
                    : 'Berakhir';
                const sisaClass = item.status === 'aktif'
                    ? (days <= 30 ? 'status-critical' : days <= 90 ? 'status-warning-badge' : 'status-active')
                    : 'status-neutral';
                const rowClass = item.status !== 'aktif' ? ' class="tr-inactive"' : '';

                const penagihanList = item.penagihan_pembayaran || [];
                const jwLabel = penagihanList.length > 0
                    ? penagihanList.map(p => `${p.jangka_waktu_hari || p.jangka_waktu || '-'} ${p.tipe_hari || 'Hari'}`).join(', ')
                    : '-';

                html += `
                    <tr${rowClass}>
                        <td class="td-no">${startIndex + index + 1}</td>
                        <td>
                            <div class="entitas-cell">
                                <span class="entitas-name">${item.nama_asuransi}</span>
                                ${visibleColumns.includes('alias_versi_tera') && item.alias_versi_tera ? `` : ''}
                            </div>
                        </td>
                        <td>
                            <div class="entitas-cell">
                                <span class="entitas-name">${item.pic_nama || '-'}</span>
                                <span class="text-xs-muted">${item.pic_kontak || ''}</span>
                            </div>
                        </td>
                        <td><span class="text-xs-muted">${item.alamat_penagihan || '-'}</span></td>
                        <td><span class="text-xs-muted">${(Array.isArray(item.deskripsi_list) && item.deskripsi_list.length ? item.deskripsi_list.join(' | ') : (item.deskripsi || '-')).toString().substring(0, 100)}</span></td>
                        ${visibleColumns.includes('periode') ? `
                        <td>
                            <div class="period-info">
                                <div class="period-dates">
                                    <div class="start-date">${formatDate(item.tanggal_mulai)}</div>
                                    <div class="date-separator">—</div>
                                    <div class="end-date">${formatDate(item.tanggal_berakhir)}</div>
                                </div>
                                <div class="period-duration">
                                    <span class="remaining status-badge ${sisaClass} font-bold-700 text-xs-medium">
                                        ${sisaLabel}
                                    </span>
                                </div>
                            </div>
                        </td>` : ''}
                        ${visibleColumns.includes('jangka_waktu') ? `
                        <td class="td-jangka">
                            ${jwLabel}
                        </td>` : ''}
                        ${visibleColumns.includes('status') ? `
                        <td>
                            <span class="monitor-status-badge ${getBadgeClass(item)}">${statusLabel}</span>
                        </td>` : ''}
                        <td class="td-aksi">
                            <span class="aksi-btns">
                                <a href="/user/kerjasama/detail/${item.id}" class="btn-view-detail" title="Lihat Detail">
                                    <x-icon name="eye" class="icon-btn" />
                                </a>
                                ${item.pic_portal ? `<a href="${item.pic_portal}" target="_blank" rel="noopener" class="btn-view-detail" title="Buka Portal Asuransi">
                                    <x-icon name="link" class="icon-btn" />
                                </a>` : ''}
                            </span>
                        </td>
                    </tr>
                `;
            });

            html += `</tbody></table></div>`;
            container.innerHTML = html;
        }

        function renderCards(data, container) {
            let html = '<div class="mon-card-grid">';

            data.forEach(item => {
                const days = Math.floor(item.days_remaining ?? 0);
                const statusLabel = item.status === 'aktif' ? 'Aktif' : 'Arsip';
                const sisaLabel = item.status === 'aktif' ? `Tersisa: ${days} Hari` : 'Berakhir';
                const sisaClass = item.status === 'aktif'
                    ? (days <= 30 ? 'status-critical' : days <= 90 ? 'status-warning-badge' : 'status-active')
                    : 'status-neutral';
                const cardClass = item.status !== 'aktif' ? ' mon-card-inactive' : '';

                const penagihanList = item.penagihan_pembayaran || [];
                const jwLabel = penagihanList.length > 0
                    ? penagihanList.map(p => `${p.jangka_waktu_hari || p.jangka_waktu || '-'} ${p.tipe_hari || 'Hari'}`).join(', ')
                    : '-';

                html += `
                    <div class="mon-card${cardClass}">
                        <div class="mon-card-head">
                            <div class="mon-card-title">${item.nama_asuransi}</div>
                            <span class="monitor-status-badge ${getBadgeClass(item)}">${statusLabel}</span>
                        </div>
                        <div class="mon-card-body">
                            ${visibleColumns.includes('periode') ? `
                            <div class="mon-card-row">
                                <label>Masa Berlaku:</label>
                                <div class="period-info period-col">
                                    <span class="text-xs">${formatDate(item.tanggal_mulai)} — ${formatDate(item.tanggal_berakhir)}</span>
                                    <span class="remaining status-badge ${sisaClass} font-bold-700 text-xs-medium w-fit">${sisaLabel}</span>
                                </div>
                            </div>` : ''}
                            ${visibleColumns.includes('jangka_waktu') ? `
                            <div class="mon-card-row">
                                <label>Jangka Waktu Bayar:</label>
                                <span class="fw-700">${jwLabel}</span>
                            </div>` : ''}
                            ${visibleColumns.includes('diskon') ? `
                            <div class="mon-card-row">
                                <label>Diskon:</label>
                                <span>${item.diskon_persen ? item.diskon_persen + '%' : '-'}</span>
                            </div>` : ''}
                            <div class="mon-card-row">
                                <label>PIC:</label>
                                <span>${item.pic_nama ? `${item.pic_nama}${item.pic_kontak ? ' (' + item.pic_kontak + ')' : ''}` : '-'}</span>
                            </div>
                            <div class="mon-card-row">
                                <label>Deskripsi:</label>
                                <span>${(Array.isArray(item.deskripsi_list) && item.deskripsi_list.length ? item.deskripsi_list[0] : (item.deskripsi || '-')).toString().substring(0, 80)}</span>
                            </div>
                            <div class="mon-card-row">
                                <label>Alamat Tagih:</label>
                                <span>${(item.alamat_penagihan || '-').toString().substring(0, 80)}</span>
                            </div>
                        </div>
                        <a href="/user/kerjasama/detail/${item.id}" class="btn-mon-full no-underline flex-center">BUKA DETAIL</a>
                    </div>
                `;
            });

            html += '</div>';
            container.innerHTML = html;
        }

        function renderPagination(totalPages, totalItems, startIndex, endIndex) {
            const panel = document.getElementById('ksPanelBody');
            let paginationEl = document.getElementById('ksPagination');
            if (!paginationEl) {
                paginationEl = document.createElement('div');
                paginationEl.id = 'ksPagination';
                paginationEl.className = 'mon-pagination';
                panel.appendChild(paginationEl);
            }
            if (totalPages <= 1) { paginationEl.style.display = 'none'; return; }
            paginationEl.style.display = 'flex';
            let html = `<div class="mon-pg-info">MENAMPILKAN ${startIndex + 1}-${endIndex} DARI ${totalItems} DATA</div><div class="mon-pg-btns">`;
            html += `<button onclick="changePage(${currentPage - 1})" class="mon-pg-btn" ${currentPage === 1 ? 'disabled' : ''}>&larr;</button>`;
            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 5 && Math.abs(i - currentPage) > 2) continue;
                html += `<button onclick="changePage(${i})" class="mon-pg-btn ${i === currentPage ? 'active' : ''}">${i}</button>`;
            }
            html += `<button onclick="changePage(${currentPage + 1})" class="mon-pg-btn" ${currentPage === totalPages ? 'disabled' : ''}>&rarr;</button></div>`;
            paginationEl.innerHTML = html;
        }

        function changePage(page) { currentPage = page; render(); }

        function formatDate(str) {
            if (!str) return '-';
            return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function getBadgeClass(item) {
            if (item.status !== 'aktif') return 'status-expired';
            const d = Math.floor(item.days_remaining ?? 999);
            if (d <= 30) return 'status-critical-badge';
            if (d <= 90) return 'status-warning-badge-solid';
            return 'status-active';
        }

        function openDetailModal(item) {
            document.getElementById('detail_nama_asuransi').textContent = item.nama_asuransi;
            document.getElementById('detail_periode').textContent = `${formatDate(item.tanggal_mulai)} — ${formatDate(item.tanggal_berakhir)}`;
            const st = document.getElementById('detail_status');
            st.textContent = item.status === 'aktif' ? 'KONTRAK AKTIF' : 'KONTRAK BERAKHIR';
            st.className = 'monitor-status-badge ' + getBadgeClass(item);
            document.getElementById('detail_diskon').textContent = item.diskon_persen ? item.diskon_persen + '%' : '-';
            const penagihanList = item.penagihan_pembayaran || [];
            const jwLabel = penagihanList.length > 0
                ? penagihanList.map(p => `${p.jangka_waktu_hari || p.jangka_waktu || '-'} ${p.tipe_hari || 'Hari'}`).join(', ')
                : '-';
            document.getElementById('detail_jangka_waktu').textContent = jwLabel;
            document.getElementById('detail_badan_usaha').textContent = item.badan_usaha ? item.badan_usaha.nama_bu : '-';
            document.getElementById('detail_versi_tera').textContent = item.alias_versi_tera || '-';
            const lys = Array.isArray(item.jenis_layanan) ? item.jenis_layanan : [];
            document.getElementById('detail_jenis_layanan').innerHTML = lys.map(l => `<span class="tag-pill">${l}</span>`).join('') || '-';
            const dsc = Array.isArray(item.deskripsi_list) ? item.deskripsi_list : (item.deskripsi ? [item.deskripsi] : []);
            document.getElementById('detail_deskripsi').textContent = dsc.filter(Boolean).join(' | ') || '-';
            document.getElementById('detail_pic_nama').textContent = item.pic_nama || '-';
            document.getElementById('detail_pic_kontak').textContent = item.pic_kontak || '-';
            document.getElementById('detail_pic_email').textContent = item.pic_email || '-';
            const portalEl = document.getElementById('detail_pic_portal');
            if (item.pic_portal) {
                portalEl.innerHTML = `<a href="${item.pic_portal}" target="_blank" rel="noopener">${item.pic_portal}</a>`;
            } else {
                portalEl.textContent = '-';
            }
            document.getElementById('detail_alamat_penagihan').textContent = item.alamat_penagihan || '-';
            let bnf = [];
            try { bnf = typeof item.benefit === 'string' ? JSON.parse(item.benefit) : (Array.isArray(item.benefit) ? item.benefit : [item.benefit]); } catch (e) { bnf = Array.isArray(item.benefit) ? item.benefit : [item.benefit]; }
            document.getElementById('detail_benefit').innerHTML = (bnf || []).filter(b => b).map(b => `<li>${b}</li>`).join('') || '-';
            document.getElementById('detailModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    </script>
</x-user-layout>