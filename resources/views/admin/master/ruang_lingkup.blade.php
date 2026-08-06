<x-app-layout>
    <x-slot name="title">Data Kerjasama Asuransi</x-slot>

    @php
        $pageTitle = 'Master Data Kerjasama';
        $pageDescription = 'Kelola data master kerjasama asuransi, unit, dan kategori penjamin';
    @endphp

    @push('navbar-meta')
        <script>
            (function () {
                const descEl = document.getElementById('navbarPageDesc');
                if (descEl) { descEl.textContent = '{{ $pageDescription }}'; descEl.style.display = 'block'; }
            })();
        </script>
    @endpush

    <div class="data-table-container">
        <div class="filter-search-section">
            <div class="search-container mb-6">
                <div class="search-box-advanced">
                    <div class="search-icon">
                        <x-icon name="search" class="" />
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari asuransi, layanan, benefit, cakupan..."
                        onkeyup="filterTable()">
                    <button id="clearSearch" onclick="clearSearchInput()" class="clear-search-btn">
                        <x-icon name="close" class="" />
                    </button>
                    <div id="autocompleteList" class="autocomplete-dropdown"></div>
                </div>
                <div class="header-actions">
                    <div class="btn-group btn-group-flex">
                        <a href="{{ route('admin.export.import-form') }}" class="btn-secondary btn-sm btn-import-export">
                            Import
                        </a>
                        <div class="dropdown dropdown-wrapper">
                            <button onclick="toggleExportDropdown(event)" class="btn-secondary btn-sm btn-import-export btn-export-toggle">
                                Export
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div id="exportDropdown" class="dropdown-menu export-dropdown-menu d-none">
                                <a href="{{ route('admin.export.xlsx') }}" class="dropdown-item dropdown-item-link">Export XLSX</a>
                                <a href="{{ route('admin.export.csv') }}" class="dropdown-item dropdown-item-link">Export CSV</a>
                                <a href="{{ route('admin.export.pdf') }}" class="dropdown-item dropdown-item-link">Export PDF</a>
                            </div>
                        </div>
                        <a href="{{ route('admin.ruang-lingkup.create') }}" class="btn-primary btn-add-premium">
                            <x-icon name="plus-circle-solid" class="icon-16-fixed" />
                            Tambah Data
                        </a>
                    </div>
                </div>
            </div>

            <div class="filter-row-wrapper">
                <div class="filter-left-side">
                    <div class="filter-group">
                        <label>Bulan Kontrak</label>
                        <div class="filter-input-container">
                            <select id="monthFilter" onchange="filterTable()" class="filter-select">
                                <option value="">Semua Bulan</option>
                                @foreach(['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $v => $n)
                                    <option value="{{ $v }}">{{ $n }}</option>
                                @endforeach
                            </select>
                            <button id="clearMonth" onclick="clearMonthFilter()" class="clear-filter-btn hidden">
                                <x-icon name="close" class="" />
                            </button>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Tahun Kontrak</label>
                        <div class="filter-input-container">
                            <select id="yearFilter" onchange="filterTable()" class="filter-select">
                                <option value="">Semua Tahun</option>
                                @php
                                    $rlModels = collect($ruangLingkup)->pluck('rl');
                                    $years = $rlModels->pluck('tanggal_mulai')->filter()->map(fn($d) => \Carbon\Carbon::parse($d)->year)
                                        ->merge($rlModels->pluck('tanggal_berakhir')->filter()->map(fn($d) => \Carbon\Carbon::parse($d)->year))
                                        ->unique()->sort();
                                @endphp
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                            <button id="clearYear" onclick="clearYearFilter()" class="clear-filter-btn hidden">
                                <x-icon name="close" class="" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="filter-right-side">
                    <div class="tampilan-filter-group">
                        <label>Tampilan</label>
                        <select id="itemsPerPageSelect" onchange="changeItemsPerPage()" class="tampilan-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <button onclick="resetAllFilters()" class="btn-reset-standard">
                        <x-icon name="refresh" class="icon-14" />
                        Reset Filter
                    </button>
                </div>
            </div>

            <div class="hidden">
                <input type="checkbox" id="enablePeriodFilter">
                <input type="checkbox" id="enableDateRangeFilter">
                <input type="checkbox" id="enableMonthFilter">
                <input type="checkbox" id="enableYearFilter">
                <div id="periodFilterContainer"></div>
                <div id="monthFilterContainer"></div>
                <div id="yearFilterContainer"></div>
                <div id="dateRangeFilterContainer"></div>
            </div>
        </div>

        <div id="tableView" class="table-view">
            <div class="admin-wrapper">
                <table class="page-table admin-table">
                    <thead>
                        <tr class="table-filter-row">
                            <th></th>
                            <th>
                                <div class="filter-input-container" id="wrap-namaAsuransiFilter">
                                    <input type="text" id="namaAsuransiFilter" list="namaAsuransiList"
                                        oninput="handleFilterInput(this)" autocomplete="off" class="filter-select"
                                        placeholder="Cari...">
                                    <button type="button" class="clear-filter-btn"
                                        onclick="clearFilterWrap('namaAsuransiFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                                <datalist id="namaAsuransiList">
                                    @php $namaList = collect($ruangLingkup)->pluck('rl')->pluck('nama_asuransi')->filter()->unique()->sort(); @endphp
                                    @foreach($namaList as $nama)<option value="{{ $nama }}">@endforeach
                                </datalist>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-aliasTeraFilter">
                                    <input type="text" id="aliasTeraFilter" list="aliasTeraList"
                                        oninput="handleFilterInput(this)" autocomplete="off" class="filter-select"
                                        placeholder="Cari...">
                                    <button type="button" class="clear-filter-btn"
                                        onclick="clearFilterWrap('aliasTeraFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                                <datalist id="aliasTeraList">
                                    @php $aliasList = collect($ruangLingkup)->pluck('rl')->pluck('alias_versi_tera')->filter()->unique()->sort(); @endphp
                                    @foreach($aliasList as $alias)<option value="{{ $alias }}">@endforeach
                                </datalist>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-payorTpaFilter">
                                    <select id="payorTpaFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="TPA">TPA</option>
                                        <option value="PAYOR">PAYOR</option>
                                        <option value="TPA &amp; PAYOR">TPA & PAYOR</option>
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('payorTpaFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-highProfileFilter">
                                    <select id="highProfileFilter" onchange="handleFilterInput(this)"
                                        class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="1">High</option>
                                        <option value="0">No High</option>
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('highProfileFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-badanUsahaFilter">
                                    <input type="text" id="badanUsahaFilter" list="badanUsahaList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari BU...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('badanUsahaFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                                <datalist id="badanUsahaList">
                                    @php $bus = collect($ruangLingkup)->pluck('bu_nama')->filter()->unique()->sort(); @endphp
                                    @foreach($bus as $bu)<option value="{{ htmlspecialchars($bu) }}">@endforeach
                                </datalist>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-periodFilter">
                                    <select id="periodFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="active">Jalan</option>
                                        <option value="expiring30">≤ 30 H</option>
                                        <option value="expiring90">≤ 90 H</option>
                                        <option value="expired">Habis</option>
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('periodFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-jangkaWaktuFilter">
                                    <select id="jangkaWaktuFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        @php
                                            $jangkaWaktuList = collect($ruangLingkup)->pluck('rl')->flatMap(function ($item) {
                                                return $item->penagihanPembayaran ? $item->penagihanPembayaran->map(fn($p) => [
                                                    'key' => $p->jangka_waktu_hari . '|' . $p->tipe_hari,
                                                    'label' => $p->jangka_waktu_hari . ' Hari ' . $p->tipe_hari,
                                                ]) : collect();
                                            })->filter()->unique('key')->sortBy('label');
                                        @endphp
                                        @foreach($jangkaWaktuList as $jw)
                                            <option value="{{ $jw['key'] }}">{{ $jw['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('jangkaWaktuFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-diskonFilter">
                                    <input type="text" id="diskonFilter" oninput="handleFilterInput(this)" autocomplete="off" class="filter-input-pure" placeholder="Cari diskon...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('diskonFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-serviceFilter">
                                    <input type="text" id="serviceFilter" list="serviceList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari layanan...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('serviceFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                                <datalist id="serviceList">
                                    @php
                                        $semuaLayanan = collect($ruangLingkup)->pluck('rl')->flatMap(function ($item) {
                                            $services = collect();
                                            if ($item->jenisLayananRelasi && $item->jenisLayananRelasi->count() > 0) {
                                                $services = $services->merge($item->jenisLayananRelasi->pluck('nama'));
                                            }
                                            return $services;
                                        })->filter()->unique()->sort();
                                    @endphp
                                    @foreach($semuaLayanan as $layanan)
                                        <option value="{{ $layanan }}">
                                    @endforeach
                                </datalist>
                            </th>
                            <th></th>
                            <th></th>
                            <th>
                                <div class="filter-input-container" id="wrap-obatFilter">
                                    <input type="text" id="obatFilter" list="obatList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari obat...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('obatFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                                <datalist id="obatList">
                                    @php
                                        $semuaObat = collect($ruangLingkup)->pluck('rl')->flatMap(function ($item) {
                                            return $item->obatRelasi->pluck('nama');
                                        })->filter()->unique()->sort();
                                    @endphp
                                    @foreach($semuaObat as $namaObat)
                                        <option value="{{ $namaObat }}">
                                    @endforeach
                                </datalist>
                            </th>
                            <th>
                                <div class="filter-input-container" id="wrap-statusFilter">
                                    <select id="statusFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="aktif">Aktif</option>
                                        <option value="tidak_aktif">Non-Aktif</option>
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('statusFilter')" title="Clear"><x-icon name="close" class="" /></button>
                                </div>
                            </th>
                            <th></th>
                        </tr>
                        <tr class="filter-spacer-row">
                            <th colspan="15"></th>
                        </tr>
                        <tr>
                            <th class="text-center w-40 th-min-50">No</th>
                            <th class="sortable text-left th-min-250" onclick="sortTable('nama_asuransi')">
                                <span>Nama Asuransi</span>
                                <x-icon name="sort" class="sort-icon" />
                            </th>
                            <th class="text-left w-120 th-min-140">Alias Versi Tera</th>
                            <th class="text-center w-100 th-min-110">TPA/PAYOR</th>
                            <th class="text-center w-80 th-min-90">High Profile</th>
                            <th class="bu-cell text-left th-min-200">Badan Usaha</th>
                            <th class="sortable period-cell text-left th-min-220" onclick="sortTable('periode')">
                                <span>Periode</span>
                                <x-icon name="sort" class="sort-icon" />
                            </th>
                            <th class="text-center w-120 th-min-130">Jangka Waktu</th>
                            <th class="text-center w-50 th-min-60">Diskon</th>
                            <th class="services-cell text-left th-min-200">Jenis Layanan</th>
                            <th class="benefits-cell text-left th-min-200">Benefit</th>
                            <th class="coverage-cell text-left th-min-200">Cakupan</th>
                            <th class="obat-cell text-left th-min-200">Obat</th>
                            <th class="sortable text-center w-90 th-min-100" onclick="sortTable('status')">
                                <span>Status</span>
                                <x-icon name="sort" class="sort-icon" />
                            </th>
                            <th class="actions-header aksi-cell text-center w-80 th-min-70">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @php
                            // Group by RL id for client-side grouping
                            $grouped = collect($ruangLingkup)->groupBy(fn($item) => $item['rl']->id);
                        @endphp
                        @forelse($ruangLingkup as $index => $item)
                            @php
                                $rl = $item['rl'];
                                $pivot = $item['pivot'];
                                $benefitArray = $item['benefit'];
                                $cakupanArray = $item['cakupan'];
                                $buName = $item['bu_nama'];

                                // String untuk search
                                $pivotLayananIds = $pivot->jenis_layanan_ids ?? [];
                                $jenisLayananArray = [];
                                if (! empty($pivotLayananIds) && $rl->jenisLayananRelasi && $rl->jenisLayananRelasi->count() > 0) {
                                    $jenisLayananArray = $rl->jenisLayananRelasi->whereIn('id', $pivotLayananIds)->pluck('nama')->toArray();
                                }
                                $searchLayanan = strtolower(implode(' ', $jenisLayananArray));
                                $searchBenefit = strtolower(implode(' ', $benefitArray));
                                $searchCakupan = strtolower(implode(' ', $cakupanArray));
                                $pivotObatIds = $pivot->obat_ids ?? [];
                                $searchObat = '';
                                if (! empty($pivotObatIds)) {
                                    $searchObat = strtolower($rl->obatRelasi->whereIn('id', $pivotObatIds)->pluck('nama')->implode(' '));
                                }
                                $searchDeskripsi = strtolower($rl->deskripsi ?? '');

                                $searchDiskon = '';
                                if ($rl->diskon && $rl->diskon->count() > 0) {
                                    $searchDiskon = $rl->diskon->pluck('persentase_diskon')->implode(' ') . ' ' .
                                        strtolower($rl->diskon->pluck('keterangan')->implode(' '));
                                }

                                // Semua BU names untuk grup ini (digunakan saat mode grouping)
                                $allBuNames = $grouped->get($rl->id, collect())->pluck('bu_nama')->filter()->unique();
                            @endphp
                            <tr class="table-row"
                                data-rl-id="{{ $rl->id }}"
                                data-index="{{ $index + 1 }}"
                                data-nama="{{ strtolower($rl->nama_asuransi) }}"
                                data-alias-tera="{{ strtolower($rl->alias_versi_tera ?? '') }}"
                                data-layanan="{{ $searchLayanan }}"
                                data-benefit="{{ $searchBenefit }}"
                                data-cakupan="{{ $searchCakupan }}"
                                data-obat="{{ $searchObat }}"
                                data-deskripsi="{{ $searchDeskripsi }}"
                                data-diskon="{{ $searchDiskon }}"
                                data-status="{{ $rl->status }}"
                                data-penjamin-category="{{ $rl->penjamin_category }}"
                                data-high-profile="{{ $rl->is_high_profile ? '1' : '0' }}"
                                data-tanggal-mulai="{{ $rl->tanggal_mulai->format('Y-m-d') }}"
                                data-tanggal-berakhir="{{ $rl->tanggal_berakhir->format('Y-m-d') }}"
                                data-badan-usaha="{{ strtolower($buName) }}"
                                data-jangka-waktu="{{ $rl->penagihanPembayaran ? implode(',', $rl->penagihanPembayaran->map(fn($p) => $p->jangka_waktu_hari . '|' . $p->tipe_hari)->toArray()) : '' }}"
                                data-all-bu-names="{{ strtolower($allBuNames->implode(' ')) }}">

                                <td class="number-cell text-center font-bold-700 text-muted-premium">
                                    <span class="row-number">{{ $index + 1 }}</span>
                                </td>
                                <td class="company-cell text-left">
                                    <div class="company-info">
                                        <div class="company-details">
                                            <div class="company-name font-bold-700 color-slate-900">
                                                {{ Str::upper($rl->nama_asuransi) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-left text-sm-semibold color-indigo">
                                    @if($rl->alias_versi_tera)
                                        <span class="badge badge-indigo text-xs-bold inline-flex-center letter-spacing-tight">
                                            {{ $rl->alias_versi_tera }}
                                        </span>
                                    @else
                                        <span class="color-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-info text-xs-bold min-w-60">
                                        {{ $rl->penjamin_category ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($rl->is_high_profile)
                                        <span class="badge-high-profile">High</span>
                                    @else
                                        <span class="color-muted text-xs-medium">-</span>
                                    @endif
                                </td>
                                <td class="bu-cell text-left color-slate-700">
                                    <span class="badge badge-bu bu-single">{{ Str::upper($buName) }}</span>
                                    <span class="badge badge-bu bu-group-badge d-none"></span>
                                </td>
                                <td class="period-cell text-left">
                                    <div class="period-info">
                                        <div class="period-dates">
                                            <div class="start-date">{{ $rl->tanggal_mulai->format('d M Y') }}</div>
                                            <div class="date-separator">—</div>
                                            <div class="end-date">{{ $rl->tanggal_berakhir->format('d M Y') }}</div>
                                        </div>
                                        <div class="period-duration">
                                            @php
                                                $duration = $rl->tanggal_mulai->diffInDays($rl->tanggal_berakhir);
                                                $remaining = floor(now()->diffInDays($rl->tanggal_berakhir, false));
                                            @endphp
                                            <div class="flex-col-start">
                                                <span class="duration text-xs-muted">Total: {{ $duration }} Hari</span>
                                                @if($rl->status === 'aktif')
                                                    <span class="remaining status-badge {{ $remaining <= 30 ? 'status-critical' : ($remaining <= 90 ? 'status-warning-badge' : 'status-active') }} font-bold-700 text-xs-medium"
                                                          data-expiration="{{ $rl->tanggal_berakhir }}">Tersisa: {{ max(0, $remaining) }} Hari
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center text-xs-medium">
                                    @if($rl->penagihanPembayaran && $rl->penagihanPembayaran->count() > 0)
                                        @foreach($rl->penagihanPembayaran as $penagihan)
                                            <div class="font-bold-700 color-slate-900">
                                                {{ $penagihan->jangka_waktu_hari }} {{ $penagihan->tipe_hari }}
                                            </div>
                                        @endforeach
                                    @else
                                        <a href="{{ route('admin.penagihan-pembayaran.index') }}" class="btn-tambah-inline" title="Tambah jangka waktu bayar">
                                            <x-icon name="plus-circle-solid" class="" />
                                            Tambah
                                        </a>
                                    @endif
                                </td>
                                <td class="discount-cell text-center">
                                    @if($rl->diskon && $rl->diskon->count() > 0)
                                        @foreach($rl->diskon as $d)
                                            <span class="status-badge status-active text-xs-bold inline-flex-center">
                                                {{ $d->persentase_diskon }}%
                                            </span>
                                        @endforeach
                                    @else
                                        <a href="{{ route('admin.diskon.index') }}" class="btn-tambah-inline" title="Tambah diskon">
                                            <x-icon name="plus-circle-solid" class="" />
                                            Tambah
                                        </a>
                                    @endif
                                </td>
                                <td class="services-cell text-left">
                                    @if(count($jenisLayananArray) > 0)
                                        <div class="service-badges">
                                            @foreach($jenisLayananArray as $layanan)
                                                <span class="badge-layanan-khusus" data-item="{{ strtolower($layanan) }}">{{ $layanan }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </td>
                                <td class="benefits-cell text-left">
                                    @if(count($benefitArray) > 0)
                                        <div class="benefit-list">
                                            @foreach(array_slice($benefitArray, 0, 3) as $ben)
                                                <div class="benefit-item">
                                                    <span class="benefit-icon">✓</span> {{ Str::limit($ben, 35) }}
                                                </div>
                                            @endforeach
                                            @if(count($benefitArray) > 3)
                                                <div class="more-items">+{{ count($benefitArray) - 3 }} benefit lainnya</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </td>
                                <td class="coverage-cell text-left">
                                    @if(count($cakupanArray) > 0)
                                        <div class="coverage-list">
                                            @foreach(array_slice($cakupanArray, 0, 2) as $cak)
                                                <div class="coverage-item">{{ Str::limit($cak, 30) }}</div>
                                            @endforeach
                                            @if(count($cakupanArray) > 2)
                                                <div class="more-items">+{{ count($cakupanArray) - 2 }} lainnya</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </td>
                                <td class="obat-cell text-left">
                                    @php
                                        $pivotObatIds = $pivot->obat_ids ?? [];
                                        $obats = ! empty($pivotObatIds) && $rl->obatRelasi ? $rl->obatRelasi->whereIn('id', $pivotObatIds) : collect();
                                    @endphp
                                    @if($obats->count() > 0)
                                        <div class="obat-list">
                                            @foreach($obats as $obat)
                                                <span class="badge-obat-mini" data-item="{{ strtolower($obat->nama) }}">{{ Str::limit($obat->nama, 25) }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </td>
                                <td class="status-cell text-center">
                                    @if($rl->status === 'aktif' && !$rl->is_expired)
                                        <span class="status-badge status-active">Aktif</span>
                                    @else
                                        <span class="status-badge status-expired">Expired</span>
                                    @endif
                                </td>
                                <td class="actions-cell text-center">
                                    <div class="action-buttons flex-col-center">
                                        <a href="{{ route('admin.ruang-lingkup.show', $rl) }}"
                                            title="Lihat Detail" class="action-btn view-btn no-underline inline-flex-center">
                                            <x-icon name="eye" class="" />
                                        </a>
                                        <a href="{{ route('admin.ruang-lingkup.edit', $rl) }}"
                                            title="Edit" class="action-btn edit-btn no-underline inline-flex-center">
                                            <x-icon name="edit" class="" />
                                        </a>
                                        <x-delete-modal
                                            action="{{ route('admin.ruang-lingkup.destroy', $rl) }}"
                                            label="{{ $rl->nama_asuransi }}"
                                            class="action-btn delete-btn"
                                            title="Hapus"
                                        >
                                            <x-icon name="trash" class="icon-14" />
                                        </x-delete-modal>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="15" class="empty-state">
                                    <div class="empty-icon">
                                        <x-icon name="document" class="" />
                                    </div>
                                    <div class="empty-text">
                                        <h3>Belum ada data kerjasama</h3>
                                        <p>Mulai dengan menambahkan kerjasama asuransi pertama Anda</p>
                                    </div>
                                    <a href="{{ route('admin.ruang-lingkup.create') }}" class="btn-primary no-underline inline-flex-center gap-2">
                                        <x-icon name="plus-circle-solid" class="" />
                                        Tambah Kerjasama
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="searchEmptyRow" class="d-none">
                            <td colspan="15" class="empty-state empty-state-compact">
                                <div class="empty-icon empty-icon-mb-1 text-muted-premium">
                                    <x-icon name="search-empty" class="empty-icon-large-svg" />
                                </div>
                                <div class="empty-text text-center">
                                    <h3 class="empty-title">Tidak ada data yang dicari</h3>
                                    <p class="empty-subtitle">Silakan ubah kata kunci pencarian atau sesuaikan filter Anda.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-container" id="paginationContainer">
            <div class="pagination-info" id="pageInfo">
                Menampilkan 0-0 dari 0 data
            </div>
            <div class="pagination-controls" id="paginationControls">
            </div>
        </div>
    </div>
    <script>
        /**
         * ==========================================
         * CORE STATE MANAGEMENT
         * ==========================================
         */
        let currentPage = 1;
        let itemsPerPage = 10;
        let allRows = [];
        let filteredRows = [];
        let sortColumn = '';
        let sortDirection = 'asc';
        let currentModalItem = null;
        let isGroupedMode = true;

        // Store all items data by RL ID for easy access
        const itemsDataById = {};
        @foreach(collect($ruangLingkup)->groupBy(fn($i) => $i['rl']->id) as $rlId => $group)
            @php $first = $group->first(); @endphp
            itemsDataById[{{ $rlId }}] = @json($first['rl']);
        @endforeach

        /**
         * ==========================================
         * AUTOCOMPLETE DATA PREPARATION
         * ==========================================
         */
        const asuransiList = @json(collect($ruangLingkup)->pluck('rl')->pluck('nama_asuransi')->unique()->values());
        let suggestions = new Set();
        let suggestionList = [];
        let benefitSuggestions = new Set();
        let cakupanSuggestions = new Set();

        const defaultBenefits = [
            'Obat-obatan', 'Obat Generik', 'Obat Resep Dokter', 'Rawat Inap', 'Rawat Jalan',
            'Rawat Darurat', 'Konsultasi Dokter', 'Konsultasi Spesialis', 'Pemeriksaan Laboratorium',
            'Pemeriksaan Radiologi', 'Operasi', 'Tindakan Medis', 'Rehabilitasi Medik',
            'Fisioterapi', 'Persalinan Normal', 'Persalinan Caesar', 'Perawatan Gigi',
            'Kacamata', 'Alat Bantu Kesehatan', 'Ambulans', 'Kamar Jenazah', 'Check Up Kesehatan',
            'Vaksinasi', 'Cuci Darah', 'Kemoterapi', 'Radioterapi'
        ];

        defaultBenefits.forEach(benefit => benefitSuggestions.add(benefit));

        @foreach($ruangLingkup as $item)
            @php
                $itemBenefitArray = $item['benefit'];
                $itemCakupanArray = $item['cakupan'];
            @endphp
            @foreach($itemBenefitArray as $benefit)
                @if(strpos(strtolower($benefit), 'biaya') === false)
                    benefitSuggestions.add(@json($benefit));
                @endif
            @endforeach
            @foreach($itemCakupanArray as $cakupan)
                cakupanSuggestions.add(@json($cakupan));
            @endforeach
        @endforeach

        let benefitSuggestionList = Array.from(benefitSuggestions).sort();
        let cakupanSuggestionList = Array.from(cakupanSuggestions).sort();

        /**
         * ==========================================
         * CLIENT-SIDE GROUPING
         * ==========================================
         */
        function updateGrouping() {
            const searchValue = document.getElementById('searchInput').value.trim();
            const bulanValue = document.getElementById('monthFilter').value;
            const tahunValue = document.getElementById('yearFilter').value;

            // Only per-BU filters trigger ungrouped (individual BU rows)
            const hasPerBuFilter = searchValue ||
                document.getElementById('namaAsuransiFilter')?.value ||
                document.getElementById('badanUsahaFilter')?.value ||
                document.getElementById('aliasTeraFilter')?.value;

            isGroupedMode = !hasPerBuFilter;

            // Collect all rows per RL
            const groups = {};
            allRows.forEach(function(row) {
                const rlId = row.getAttribute('data-rl-id');
                if (!groups[rlId]) groups[rlId] = [];
                groups[rlId].push(row);
            });

            // Update display based on mode
            allRows.forEach(function(row) {
                const rlId = row.getAttribute('data-rl-id');
                const group = groups[rlId];
                const isFirstInGroup = group && group[0] === row;

                if (isGroupedMode) {
                    // Show only first row of each group
                    row.style.display = isFirstInGroup ? '' : 'none';

                    // Update the first row's BU cell to show all BU names
                    if (isFirstInGroup) {
                        const buCell = row.querySelector('.bu-cell');
                        const singleBadge = buCell ? buCell.querySelector('.bu-single') : null;
                        const groupBadge = buCell ? buCell.querySelector('.bu-group-badge') : null;
                        if (singleBadge) singleBadge.style.display = 'none';
                        if (groupBadge) {
                            const allBuNames = group.map(function(r) {
                                return r.getAttribute('data-badan-usaha');
                            }).filter(function(n) { return n; }).map(function(n) {
                                return n.charAt(0).toUpperCase() + n.slice(1);
                            });
                            groupBadge.textContent = allBuNames.join(', ');
                            groupBadge.style.display = 'inline-block';
                        }
                    }
                } else {
                    // Show all rows (individual per BU)
                    row.style.display = '';
                    const buCell = row.querySelector('.bu-cell');
                    const singleBadge = buCell ? buCell.querySelector('.bu-single') : null;
                    const groupBadge = buCell ? buCell.querySelector('.bu-group-badge') : null;
                    if (singleBadge) {
                        singleBadge.textContent = row.getAttribute('data-badan-usaha')
                            ? row.getAttribute('data-badan-usaha').replace(/\b\w/g, function(c) { return c.toUpperCase(); })
                            : '-';
                        singleBadge.style.display = 'inline-block';
                    }
                    if (groupBadge) groupBadge.style.display = 'none';
                }
            });
        }

        /**
         * ==========================================
         * INITIALIZATION
         * ==========================================
         */
        document.addEventListener('DOMContentLoaded', function () {
            initializeData();
            setupEventListeners();
            setupActionButtonDelegation();
            buildAutocompleteData();
            setupBenefitCakupanAutocomplete();
            updateGrouping();
            updateDisplay();
        });

        // Event delegation for action buttons
        function setupActionButtonDelegation() {
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;

                const action = btn.getAttribute('data-action');
                const itemId = parseInt(btn.getAttribute('data-item-id'));
                const item = itemsDataById[itemId];

                if (!item) {
                    console.error('Item not found:', itemId);
                    return;
                }

                e.preventDefault();
                e.stopPropagation();

                if (action === 'edit') {
                    openEditModal(item);
                }
            });
        }

        function initializeData() {
            allRows = Array.from(document.querySelectorAll('.table-row'));
            filteredRows = [...allRows];
        }

        function setupEventListeners() {
            const searchInput = document.getElementById('searchInput');
            const clearSearchBtn = document.getElementById('clearSearch');
            const autocompleteList = document.getElementById('autocompleteList');

            searchInput.addEventListener('input', function() {
                debounce(filterTable, 300)();
                clearSearchBtn.style.display = this.value.length > 0 ? 'flex' : 'none';
                showAutocomplete(this.value, autocompleteList);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    clearSearchBtn.style.display = 'none';
                    autocompleteList.style.display = 'none';
                    filterTable();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    navigateAutocomplete('down');
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    navigateAutocomplete('up');
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    selectAutocompleteItem();
                }
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                    autocompleteList.style.display = 'none';
                }
            });

            setupDateValidation();
        }

        function buildAutocompleteData() {
            allRows.forEach(row => {
                const nama = row.getAttribute('data-nama');
                const layanan = row.getAttribute('data-layanan');
                const benefit = row.getAttribute('data-benefit');
                const cakupan = row.getAttribute('data-cakupan');
                const obat = row.getAttribute('data-obat');
                const diskon = row.getAttribute('data-diskon');
                const deskripsi = row.getAttribute('data-deskripsi');

                if (nama) suggestions.add(capitalizeWords(nama));
                if (layanan) layanan.split(' ').forEach(l => l.length > 2 && suggestions.add(capitalizeWords(l)));
                if (benefit) benefit.split(' ').forEach(b => b.length > 2 && suggestions.add(capitalizeWords(b)));
                if (cakupan) cakupan.split(' ').forEach(c => c.length > 2 && suggestions.add(capitalizeWords(c)));
                if (obat) obat.split(' ').forEach(o => o.length > 2 && suggestions.add(capitalizeWords(o)));
                if (diskon) diskon.split(' ').forEach(d => d.length > 1 && suggestions.add(capitalizeWords(d)));
                if (deskripsi) deskripsi.split(' ').forEach(d => d.length > 3 && suggestions.add(capitalizeWords(d)));
            });

            suggestionList = Array.from(suggestions).sort();
        }

        function capitalizeWords(str) {
            return str.split(' ').map(word =>
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        }

        let currentAutocompleteIndex = -1;

        function showAutocomplete(query, container) {
            if (!query || query.length < 2) {
                container.style.display = 'none';
                return;
            }

            const matches = suggestionList.filter(item =>
                item.toLowerCase().includes(query.toLowerCase())
            ).slice(0, 8);

            if (matches.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.innerHTML = matches.map((item, index) =>
                `<div class="autocomplete-item" data-index="${index}" onclick="selectAutocompleteValue('${item}')">${item}</div>`
            ).join('');

            container.style.display = 'block';
            currentAutocompleteIndex = -1;
        }

        function navigateAutocomplete(direction) {
            const items = document.querySelectorAll('.autocomplete-item');
            if (items.length === 0) return;

            if (currentAutocompleteIndex >= 0) {
                items[currentAutocompleteIndex].classList.remove('active');
            }

            if (direction === 'down') {
                currentAutocompleteIndex = (currentAutocompleteIndex + 1) % items.length;
            } else {
                currentAutocompleteIndex = currentAutocompleteIndex <= 0 ? items.length - 1 : currentAutocompleteIndex - 1;
            }

            items[currentAutocompleteIndex].classList.add('active');
        }

        function selectAutocompleteItem() {
            const activeItem = document.querySelector('.autocomplete-item.active');
            if (activeItem) {
                const value = activeItem.textContent;
                selectAutocompleteValue(value);
            }
        }

        function selectAutocompleteValue(value) {
            const searchInput = document.getElementById('searchInput');
            const autocompleteList = document.getElementById('autocompleteList');

            searchInput.value = value;
            autocompleteList.style.display = 'none';
            document.getElementById('clearSearch').style.display = 'flex';
            filterTable();
        }

        function setupBenefitCakupanAutocomplete() {
            setupFieldAutocomplete('add_benefit_container', benefitSuggestionList, 'benefit');
            setupFieldAutocomplete('add_cakupan_container', cakupanSuggestionList, 'cakupan');
        }

        function setupFieldAutocomplete(containerId, suggestionList, fieldType) {
            const container = document.getElementById(containerId);
            if (!container) return;

            container.addEventListener('input', function(e) {
                if (e.target.matches('input[name="' + fieldType + '[]"]')) {
                    showFieldAutocomplete(e.target, suggestionList, fieldType);
                }
            });

            container.addEventListener('keydown', function(e) {
                if (e.target.matches('input[name="' + fieldType + '[]"]')) {
                    const dropdown = e.target.nextElementSibling;
                    if (dropdown && dropdown.classList.contains('field-autocomplete-dropdown')) {
                        if (e.key === 'Escape') {
                            dropdown.style.display = 'none';
                        } else if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            navigateFieldAutocomplete(dropdown, 'down');
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            navigateFieldAutocomplete(dropdown, 'up');
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            const activeItem = dropdown.querySelector('.autocomplete-item.active');
                            if (activeItem) {
                                e.target.value = activeItem.textContent;
                                dropdown.style.display = 'none';
                            }
                        }
                    }
                }
            });

            document.addEventListener('click', function(e) {
                if (!e.target.matches('input[name="' + fieldType + '[]"]')) {
                    const dropdowns = container.querySelectorAll('.field-autocomplete-dropdown');
                    dropdowns.forEach(dropdown => dropdown.style.display = 'none');
                }
            });
        }

        function showFieldAutocomplete(inputElement, suggestionList, fieldType) {
            const query = inputElement.value;

            let dropdown = inputElement.nextElementSibling;
            if (dropdown && dropdown.classList.contains('field-autocomplete-dropdown')) {
                dropdown.remove();
            }

            if (!query || query.length < 2) {
                return;
            }

            const matches = suggestionList.filter(item =>
                item.toLowerCase().includes(query.toLowerCase())
            ).slice(0, 8);

            if (matches.length === 0) {
                return;
            }

            dropdown = document.createElement('div');
            dropdown.className = 'field-autocomplete-dropdown';
            dropdown.innerHTML = matches.map((item, index) =>
                `<div class="autocomplete-item" data-index="${index}">${item}</div>`
            ).join('');

            inputElement.parentNode.insertBefore(dropdown, inputElement.nextSibling);

            dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
                item.addEventListener('click', function() {
                    inputElement.value = this.textContent;
                    dropdown.style.display = 'none';
                });
            });

            dropdown.style.display = 'block';
        }

        function navigateFieldAutocomplete(dropdown, direction) {
            const items = dropdown.querySelectorAll('.autocomplete-item');
            if (items.length === 0) return;

            let currentIndex = -1;
            items.forEach((item, index) => {
                if (item.classList.contains('active')) {
                    currentIndex = index;
                    item.classList.remove('active');
                }
            });

            if (direction === 'down') {
                currentIndex = (currentIndex + 1) % items.length;
            } else {
                currentIndex = currentIndex <= 0 ? items.length - 1 : currentIndex - 1;
            }

            items[currentIndex].classList.add('active');
        }

        // Sorting
        let currentSort = { column: '', direction: 'asc' };

        function sortTable(column) {
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }

            updateSortIcons(column, currentSort.direction);

            filteredRows.sort((a, b) => {
                let aValue, bValue;

                switch (column) {
                    case 'nama_asuransi':
                        aValue = a.getAttribute('data-nama').toLowerCase();
                        bValue = b.getAttribute('data-nama').toLowerCase();
                        break;
                    case 'status':
                        aValue = a.getAttribute('data-status');
                        bValue = b.getAttribute('data-status');
                        break;
                    case 'periode':
                        aValue = new Date(a.getAttribute('data-tanggal-mulai'));
                        bValue = new Date(b.getAttribute('data-tanggal-mulai'));
                        break;
                    default:
                        return 0;
                }

                if (aValue < bValue) {
                    return currentSort.direction === 'asc' ? -1 : 1;
                } else if (aValue > bValue) {
                    return currentSort.direction === 'asc' ? 1 : -1;
                } else {
                    return 0;
                }
            });

            const tbody = document.querySelector('#tableView tbody');
            filteredRows.forEach(row => tbody.appendChild(row));

            currentPage = 1;
            updateDisplay();
        }

        function updateSortIcons(activeColumn, direction) {
            document.querySelectorAll('.sort-icon').forEach(icon => {
                icon.style.opacity = '0.5';
                icon.style.transform = 'rotate(0deg)';
            });

            const activeHeader = document.querySelector(`th[onclick="sortTable('${activeColumn}')"] .sort-icon`);
            if (activeHeader) {
                activeHeader.style.opacity = '1';
                activeHeader.style.transform = direction === 'desc' ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        /**
         * ==========================================
         * FILTERING & SEARCH LOGIC
         * ==========================================
         */
        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('statusFilter').value;
            const periodValue = document.getElementById('periodFilter').value;
            const serviceValue = document.getElementById('serviceFilter') ? document.getElementById('serviceFilter').value : '';
            const obatValue = document.getElementById('obatFilter') ? document.getElementById('obatFilter').value : '';
            const monthValue = document.getElementById('monthFilter').value;
            const yearValue = document.getElementById('yearFilter').value;
            const dateRangeValue = document.getElementById('dateRangeFilter')?.value || '';
            const payorTpaValue = document.getElementById('payorTpaFilter') ? document.getElementById('payorTpaFilter').value : '';
            const highProfileValue = document.getElementById('highProfileFilter') ? document.getElementById('highProfileFilter').value : '';
            const badanUsahaValue = document.getElementById('badanUsahaFilter') ? document.getElementById('badanUsahaFilter').value.toLowerCase() : '';
            const jangkaWaktuValue = document.getElementById('jangkaWaktuFilter') ? document.getElementById('jangkaWaktuFilter').value : '';
            const namaAsuransiValue = document.getElementById('namaAsuransiFilter') ? document.getElementById('namaAsuransiFilter').value.toLowerCase() : '';
            const aliasTeraValue = document.getElementById('aliasTeraFilter') ? document.getElementById('aliasTeraFilter').value.toLowerCase() : '';
            const diskonValue = document.getElementById('diskonFilter') ? document.getElementById('diskonFilter').value.toLowerCase() : '';

            toggleClearButton('clearStatus', statusValue);
            toggleClearButton('clearPeriod', periodValue);
            toggleClearButton('clearService', serviceValue);
            toggleClearButton('clearMonth', monthValue);
            toggleClearButton('clearYear', yearValue);
            toggleClearButton('clearDateRange', dateRangeValue);
            toggleClearButton('clearPayorTpa', payorTpaValue);
            toggleClearButton('clearHighProfile', highProfileValue);
            if(document.getElementById('clearBadanUsaha')) toggleClearButton('clearBadanUsaha', badanUsahaValue);
            if(document.getElementById('clearJangkaWaktu')) toggleClearButton('clearJangkaWaktu', jangkaWaktuValue);

            filteredRows = allRows.filter(row => {
                const nama = row.getAttribute('data-nama') || '';
                const aliasTera = row.getAttribute('data-alias-tera') || '';
                const layanan = row.getAttribute('data-layanan') || '';
                const obatData = row.getAttribute('data-obat') || '';
                const benefit = row.getAttribute('data-benefit') || '';
                const cakupan = row.getAttribute('data-cakupan') || '';
                const obat = row.getAttribute('data-obat') || '';
                const diskon = row.getAttribute('data-diskon') || '';
                const deskripsi = row.getAttribute('data-deskripsi') || '';
                const status = row.getAttribute('data-status') || '';
                const penjaminCategory = row.getAttribute('data-penjamin-category') || '';
                const isHighProfile = row.getAttribute('data-high-profile') || '0';
                const badanUsaha = row.getAttribute('data-badan-usaha') || '';
                const jangkaWaktu = row.getAttribute('data-jangka-waktu') || '';

                const parseLocalDate = (str) => {
                    if (!str) return null;
                    const parts = str.split('-');
                    if (parts.length < 3) return null;
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    return isNaN(d.getTime()) ? null : d;
                };
                const tanggalMulai    = parseLocalDate(row.getAttribute('data-tanggal-mulai'));
                const tanggalBerakhir = parseLocalDate(row.getAttribute('data-tanggal-berakhir'));
                const now = new Date();

                let showRow = true;

                if (namaAsuransiValue && !nama.includes(namaAsuransiValue)) showRow = false;
                if (aliasTeraValue && !aliasTera.includes(aliasTeraValue)) showRow = false;
                if (diskonValue && !diskon.includes(diskonValue)) showRow = false;

                if (searchValue) {
                    const searchMatch = nama.includes(searchValue) ||
                        layanan.includes(searchValue) ||
                        benefit.includes(searchValue) ||
                        cakupan.includes(searchValue) ||
                        obat.includes(searchValue) ||
                        diskon.includes(searchValue) ||
                        deskripsi.includes(searchValue) ||
                        badanUsaha.toLowerCase().includes(searchValue);
                    if (!searchMatch) showRow = false;
                }

                if (statusValue && status !== statusValue) showRow = false;
                if (payorTpaValue && penjaminCategory !== payorTpaValue) showRow = false;
                if (highProfileValue && isHighProfile !== highProfileValue) showRow = false;
                if (badanUsahaValue && !badanUsaha.toLowerCase().includes(badanUsahaValue)) showRow = false;

                if (jangkaWaktuValue) {
                    const jwArray = jangkaWaktu.split(',');
                    if (!jwArray.includes(jangkaWaktuValue)) showRow = false;
                }

                if (periodValue && tanggalBerakhir) {
                    const daysDiff = Math.floor((tanggalBerakhir - now) / (1000 * 60 * 60 * 24));
                    switch (periodValue) {
                        case 'active':
                            if (status !== 'aktif' || daysDiff <= 0) showRow = false;
                            break;
                        case 'expiring30':
                            if (status !== 'aktif' || daysDiff > 30 || daysDiff <= 0) showRow = false;
                            break;
                        case 'expiring90':
                            if (status !== 'aktif' || daysDiff > 90 || daysDiff <= 0) showRow = false;
                            break;
                        case 'expired':
                            if (daysDiff > 0) showRow = false;
                            break;
                    }
                }

                if (serviceValue) {
                    const serviceMatch = layanan.toLowerCase().includes(serviceValue.toLowerCase());
                    if (!serviceMatch) showRow = false;
                }

                if (obatValue) {
                    const obatMatch = obatData.toLowerCase().includes(obatValue.toLowerCase());
                    if (!obatMatch) showRow = false;
                }

                if (monthValue && tanggalMulai && tanggalBerakhir) {
                    const selectedMonthNum = parseInt(monthValue);
                    const startMonthNum = tanggalMulai.getMonth() + 1;
                    const endMonthNum   = tanggalBerakhir.getMonth() + 1;
                    const startYear     = tanggalMulai.getFullYear();
                    const endYear       = tanggalBerakhir.getFullYear();

                    let monthMatches = false;
                    if (startYear === endYear) {
                        monthMatches = selectedMonthNum >= startMonthNum && selectedMonthNum <= endMonthNum;
                    } else if (endYear - startYear > 1) {
                        monthMatches = true;
                    } else {
                        monthMatches = selectedMonthNum >= startMonthNum || selectedMonthNum <= endMonthNum;
                    }

                    if (!monthMatches) showRow = false;
                }

                if (yearValue && tanggalMulai && tanggalBerakhir) {
                    const startYear    = tanggalMulai.getFullYear();
                    const endYear      = tanggalBerakhir.getFullYear();
                    const selectedYear = parseInt(yearValue);
                    if (!(selectedYear >= startYear && selectedYear <= endYear)) showRow = false;
                }

                if (dateRangeValue) {
                    const dateRangeParts = dateRangeValue.split(' to ');

                    if (dateRangeParts.length === 2) {
                        const startParts = dateRangeParts[0].split('/');
                        const filterStartDate = new Date(startParts[2], startParts[1] - 1, startParts[0]);
                        const endParts = dateRangeParts[1].split('/');
                        const filterEndDate = new Date(endParts[2], endParts[1] - 1, endParts[0]);

                        let dateRangeMatches = true;
                        if (tanggalBerakhir < filterStartDate || tanggalMulai > filterEndDate) {
                            dateRangeMatches = false;
                        }

                        if (!dateRangeMatches) showRow = false;
                    } else if (dateRangeParts.length === 1) {
                        const dateParts = dateRangeParts[0].split('/');
                        const filterDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
                        if (tanggalMulai > filterDate || tanggalBerakhir < filterDate) {
                            showRow = false;
                        }
                    }
                }

                return showRow;
            });

            currentPage = 1;
            updateGrouping();
            updateDisplay();
            updateTableSubtitle();
        }

        function toggleClearButton(buttonId, value) {
            const button = document.getElementById(buttonId);
            if (button) {
                if (value) {
                    button.classList.remove('hidden');
                    button.style.display = 'flex';
                } else {
                    button.classList.add('hidden');
                    button.style.display = 'none';
                }
            }
        }

        function updateTableSubtitle() {
            const subtitle = document.getElementById('tableSubtitle');
            if (!subtitle) return;
            const total = filteredRows.length;
            const allTotal = allRows.length;
            const obatVal = document.getElementById('obatFilter')?.value?.trim();
            const serviceVal = document.getElementById('serviceFilter')?.value?.trim();

            if (total === allTotal && !obatVal && !serviceVal) {
                subtitle.textContent = 'Menampilkan semua data kerjasama';
            } else {
                let parts = [`Menampilkan ${total} dari ${allTotal} data`];
                if (obatVal) {
                    const usingObat = allRows.filter(r =>
                        (r.getAttribute('data-obat') || '').toLowerCase().includes(obatVal.toLowerCase())
                    ).length;
                    parts.push(` dengan obat "${obatVal}" (${usingObat} record)`);
                }
                if (serviceVal) {
                    parts.push(` layanan "${serviceVal}"`);
                }
                subtitle.textContent = parts.join('');
            }
        }

        function openTimeFilterModal() {
            const modal = document.getElementById('timeFilterModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeTimeFilterModal() {
            const modal = document.getElementById('timeFilterModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function toggleTimeFilter(type) {
            const checkbox = document.getElementById(`enable${type.charAt(0).toUpperCase() + type.slice(1)}Filter`);
            const container = document.getElementById(`${type}FilterContainer`);

            if (checkbox.checked) {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
                if (type === 'period') {
                    document.getElementById('periodFilter').value = '';
                    toggleClearButton('clearPeriod', '');
                } else if (type === 'dateRange') {
                    const dateRangeFilter = document.getElementById('dateRangeFilter');
                    if (dateRangeFilter._flatpickr) {
                        dateRangeFilter._flatpickr.clear();
                    }
                    toggleClearButton('clearDateRange', '');
                } else if (type === 'month') {
                    document.getElementById('monthFilter').value = '';
                    toggleClearButton('clearMonth', '');
                } else if (type === 'year') {
                    document.getElementById('yearFilter').value = '';
                    toggleClearButton('clearYear', '');
                }
            }

            filterTable();
        }

        function clearAllTimeFilters() {
            document.querySelectorAll('input[type="checkbox"][id*="enable"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            document.getElementById('periodFilterContainer').style.display = 'none';
            document.getElementById('dateRangeFilterContainer').style.display = 'none';
            document.getElementById('monthFilterContainer').style.display = 'none';
            document.getElementById('yearFilterContainer').style.display = 'none';

            document.getElementById('periodFilter').value = '';
            document.getElementById('monthFilter').value = '';
            document.getElementById('yearFilter').value = '';

            const dateRangeFilter = document.getElementById('dateRangeFilter');
            if (dateRangeFilter._flatpickr) {
                dateRangeFilter._flatpickr.clear();
            }

            toggleClearButton('clearPeriod', '');
            toggleClearButton('clearDateRange', '');
            toggleClearButton('clearMonth', '');
            toggleClearButton('clearYear', '');

            filterTable();
        }

        function clearStatusFilter() {
            document.getElementById('statusFilter').value = '';
            toggleClearButton('clearStatus', '');
            filterTable();
        }

        function clearPeriodFilter() {
            document.getElementById('periodFilter').value = '';
            toggleClearButton('clearPeriod', '');
            filterTable();
        }

        function clearDateRangeFilter() {
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            if (dateRangeFilter._flatpickr) {
                dateRangeFilter._flatpickr.clear();
            }
            dateRangeFilter.value = '';
            toggleClearButton('clearDateRange', '');
            filterTable();
        }

        function clearServiceFilter() {
            document.getElementById('serviceFilter').value = '';
            toggleClearButton('clearService', '');
            filterTable();
        }

        function clearPayorTpaFilter() {
            document.getElementById('payorTpaFilter').value = '';
            toggleClearButton('clearPayorTpa', '');
            filterTable();
        }

        function clearHighProfileFilter() {
            document.getElementById('highProfileFilter').value = '';
            toggleClearButton('clearHighProfile', '');
            filterTable();
        }

        function clearMonthFilter() {
            document.getElementById('monthFilter').value = '';
            toggleClearButton('clearMonth', '');
            filterTable();
        }

        function clearYearFilter() {
            document.getElementById('yearFilter').value = '';
            toggleClearButton('clearYear', '');
            filterTable();
        }

        function updateDisplay() {
            updateTableView();
            updatePagination();
        }

        function updateTableView() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            allRows.forEach(row => row.style.display = 'none');

            let visibleCount = 0;
            filteredRows.slice(start, end).forEach((row, index) => {
                if (isGroupedMode) {
                    const rlId = row.getAttribute('data-rl-id');
                    const isFirstInGroup = filteredRows.find(function(r) {
                        return r.getAttribute('data-rl-id') === rlId;
                    }) === row;
                    if (!isFirstInGroup) return;
                }
                row.style.display = '';
                visibleCount++;
                const rowNumber = start + visibleCount;
                const numberCell = row.querySelector('.row-number');
                if (numberCell) {
                    numberCell.textContent = rowNumber;
                }
            });

            const searchEmptyRow = document.getElementById('searchEmptyRow');
            if (searchEmptyRow) {
                if (allRows.length > 0 && filteredRows.length === 0) {
                    searchEmptyRow.style.display = '';
                } else {
                    searchEmptyRow.style.display = 'none';
                }
            }

            applyCellItemFilters();
            truncateServiceBadges();
        }

        function truncateServiceBadges() {
            const svc = document.getElementById('serviceFilter');
            const serviceVal = svc ? svc.value.trim() : '';

            document.querySelectorAll('#tableBody .table-row').forEach(row => {
                if (row.style.display === 'none') return;

                const container = row.querySelector('.service-badges');
                if (!container) return;

                const badges = container.querySelectorAll('.badge-layanan-khusus');
                const total = badges.length;

                let oldMore = container.querySelector('.more-services');
                if (oldMore) oldMore.remove();

                badges.forEach(b => {
                    if (b.style.display !== 'none') b.style.display = '';
                });

                if (serviceVal) return;

                if (total > 3) {
                    const showCount = Math.ceil(total / 2);
                    badges.forEach((b, i) => {
                        b.style.display = i < showCount ? '' : 'none';
                    });
                    const span = document.createElement('span');
                    span.className = 'more-services';
                    span.textContent = `+${total - showCount} lainnya`;
                    container.appendChild(span);
                }
            });
        }

        function applyCellItemFilters() {
            const serviceInp = document.getElementById('serviceFilter');
            const obatInp = document.getElementById('obatFilter');
            const serviceVal = serviceInp ? serviceInp.value.toLowerCase().trim() : '';
            const obatVal = obatInp ? obatInp.value.toLowerCase().trim() : '';

            document.querySelectorAll('#tableBody .table-row').forEach(row => {
                if (row.style.display === 'none') return;

                const layananBadges = row.querySelectorAll('.badge-layanan-khusus');
                if (serviceVal) {
                    layananBadges.forEach(b => {
                        const item = (b.getAttribute('data-item') || '').toLowerCase();
                        const match = item.includes(serviceVal);
                        b.style.display = match ? '' : 'none';
                        b.classList.toggle('badge-filter-match', match);
                    });
                } else {
                    layananBadges.forEach(b => {
                        b.style.display = '';
                        b.classList.remove('badge-filter-match');
                    });
                }

                const obatBadges = row.querySelectorAll('.badge-obat-mini');
                if (obatVal) {
                    obatBadges.forEach(b => {
                        const item = (b.getAttribute('data-item') || '').toLowerCase();
                        const match = item.includes(obatVal);
                        b.style.display = match ? '' : 'none';
                        b.classList.toggle('badge-filter-match', match);
                    });
                } else {
                    obatBadges.forEach(b => {
                        b.style.display = '';
                        b.classList.remove('badge-filter-match');
                    });
                }
            });
        }

        function updatePagination() {
            let totalVisibleInGroupedMode = 0;
            if (isGroupedMode) {
                const seen = {};
                filteredRows.forEach(function(row) {
                    const rlId = row.getAttribute('data-rl-id');
                    if (!seen[rlId]) {
                        seen[rlId] = true;
                        totalVisibleInGroupedMode++;
                    }
                });
            }
            const totalItems = isGroupedMode ? totalVisibleInGroupedMode : filteredRows.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const start = (currentPage - 1) * itemsPerPage;
            const end = Math.min(start + itemsPerPage, totalItems);

            const pageInfo = document.getElementById('pageInfo');
            if (totalItems > 0) {
                pageInfo.textContent = `Menampilkan ${start + 1}-${end} dari ${totalItems} data`;
            } else {
                pageInfo.textContent = 'Menampilkan 0-0 dari 0 data';
            }

            renderPaginationButtons(totalPages);

            const paginationContainer = document.getElementById('paginationContainer');
            if (totalItems === 0) {
                paginationContainer.classList.add('hidden');
                paginationContainer.style.display = 'none';
            } else {
                paginationContainer.classList.remove('hidden');
                paginationContainer.style.display = 'flex';
            }
        }

        function renderPaginationButtons(totalPages) {
            const container = document.getElementById('paginationControls');
            let html = '';

            html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M15 19l-7-7 7-7"/></svg>
                     </button>`;

            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);

            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }

            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M9 5l7 7-7 7"/></svg>
                     </button>`;

            container.innerHTML = html;
        }

        function changePage(direction) {
            let totalVisible = filteredRows.length;
            if (isGroupedMode) {
                const seen = {};
                filteredRows.forEach(function(row) {
                    const rlId = row.getAttribute('data-rl-id');
                    if (!seen[rlId]) {
                        seen[rlId] = true;
                        totalVisible++;
                    }
                });
            }
            const totalPages = Math.ceil(totalVisible / itemsPerPage);
            const newPage = currentPage + direction;

            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                updateDisplay();
            }
        }

        function goToPage(page) {
            let totalVisible = filteredRows.length;
            if (isGroupedMode) {
                const seen = {};
                filteredRows.forEach(function(row) {
                    const rlId = row.getAttribute('data-rl-id');
                    if (!seen[rlId]) {
                        seen[rlId] = true;
                        totalVisible++;
                    }
                });
            }
            const totalPages = Math.ceil(totalVisible / itemsPerPage);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                updateDisplay();
            }
        }

        function changeItemsPerPage() {
            itemsPerPage = parseInt(document.getElementById('itemsPerPageSelect').value);
            currentPage = 1;
            updateDisplay();
        }

        function clearSearchInput() {
            document.getElementById('searchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            filterTable();
        }

        function clearBadanUsahaFilter() {
            if(document.getElementById('badanUsahaFilter')) document.getElementById('badanUsahaFilter').value = '';
            toggleClearButton('clearBadanUsaha', '');
            filterTable();
        }

        function clearJangkaWaktuFilter() {
            if(document.getElementById('jangkaWaktuFilter')) document.getElementById('jangkaWaktuFilter').value = '';
            toggleClearButton('clearJangkaWaktu', '');
            filterTable();
        }

        function resetAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            if(document.getElementById('serviceFilter')) document.getElementById('serviceFilter').value = '';
            if(document.getElementById('payorTpaFilter')) document.getElementById('payorTpaFilter').value = '';
            if(document.getElementById('highProfileFilter')) document.getElementById('highProfileFilter').value = '';
            if(document.getElementById('badanUsahaFilter')) document.getElementById('badanUsahaFilter').value = '';
            if(document.getElementById('jangkaWaktuFilter')) document.getElementById('jangkaWaktuFilter').value = '';
            if(document.getElementById('namaAsuransiFilter')) document.getElementById('namaAsuransiFilter').value = '';
            if(document.getElementById('aliasTeraFilter')) document.getElementById('aliasTeraFilter').value = '';
            if(document.getElementById('diskonFilter')) document.getElementById('diskonFilter').value = '';
            if(document.getElementById('obatFilter')) document.getElementById('obatFilter').value = '';

            document.getElementById('clearSearch').style.display = 'none';
            const autocompleteList = document.getElementById('autocompleteList');
            if (autocompleteList) autocompleteList.style.display = 'none';

            document.getElementById('periodFilter').value = '';
            document.getElementById('monthFilter').value = '';
            document.getElementById('yearFilter').value = '';

            const dateRangeFilter = document.getElementById('dateRangeFilter');
            if (dateRangeFilter && dateRangeFilter._flatpickr) {
                dateRangeFilter._flatpickr.clear();
            }

            toggleClearButton('clearStatus', '');
            toggleClearButton('clearService', '');
            toggleClearButton('clearPayorTpa', '');
            toggleClearButton('clearHighProfile', '');
            toggleClearButton('clearPeriod', '');
            toggleClearButton('clearDateRange', '');
            toggleClearButton('clearMonth', '');
            toggleClearButton('clearYear', '');
            if(document.getElementById('clearBadanUsaha')) toggleClearButton('clearBadanUsaha', '');
            if(document.getElementById('clearJangkaWaktu')) toggleClearButton('clearJangkaWaktu', '');

            filterTable();
        }

        function setupDateValidation() {
            const addStartDate = document.getElementById('add_tanggal_mulai');
            const addEndDate = document.getElementById('add_tanggal_berakhir');

            if (addStartDate && addEndDate) {
                addStartDate.addEventListener('change', function () {
                    addEndDate.min = this.value;
                });

                addEndDate.addEventListener('change', function () {
                    if (addStartDate.value && this.value < addStartDate.value) {
                        alert('Tanggal berakhir harus sama atau setelah tanggal mulai');
                        this.value = '';
                    }
                });
            }
        }

        function handleFilterInput(el) {
            const wrap = document.getElementById('wrap-' + el.id);
            if (wrap) {
                const btn = wrap.querySelector('.clear-filter-btn');
                if (btn) btn.style.display = el.value ? 'flex' : 'none';
            }
            filterTable();
        }

        function toggleExportDropdown(e) {
            e.stopPropagation();
            const menu = document.getElementById('exportDropdown');
            const isOpen = menu.style.display === 'block';
            document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
            menu.style.display = isOpen ? 'none' : 'block';
            const container = document.querySelector('.data-table-container');
            if (container) {
                container.style.overflow = isOpen ? 'hidden' : 'visible';
            }
        }
        document.addEventListener('click', function () {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
            const container = document.querySelector('.data-table-container');
            if (container) container.style.overflow = 'hidden';
        });

        function clearFilterWrap(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = '';
            const wrap = document.getElementById('wrap-' + id);
            if (wrap) {
                const btn = wrap.querySelector('.clear-filter-btn');
                if (btn) btn.style.display = 'none';
            }
            filterTable();
        }
    </script>
</x-app-layout>
