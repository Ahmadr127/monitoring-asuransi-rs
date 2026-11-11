<x-app-layout>
    <x-slot name="title">{{ $title }}</x-slot>

    @php $pageTitle = $title; @endphp

    <div class="detail-page-header">
        <div class="detail-header-content">
            <a href="{{ route('admin.dashboard') }}" class="back-button">
                <x-icon name="chevron-left" class="icon-sm" />
                Kembali ke Dashboard
            </a>
            <div class="detail-header-title">
                <div class="detail-icon {{ $color }}">
                    @if($icon === 'document')
                        <x-icon name="document" class="icon-lg" />
                    @elseif($icon === 'check-circle')
                        <x-icon name="check-circle" class="icon-lg" />
                    @elseif($icon === 'clock')
                        <x-icon name="clock" class="icon-lg" />
                    @elseif($icon === 'x')
                        <x-icon name="close" class="icon-lg" />
                    @endif
                </div>
                <div>
                    <h1>{{ $title }}</h1>
                    <p>Total: {{ $kerjasama->count() }} Kontrak</p>
                </div>
            </div>
        </div>
    </div>

    <div class="detail-page-content">
        @if($kerjasama->count() > 0)
            <div class="kerjasama-grid">
                @foreach($kerjasama as $item)
                    <div class="kerjasama-detail-card {{ $type === 'expiring' && $item->days_remaining <= 15 ? 'critical' : '' }}">
                        <div class="kerjasama-card-header">
                            <div class="card-header-inner">
                                <h3>{{ $item->nama_asuransi }}</h3>
                                @if($item->is_high_profile)
                                    <span class="status-badge status-expired badge-high">High</span>
                                @endif
                            </div>
                            @if(in_array('status', $visibleColumns))
                            <span class="status-badge {{ $item->status === 'aktif' ? 'status-active' : 'status-expired' }}">
                                {{ $item->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                            @endif
                        </div>

                        <div class="kerjasama-card-body">
                            @if(in_array('periode', $visibleColumns))
                            <div class="info-row">
                                <div class="info-icon">
                                    <x-icon name="calendar" class="icon-sm" />
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Periode Kerjasama</span>
                                    <span class="info-value">
                                        @if($item->tanggal_mulai && $item->tanggal_berakhir)
                                            {{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_berakhir->format('d M Y') }}
                                        @else
                                            Belum ada periode
                                        @endif
                                    </span>
                                </div>
                            </div>
                            @endif

                            @if($type === 'expiring' && isset($item->days_remaining))
                                <div class="info-row highlight">
                                    <div class="info-icon warning">
                                        <x-icon name="clock" class="icon-sm" />
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Sisa Waktu</span>
                                        <span class="info-value warning-text">{{ floor($item->days_remaining) }} Hari Lagi</span>
                                    </div>
                                </div>
                            @endif

                            @if(in_array('diskon', $visibleColumns) && $item->diskon && $item->diskon->count() > 0)
                                <div class="info-row">
                                    <div class="info-icon">
                                        <x-icon name="tag" class="icon-sm" />
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Diskon</span>
                                        <span class="info-value">{{ number_format($item->diskon->first()->persentase_diskon, 0) }}%</span>
                                    </div>
                                </div>
                            @endif

                            @if(in_array('jenis_layanan', $visibleColumns) && $item->jenis_layanan && count($item->jenis_layanan) > 0)
                                <div class="info-row">
                                    <div class="info-content full-width">
                                        <span class="info-label">Jenis Layanan</span>
                                        <div class="badge-list">
                                            @foreach($item->jenis_layanan as $layanan)
                                                <span class="status-badge badge-layanan-sync">{{ $layanan }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="kerjasama-card-footer">
                            <a href="{{ route('admin.ruang-lingkup.show', $item) }}" class="btn-detail no-underline btn-detail-flex">
                                <x-icon name="eye" class="icon-sm" />
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3>Tidak Ada Data</h3>
                <p>Tidak ada data kerjasama untuk kategori ini</p>
            </div>
        @endif
    </div>

    <!-- Premium Detail Modal -->
    <div id="detailModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon primary">
                        <x-icon name="document" class="icon-lg" />
                    </div>
                    <div class="modal-title">
                        <h2 id="detail_nama_asuransi">Detail Kerjasama</h2>
                        <p id="detail_periode_top">Informasi lengkap kontrak asuransi</p>
                    </div>
                </div>
                <button onclick="closeDetailModal()" class="modal-close">
                    <x-icon name="close-solid" class="icon-sm" />
                </button>
            </div>
            
            <div class="modal-body">
                <!-- Status & Periode Summary -->
                <div class="detail-summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Status Kontrak</div>
                        <div id="detail_status_badge" class="status-badge"></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Periode Berlaku</div>
                        <div class="summary-value" id="detail_periode"></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Sisa Waktu</div>
                        <div class="summary-value highlight" id="detail_sisa_waktu">-</div>
                    </div>
                </div>

                <div class="detail-main-grid">
                    <!-- Left Column -->
                    <div class="detail-column">
                        <div class="content-section content-section-mt">
                            <h4 class="section-subtitle">
                                <x-icon name="check-circle" class="section-subtitle-icon" />
                                Benefit Utama
                            </h4>
                            <ul id="detail_benefit" class="feature-list"></ul>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="detail-column">
                        <div class="content-section">
                            <h4 class="section-subtitle">
                                <x-icon name="information-circle" class="section-subtitle-icon" />
                                Cakupan & Ketentuan
                            </h4>
                            <ul id="detail_cakupan" class="feature-list"></ul>
                        </div>

                        <div class="content-section content-section-mt">
                            <h4 class="section-subtitle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="section-subtitle-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"></path></svg>
                                Komersial
                            </h4>
                            <div class="commercial-info-card">
                                <div class="commercial-grid">
                                    <div>
                                        <p class="commercial-label">Persentase Diskon</p>
                                        <div id="detail_diskon_container">
                                            <span id="detail_diskon" class="badge-diskon-sync">-</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="commercial-label">Jangka Waktu Bayar</p>
                                        <span id="detail_jangka_waktu" class="status-badge badge-info-sync">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="content-section content-section-mt">
                            <h4 class="section-subtitle">
                                <x-icon name="filter" class="section-subtitle-icon" />
                                Daftar Obat
                            </h4>
                            <div id="detail_obat" class="badge-list"></div>
                        </div>
                    </div>
                </div>

                <div id="detail_deskripsi_section">
                    <h4 class="section-subtitle">Catatan Tambahan</h4>
                    <p id="detail_deskripsi"></p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button onclick="closeDetailModal()" class="btn-secondary">Tutup Detail Kontrak</button>
            </div>
        </div>
    </div>

    <script>
        function openDetailModal(item) {
            // Parse JSON strings if needed
            const parseJsonField = (field) => {
                if (Array.isArray(field)) return field;
                if (typeof field === 'string') {
                    try {
                        return JSON.parse(field);
                    } catch (e) {
                        console.error('Failed to parse JSON field:', e);
                        return [];
                    }
                }
                return [];
            };
            
            const namaContainer = document.getElementById('detail_nama_asuransi');
            namaContainer.innerHTML = item.nama_asuransi;
            if (item.is_high_profile) {
                namaContainer.innerHTML += ' <span class="status-badge status-expired badge-high-inline">High</span>';
            }
            const startDate = item.tanggal_mulai ? new Date(item.tanggal_mulai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
            const endDate = item.tanggal_berakhir ? new Date(item.tanggal_berakhir).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
            
            document.getElementById('detail_periode_top').textContent = `Kontrak Periode ${startDate} - ${endDate}`;
            document.getElementById('detail_periode').textContent = `${startDate} - ${endDate}`;
            
            const badge = document.getElementById('detail_status_badge');
            badge.textContent = item.status === 'aktif' ? 'Aktif' : 'Tidak Aktif';
            badge.className = `status-badge ${item.status === 'aktif' ? 'status-active' : 'status-expired'}`;

            // Sisa Waktu
            const sisaWaktuEl = document.getElementById('detail_sisa_waktu');

            if (item.status === 'aktif' && item.tanggal_berakhir) {
                const diffTime = new Date(item.tanggal_berakhir) - (new Date()).setHours(0,0,0,0);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const days = Math.max(0, diffDays);
                sisaWaktuEl.textContent = `${days} Hari`;
                sisaWaktuEl.style.color = days <= 15 ? 'var(--danger)' : (days <= 30 ? 'var(--warning)' : 'var(--success)');
                sisaWaktuEl.setAttribute('data-expiration', item.tanggal_berakhir);
                sisaWaktuEl.classList.add('status-badge');
            } else if (item.status === 'aktif') {
                sisaWaktuEl.textContent = '-';
                sisaWaktuEl.removeAttribute('data-expiration');
            } else {
                sisaWaktuEl.textContent = 'Sudah Berakhir';
                sisaWaktuEl.style.color = 'var(--danger)';
                sisaWaktuEl.removeAttribute('data-expiration');
            }

            const benefitArray = parseJsonField(item.benefit);
            const benefitHtml = benefitArray.length > 0 
                ? benefitArray.map(b => `<li>${b}</li>`).join('') 
                : '<li class="text-muted-empty">Tidak ada data benefit</li>';
            document.getElementById('detail_benefit').innerHTML = benefitHtml;

            const cakupanArray = parseJsonField(item.cakupan);
            const cakupanHtml = cakupanArray.length > 0 
                ? `<div class="badge-list">${cakupanArray.map(c => `<span class="status-badge badge-cakupan-sync">${c}</span>`).join('')}</div>`
                : '<p class="empty-text-sm">Tidak ada data cakupan</p>';
            document.getElementById('detail_cakupan').innerHTML = cakupanHtml;

            const diskon = item.diskon && item.diskon.length > 0 ? Math.round(item.diskon[0].persentase_diskon) + '%' : '-';
            const diskonEl = document.getElementById('detail_diskon');
            diskonEl.textContent = diskon;
            if (diskon !== '-') {
                diskonEl.className = 'badge-diskon-sync';
            } else {
                diskonEl.className = '';
            }

            const obatArray = parseJsonField(item.obat);
            const obatHtml = obatArray.length > 0 
                ? obatArray.map(o => `<span class="status-badge badge-obat-sync">${typeof o === 'object' ? (o.nama || JSON.stringify(o)) : o}</span>`).join('')
                : '<p class="empty-text-sm">Seluruh obat (Tidak ada batasan)</p>';
            document.getElementById('detail_obat').innerHTML = obatHtml;

            const penagihanList = item.penagihan_pembayaran || [];
            const jwLabel = penagihanList.length > 0 
                ? penagihanList.map(p => `${p.jangka_waktu_hari || p.jangka_waktu || '-'} ${p.tipe_hari || 'Hari'}`).join(', ')
                : '-';
            document.getElementById('detail_jangka_waktu').textContent = jwLabel;

            document.getElementById('detail_deskripsi').textContent = item.deskripsi || 'Tidak ada catatan tambahan.';
            
            document.getElementById('detailModal').style.display = 'flex';
        }

        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) closeDetailModal();
        });
    </script>
</x-app-layout>
