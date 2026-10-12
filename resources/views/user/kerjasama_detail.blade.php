<x-user-layout>
    <x-slot name="title">Detail Kerjasama - {{ Str::upper($ruangLingkup->nama_asuransi) }}</x-slot>

    <div class="compact-detail-container">
        <!-- Page Header -->
        <div class="flex-between-center pb-2 mb-4 border-b border-slate-200">
            <div class="header-left">
                <h1 class="flex-center-gap-2 font-bold-700 text-lg color-slate-900">
                    {{ Str::upper($ruangLingkup->nama_asuransi) }}
                    @if(in_array('is_high_profile', $visibleColumns) && $ruangLingkup->is_high_profile)
                        <span class="status-badge status-expired badge-high">High</span>
                    @endif
                </h1>
            </div>
            <div class="flex-center-gap-2">
                <a href="{{ route('user.kerjasama.index') }}" title="Kembali ke Daftar" class="action-icon-btn">
                    <x-icon name="arrow-left" class="icon-14" />
                </a>
            </div>
        </div>

        <!-- Status Overview Cards -->
        <div class="status-overview-grid">
            @if(in_array('periode', $visibleColumns))
            <div class="status-card">
                <div class="status-icon icon-info">
                    <x-icon name="calendar" class="icon-16" />
                </div>
                <div class="status-content">
                    <div class="status-label">Tanggal Mulai</div>
                    <div class="status-value">{{ $ruangLingkup->tanggal_mulai->format('d M Y') }}</div>
                </div>
            </div>

            <div class="status-card">
                <div class="status-icon icon-warning">
                    <x-icon name="calendar" class="icon-16" />
                </div>
                <div class="status-content">
                    <div class="status-label">Tanggal Berakhir</div>
                    <div class="status-value">{{ $ruangLingkup->tanggal_berakhir->format('d M Y') }}</div>
                </div>
            </div>
            @endif

            @if(in_array('status', $visibleColumns))
            <div class="status-card">
                <div class="status-icon {{ $ruangLingkup->status === 'aktif' ? 'icon-success' : 'icon-danger' }}">
                    <x-icon name="check-circle" class="icon-16" />
                </div>
                <div class="status-content">
                    <div class="status-label">Status</div>
                    <div class="status-value">
                        <span class="status-badge {{ $ruangLingkup->status === 'aktif' ? 'status-active' : 'status-expired' }}">
                            {{ $ruangLingkup->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            @if(in_array('periode', $visibleColumns) && $ruangLingkup->status === 'aktif')
                <div class="status-card">
                    @php
                        $remaining = floor(now()->diffInDays($ruangLingkup->tanggal_berakhir, false));
                        $iconClass = $remaining <= 15 ? 'icon-danger' : ($remaining <= 30 ? 'icon-warning' : 'icon-success');
                    @endphp
                    <div class="status-icon {{ $iconClass }}">
                        <x-icon name="clock" class="icon-16" />
                    </div>
                    <div class="status-content">
                        <div class="status-label">Sisa Waktu</div>
                        <div class="status-value">{{ $remaining > 0 ? $remaining : '0' }} Hari</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="flex-col gap-4">

            <!-- Informasi Dasar -->
            <div class="detail-section">
                <div class="section-header-minimal">
                    <x-icon name="information-circle" class="icon-14 color-muted" />
                    <h3>Informasi Dasar</h3>
                </div>
                <div class="detail-grid-4">
                    <div class="detail-item span-2">
                        <div class="detail-label">Nama Asuransi</div>
                        <div class="detail-value">{{ Str::upper($ruangLingkup->nama_asuransi) }}</div>
                    </div>
                    @if(in_array('alias_versi_tera', $visibleColumns))
                    <div class="detail-item span-2">
                        <div class="detail-label">Alias Versi Tera</div>
                        <div class="detail-value">{{ $ruangLingkup->alias_versi_tera ?? '-' }}</div>
                    </div>
                    @endif
                    @if(in_array('penjamin_category', $visibleColumns))
                    <div class="detail-item">
                        <div class="detail-label">TPA / PAYOR</div>
                        <div class="detail-value">
                            <span class="status-badge status-info badge-info-sync">{{ $ruangLingkup->penjamin_category ?? '-' }}</span>
                        </div>
                    </div>
                    @endif
                    @if(in_array('badan_usaha', $visibleColumns))
                    <div class="detail-item span-3">
                        <div class="detail-label">Badan Usaha</div>
                        <div class="detail-value">
                            @if($ruangLingkup->badanUsahaPivot && $ruangLingkup->badanUsahaPivot->count() > 0)
                                @foreach($ruangLingkup->badanUsahaPivot as $p)
                                    <span class="status-badge status-active badge-success-sync badge-bu">
                                        {{ Str::upper($p->badanUsaha->nama_bu ?? '-') }}
                                    </span>
                                @endforeach
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="detail-item span-4 detail-section-divider">
                        <div class="detail-label">PIC Asuransi &amp; Penagihan</div>
                    </div>
                    <div class="detail-item span-2">
                        <div class="detail-label">Nama PIC</div>
                        <div class="detail-value">{{ $ruangLingkup->pic_nama ?? '-' }}</div>
                    </div>
                    <div class="detail-item span-2">
                        <div class="detail-label">Kontak PIC</div>
                        <div class="detail-value">{{ $ruangLingkup->pic_kontak ?? '-' }}</div>
                    </div>
                    <div class="detail-item span-2">
                        <div class="detail-label">Email PIC</div>
                        <div class="detail-value">{{ $ruangLingkup->pic_email ?? '-' }}</div>
                    </div>
                    <div class="detail-item span-2">
                        <div class="detail-label">Link Portal</div>
                        <div class="detail-value">
                            @if(!empty($ruangLingkup->pic_portal))
                                <a href="{{ $ruangLingkup->pic_portal }}" target="_blank" rel="noopener">{{ $ruangLingkup->pic_portal }}</a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="detail-item span-4">
                        <div class="detail-label">Alamat Penagihan</div>
                        <div class="detail-value">{{ $ruangLingkup->alamat_penagihan ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Layanan & Cakupan per Badan Usaha -->
            <div class="detail-section">
                <div class="section-header-minimal">
                    <x-icon name="building" class="icon-14 color-muted" />
                    <h3>Layanan &amp; Cakupan</h3>
                </div>

                @php
                    $pivots = $ruangLingkup->badanUsahaPivot ?? collect();
                    $layananMap = $jenisLayanan->keyBy('id');
                    $obatMap = $masterObat->keyBy('id');
                @endphp

                @if($pivots->count() > 0)
                    @foreach($pivots as $p)
                        @php
                            $buName = $p->badanUsaha->nama_bu ?? '-';
                            $buLayananIds = $p->jenis_layanan_ids ?? [];
                            $buObatIds = $p->obat_ids ?? [];
                            $buBenefit = $p->benefit ?? [];
                            $buCakupan = $p->cakupan ?? [];
                            $buDeskripsi = $p->deskripsi ?? '';
                            $hasData = count($buLayananIds) > 0 || count($buObatIds) > 0
                                || count($buBenefit) > 0 || count($buCakupan) > 0
                                || $buDeskripsi;
                        @endphp

                        @if($hasData || $loop->first)
                        <div class="detail-item span-4 bu-section">
                            <div class="detail-label bu-name-label">
                                {{ Str::upper($buName) }}
                            </div>

                            <div class="bu-grid">
                                @if(in_array('jenis_layanan', $visibleColumns))
                                <div class="grid-full-width">
                                    <div class="text-xs-muted detail-label-sm">Jenis Layanan</div>
                                    @if(count($buLayananIds) > 0)
                                        <div class="flex-wrap-gap-1">
                                            @foreach($buLayananIds as $lid)
                                                @php $lnama = $layananMap->get($lid)?->nama ?? null; @endphp
                                                @if($lnama)
                                                    <span class="badge-layanan-sync">{{ $lnama }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs-muted text-xs-fallback">-</div>
                                    @endif
                                </div>
                                @endif

                                @if(in_array('benefit', $visibleColumns))
                                <div>
                                    <div class="text-xs-muted detail-label-sm">Benefit</div>
                                    @if(count($buBenefit) > 0)
                                        <div class="flex-col gap-1">
                                            @foreach($buBenefit as $b)
                                                <div class="flex-center-gap-2 text-xs color-slate-600">
                                                    <span class="bullet-primary">•</span>{{ $b }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs-muted text-xs-fallback">-</div>
                                    @endif
                                </div>
                                @endif

                                @if(in_array('cakupan', $visibleColumns))
                                <div>
                                    <div class="text-xs-muted detail-label-sm">Cakupan</div>
                                    @if(count($buCakupan) > 0)
                                        <div class="flex-col gap-1">
                                            @foreach($buCakupan as $c)
                                                <div class="flex-center-gap-2 text-xs color-slate-600">
                                                    <span class="bullet-primary">•</span>{{ $c }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs-muted text-xs-fallback">-</div>
                                    @endif
                                </div>
                                @endif

                                @if(in_array('obat', $visibleColumns))
                                <div class="grid-full-width">
                                    <div class="text-xs-muted detail-label-sm">Daftar Obat yang Tercakup</div>
                                    @if(count($buObatIds) > 0)
                                        <div class="flex-wrap-gap-1">
                                            @foreach($buObatIds as $oid)
                                                @php $onama = $obatMap->get($oid)?->nama ?? null; @endphp
                                                @if($onama)
                                                    <span class="badge-obat-sync">{{ $onama }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs-muted text-xs-fallback">Seluruh obat (Tidak ada batasan)</div>
                                    @endif
                                </div>
                                @endif
                            </div>

                            @if($buDeskripsi)
                            <div class="bu-section-footer">
                                <div class="text-xs-muted detail-label-sm">Deskripsi / Catatan</div>
                                <div class="text-xs color-slate-600">{{ $buDeskripsi }}</div>
                            </div>
                            @endif
                        </div>
                        @endif
                    @endforeach
                @else
                    <div class="detail-item span-4">
                        <div class="text-xs-muted">Tidak ada data Badan Usaha</div>
                    </div>
                @endif
            </div>

            <!-- Komersial -->
            @if(count(array_intersect(['diskon', 'jangka_waktu'], $visibleColumns)) > 0)
            <div class="detail-grid-2">
                @if(in_array('diskon', $visibleColumns))
                <div class="detail-section">
                    <div class="section-header-minimal">
                    <x-icon name="currency-dollar" class="icon-14 color-muted" />
                    <h3>Diskon Kerjasama</h3>
                    </div>
                    <div class="detail-content">
                        @if($ruangLingkup->diskon && $ruangLingkup->diskon->count() > 0)
                            <div class="flex-col gap-2">
                                @foreach($ruangLingkup->diskon as $diskon)
                                    <div class="flex-between-center py-1 border-b border-slate-100 last-no-border">
                                        <div class="badge-diskon-sync">{{ $diskon->persentase_diskon }}% OFF</div>
                                        @if($diskon->keterangan)
                                            <div class="text-xs color-muted">{{ $diskon->keterangan }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs-muted">Tidak ada data diskon</div>
                        @endif
                    </div>
                </div>
                @endif

                @if(in_array('jangka_waktu', $visibleColumns))
                <div class="detail-section">
                    <div class="section-header-minimal">
                    <x-icon name="calendar" class="icon-14 color-muted" />
                    <h3>Penagihan & Pembayaran</h3>
                    </div>
                    <div class="detail-content">
                        @if($ruangLingkup->penagihanPembayaran && $ruangLingkup->penagihanPembayaran->count() > 0)
                            @foreach($ruangLingkup->penagihanPembayaran as $penagihan)
                                <div class="flex-between-center py-1 border-b border-slate-100 last-no-border">
                                    <div class="font-bold-700 color-slate-900 text-xs">
                                        {{ $penagihan->jangka_waktu_hari }} {{ $penagihan->tipe_hari }}</div>
                                    @if($penagihan->keterangan)
                                        <div class="text-xs color-slate-600">{{ $penagihan->keterangan }}</div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-xs-muted">Tidak ada aturan penagihan</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Deskripsi & Catatan -->
            <div class="detail-section">
                <div class="section-header-minimal">
                    <x-icon name="document" class="icon-14 color-muted" />
                    <h3>Deskripsi & Catatan</h3>
                </div>
                <div class="detail-content">
                    @if($ruangLingkup->deskripsi)
                        <div class="text-xs color-slate-600 line-height-relaxed">{{ $ruangLingkup->deskripsi }}</div>
                    @else
                        <div class="text-xs-muted">Tidak ada deskripsi</div>
                    @endif
                </div>
            </div>

            <!-- Riwayat Kontrak -->
            @if(in_array('contract_history', $visibleColumns) && $ruangLingkup->contractHistory && $ruangLingkup->contractHistory->count() > 0)
            <div class="detail-section">
                <div class="section-header-minimal">
                    <x-icon name="clock" class="icon-14 color-muted" />
                    <h3>Riwayat Kontrak</h3>
                </div>
                <div class="detail-content">
                    <div class="history-timeline">
                        <div class="history-item">
                            <div class="history-date">{{ $ruangLingkup->created_at->format('d M Y, H:i') }}</div>
                            <div class="history-title">Kontrak Awal</div>
                            <div>
                                <div class="history-details">
                                    <span>{{ $ruangLingkup->tanggal_mulai->format('d M Y') }}</span>
                                    <span class="arrow">→</span>
                                    <span>{{ $ruangLingkup->tanggal_berakhir->format('d M Y') }}</span>
                                </div>
                                <div class="text-xs-muted mt-1">Durasi: {{ $ruangLingkup->tanggal_mulai->diffInDays($ruangLingkup->tanggal_berakhir) }} Hari</div>
                            </div>
                        </div>

                        @php
                            $histories = $ruangLingkup->contractHistory
                                ->where('action_type', '!=', 'created')
                                ->sortBy('created_at');
                        @endphp
                        @foreach($histories as $history)
                            @php
                                $title = match($history->action_type) {
                                    'rollover' => 'Perpanjangan Kontrak',
                                    'updated' => 'Perubahan Kontrak',
                                    'status_change' => 'Perubahan Status',
                                    'submitted' => 'Pengajuan Persetujuan',
                                    'step_approved' => 'Langkah Disetujui',
                                    'approved' => 'Kontrak Disetujui',
                                    'rejected' => 'Pengajuan Ditolak',
                                    'cancelled' => 'Pengajuan Dibatalkan',
                                    'reminder_sent' => 'Reminder Terkirim',
                                    'expired_reminder_sent' => 'Reminder Kontrak Berakhir',
                                    default => 'Perubahan Kontrak',
                                };
                                $showDates = $history->old_end_date || $history->new_end_date;
                                $showStatus = $history->old_status || $history->new_status;
                            @endphp
                            <div class="history-item">
                                <div class="history-date">{{ $history->created_at->format('d M Y, H:i') }}</div>
                                <div class="history-title">{{ $title }}</div>
                                <div>
                                    @if($showDates)
                                        <div class="history-details">
                                            <span>{{ $history->old_end_date ? $history->old_end_date->format('d M Y') : '-' }}</span>
                                            <span class="arrow">→</span>
                                            <span>{{ $history->new_end_date ? $history->new_end_date->format('d M Y') : '-' }}</span>
                                        </div>
                                    @endif
                                    @if($showStatus)
                                        <div class="text-xs-muted mt-1">
                                            Status: {{ $history->old_status ?? '-' }} → {{ $history->new_status ?? '-' }}
                                        </div>
                                    @endif
                                    @if($history->notes)
                                        <div class="text-xs-muted mt-1 italic">Catatan: {{ $history->notes }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-user-layout>