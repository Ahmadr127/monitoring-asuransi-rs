<x-user-layout>
    <x-slot name="title">{{ $title }}</x-slot>

    @php $pageTitle = $title; @endphp

    <div class="detail-page-header">
        <div class="detail-header-content">
            <a href="{{ route('user.kerjasama.index') }}" class="back-button">
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
                            <div class="card-header-content">
                                <div class="card-header-inner">
                                    <h3 class="card-title">{{ Str::upper($item->nama_asuransi) }}</h3>
                                    @if($item->is_high_profile)
                                        <span class="status-badge status-expired badge-high">High</span>
                                    @endif
                                </div>
                                @if(in_array('alias_versi_tera', $visibleColumns) && $item->alias_versi_tera)
                                    <div class="alias-version-wrapper">
                                        <span class="status-badge badge-alias">
                                            {{ $item->alias_versi_tera }}
                                        </span>
                                    </div>
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
                                    <span class="info-value">{{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_berakhir->format('d M Y') }}</span>
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
                                                <span class="status-badge status-info badge-layanan-small">{{ $layanan }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="kerjasama-card-footer">
                            <a href="{{ route('user.kerjasama.detail', $item) }}" class="btn-detail no-underline btn-detail-flex">
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
        <div class="modal-container modal-container-wide">
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
                    @if(in_array('status', $visibleColumns))
                    <div class="summary-item">
                        <div class="summary-label">Status Kontrak</div>
                        <div id="detail_status_badge" class="status-badge"></div>
                    </div>
                    @endif
                    @if(in_array('periode', $visibleColumns))
                    <div class="summary-item">
                        <div class="summary-label">Periode Berlaku</div>
                        <div class="summary-value" id="detail_periode"></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Sisa Waktu</div>
                        <div class="summary-value highlight" id="detail_sisa_waktu">-</div>
                    </div>
                    @endif
                    @if(in_array('penjamin_category', $visibleColumns))
                    <div class="summary-item">
                        <div class="summary-label">Payor</div>
                        <div class="summary-value" id="detail_payor">-</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">TPA</div>
                        <div class="summary-value" id="detail_tpa">-</div>
                    </div>
                    @endif
                    @if(in_array('badan_usaha', $visibleColumns))
                    <div class="summary-item">
                        <div class="summary-label">Badan Usaha</div>
                        <div class="summary-value" id="detail_badan_usaha">-</div>
                    </div>
                    @endif
                    @if(in_array('alias_versi_tera', $visibleColumns))
                    <div class="summary-item">
                        <div class="summary-label">Versi Tera</div>
                        <div class="summary-value" id="detail_versi_tera">-</div>
                    </div>
                    @endif
                </div>

                <div class="detail-main-grid detail-main-grid-styled">
                    <!-- Left Column -->
                    <div class="detail-column">
                        @if(in_array('benefit', $visibleColumns))
                        <div class="content-section content-section-mt">
                            <h4 class="section-subtitle">
                                <x-icon name="check-circle" class="section-subtitle-icon" />
                                Benefit Utama
                            </h4>
                            <ul id="detail_benefit" class="feature-list feature-list-styled"></ul>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div class="detail-column">
                        @if(in_array('cakupan', $visibleColumns))
                        <div class="content-section">
                            <h4 class="section-subtitle">
                                <x-icon name="information-circle" class="section-subtitle-icon" />
                                Cakupan & Ketentuan
                            </h4>
                            <ul id="detail_cakupan" class="feature-list feature-list-styled"></ul>
                        </div>
                        @endif

                        @if(in_array('diskon', $visibleColumns) || in_array('jangka_waktu', $visibleColumns))
                        <div class="content-section content-section-mt">
                            <h4 class="section-subtitle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="section-subtitle-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"></path></svg>
                                Komersial
                            </h4>
                            <div class="commercial-info-card commercial-info-card-styled">
                                <div class="commercial-grid">
                                    @if(in_array('diskon', $visibleColumns))
                                    <div>
                                        <p class="commercial-label">Persentase Diskon</p>
                                        <div id="detail_diskon_container">
                                            <span id="detail_diskon" class="badge-diskon-sync">-</span>
                                        </div>
                                    </div>
                                    @endif
                                    @if(in_array('jangka_waktu', $visibleColumns))
                                    <div>
                                        <p class="commercial-label">Jangka Waktu Bayar</p>
                                        <span id="detail_jangka_waktu" class="status-badge badge-info-sync">-</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="content-section content-section-mt">
                            <h4 class="section-subtitle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="section-subtitle-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                Daftar Obat
                            </h4>
                            <div id="detail_obat" class="badge-list badge-list-mt"></div>
                        </div>
                    </div>
                </div>

                <div id="detail_deskripsi_section" class="deskripsi-section">
                    <h4 class="section-subtitle">Catatan Tambahan</h4>
                    <p id="detail_deskripsi" class="detail-deskripsi-text"></p>
                </div>
            </div>
            
            <div class="modal-footer modal-footer-padded">
                <button onclick="closeDetailModal()" class="btn-secondary btn-full-width">Tutup Detail Kontrak</button>
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
                namaContainer.innerHTML += ' <span class="status-badge status-expired badge-high">High</span>';
            }
            const startDate = new Date(item.tanggal_mulai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const endDate = new Date(item.tanggal_berakhir).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            
            document.getElementById('detail_periode_top').textContent = `Kontrak Periode ${startDate} - ${endDate}`;
            document.getElementById('detail_periode').textContent = `${startDate} - ${endDate}`;
            
            const badge = document.getElementById('detail_status_badge');
            badge.textContent = item.status === 'aktif' ? 'Aktif' : 'Tidak Aktif';
            badge.className = `status-badge ${item.status === 'aktif' ? 'status-active' : 'status-expired'}`;

            // Sisa Waktu
            const diffTime = new Date(item.tanggal_berakhir) - (new Date()).setHours(0,0,0,0);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const sisaWaktuEl = document.getElementById('detail_sisa_waktu');
            
            if (item.status === 'aktif') {
                const days = Math.max(0, diffDays);
                sisaWaktuEl.textContent = `${days} Hari`;
                sisaWaktuEl.style.color = days <= 15 ? 'var(--danger)' : (days <= 30 ? 'var(--warning)' : 'var(--success)');
                sisaWaktuEl.setAttribute('data-expiration', item.tanggal_berakhir);
                sisaWaktuEl.classList.add('status-badge');
            } else {
                sisaWaktuEl.textContent = 'Sudah Berakhir';
                sisaWaktuEl.style.color = 'var(--danger)';
                sisaWaktuEl.removeAttribute('data-expiration');
            }

            const benefitArray = parseJsonField(item.benefit);
            const benefitHtml = benefitArray.length > 0 
                ? benefitArray.map(b => `<li>${b}</li>`).join('') 
                : '<li class="empty-data-li">Tidak ada data benefit</li>';
            document.getElementById('detail_benefit').innerHTML = benefitHtml;

            const cakupanArray = parseJsonField(item.cakupan);
            const cakupanHtml = cakupanArray.length > 0 
                ? `<div class="badge-list badge-list-flex">${cakupanArray.map(c => `<span class="status-badge badge-cakupan-sync">${c}</span>`).join('')}</div>`
                : '<p class="empty-data-text">Tidak ada data cakupan</p>';
            document.getElementById('detail_cakupan').innerHTML = cakupanHtml;

            const diskon = item.diskon && item.diskon.length > 0 ? Math.round(item.diskon[0].persentase_diskon) + '%' : '-';
            const diskonEl = document.getElementById('detail_diskon');
            if (diskonEl) {
                diskonEl.textContent = diskon;
                diskonEl.className = diskon !== '-' ? 'badge-diskon-sync' : '';
            }

            const obatArray = parseJsonField(item.obat);
            const obatHtml = obatArray.length > 0 
                ? obatArray.map(o => `<span class="status-badge badge-obat-sync">${typeof o === 'object' ? (o.nama || JSON.stringify(o)) : o}</span>`).join('')
                : '<p class="empty-data-text">Seluruh obat (Tidak ada batasan)</p>';
            document.getElementById('detail_obat').innerHTML = obatHtml;

            const penagihanList = item.penagihan_pembayaran || [];
            const jwLabel = penagihanList.length > 0 
                ? penagihanList.map(p => `${p.jangka_waktu_hari || p.jangka_waktu || '-'} ${p.tipe_hari || 'Hari'}`).join(', ')
                : '-';
            document.getElementById('detail_jangka_waktu').textContent = jwLabel;

            // Payor, TPA, Badan Usaha
            document.getElementById('detail_payor').textContent = item.payor ? item.payor.nama : '-';
            document.getElementById('detail_tpa').textContent = item.tpa ? item.tpa.nama : '-';
            document.getElementById('detail_badan_usaha').textContent = item.badan_usaha ? item.badan_usaha.nama_bu : '-';
            document.getElementById('detail_versi_tera').textContent = item.alias_versi_tera || '-';

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
</x-user-layout>