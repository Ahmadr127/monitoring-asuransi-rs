<x-user-layout>
    <x-slot name="title">Daftar Kerjasama Asuransi</x-slot>

    @php
        $pageTitle = 'Daftar Kerjasama Asuransi';
        $pageDescription = 'Lihat detail kerjasama asuransi, benefit, dan cakupan layanan yang tersedia';
        $totalCols = 2 + count($visibleColumns);
    @endphp

    @push('navbar-meta')
    <script>
        (function() {
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
                        <x-icon name="search" class="icon-sm" />
                    </div>
                    <input type="text" id="searchInput"
                        placeholder="Cari asuransi, layanan, benefit, cakupan..." onkeyup="filterTable()">
                    <button id="clearSearch" onclick="clearSearchInput()" class="clear-search-btn">
                        <x-icon name="close" class="icon-sm" />
                    </button>
                    <div id="autocompleteList" class="autocomplete-dropdown"></div>
                </div>
                <div class="header-actions">
                    <a href="{{ route('user.kerjasama.export-csv') }}" class="btn-secondary btn-sm btn-export-csv">
                        Export CSV
                    </a>
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
                                <x-icon name="close" class="icon-sm" />
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
                                <x-icon name="close" class="icon-sm" />
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
                                    <input type="text" id="namaAsuransiFilter" list="namaAsuransiList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('namaAsuransiFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                                <datalist id="namaAsuransiList">
                                    @php $namaList = collect($ruangLingkup)->pluck('rl')->pluck('nama_asuransi')->filter()->unique()->sort(); @endphp
                                    @foreach($namaList as $nama)<option value="{{ $nama }}">@endforeach
                                </datalist>
                            </th>
                            @if(in_array('alias_versi_tera', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-aliasTeraFilter">
                                    <input type="text" id="aliasTeraFilter" list="aliasTeraList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('aliasTeraFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                                <datalist id="aliasTeraList">
                                    @php $aliasList = collect($ruangLingkup)->pluck('rl')->pluck('alias_versi_tera')->filter()->unique()->sort(); @endphp
                                    @foreach($aliasList as $alias)<option value="{{ $alias }}">@endforeach
                                </datalist>
                            </th>
                            @endif
                            @if(in_array('penjamin_category', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-payorTpaFilter">
                                    <select id="payorTpaFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        @php $kategoriList = collect($ruangLingkup)->pluck('rl')->pluck('penjamin_category')->filter()->unique()->sort(); @endphp
                                        @foreach($kategoriList as $kategori)<option value="{{ $kategori }}">{{ $kategori }}</option>@endforeach
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('payorTpaFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                            </th>
                            @endif
                            @if(in_array('is_high_profile', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-highProfileFilter">
                                    <select id="highProfileFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="1">High</option>
                                        <option value="0">Reguler</option>
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('highProfileFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                            </th>
                            @endif
                            @if(in_array('badan_usaha', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-badanUsahaFilter">
                                    <input type="text" id="badanUsahaFilter" list="badanUsahaList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari BU...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('badanUsahaFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                                <datalist id="badanUsahaList">
                                    @php $bus = collect($ruangLingkup)->pluck('bu_nama')->filter()->unique()->sort(); @endphp
                                    @foreach($bus as $bu)<option value="{{ htmlspecialchars($bu) }}">@endforeach
                                </datalist>
                            </th>
                            @endif
                            @if(in_array('periode', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-periodFilter">
                                    <select id="periodFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="active">Jalan</option>
                                        <option value="expiring30">≤ 30 H</option>
                                        <option value="expiring90">≤ 90 H</option>
                                        <option value="expired">Habis</option>
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('periodFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                            </th>
                            @endif
                            @if(in_array('jangka_waktu', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-jangkaWaktuFilter">
                                    <select id="jangkaWaktuFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        @php
                                            $jangkaWaktuList = collect($ruangLingkup)->pluck('rl')->flatMap(function($item) {
                                                return $item->penagihanPembayaran ? $item->penagihanPembayaran->map(fn($p) => [
                                                    'key'   => $p->jangka_waktu_hari . '|' . $p->tipe_hari,
                                                    'label' => $p->jangka_waktu_hari . ' Hari ' . $p->tipe_hari,
                                                ]) : collect();
                                            })->filter()->unique('key')->sortBy('label');
                                        @endphp
                                        @foreach($jangkaWaktuList as $jw)
                                            <option value="{{ $jw['key'] }}">{{ $jw['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('jangkaWaktuFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                            </th>
                            @endif
                            @if(in_array('diskon', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-diskonFilter">
                                    <input type="text" id="diskonFilter" oninput="handleFilterInput(this)" autocomplete="off" class="filter-input-pure" placeholder="Cari diskon...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('diskonFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                            </th>
                            @endif
                            @if(in_array('jenis_layanan', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-serviceFilter">
                                    <input type="text" id="serviceFilter" list="serviceList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari layanan...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('serviceFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                                <datalist id="serviceList">
                                    @php
                                        $semuaLayanan = collect($ruangLingkup)->pluck('rl')->flatMap(function($item) {
                                            $services = collect();
                                            if($item->jenisLayananRelasi && $item->jenisLayananRelasi->count() > 0) {
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
                            @endif
                            @if(in_array('benefit', $visibleColumns))<th></th>@endif
                            @if(in_array('cakupan', $visibleColumns))<th></th>@endif
                            @if(in_array('obat', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-obatFilter">
                                    <input type="text" id="obatFilter" list="obatList" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari obat...">
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('obatFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                                <datalist id="obatList">
                                    @php
                                        $semuaObat = collect($ruangLingkup)->pluck('rl')->flatMap(function($item) {
                                            return $item->obatRelasi->pluck('nama');
                                        })->filter()->unique()->sort();
                                    @endphp
                                    @foreach($semuaObat as $namaObat)
                                        <option value="{{ $namaObat }}">
                                    @endforeach
                                </datalist>
                            </th>
                            @endif
                            @if(in_array('status', $visibleColumns))
                            <th>
                                <div class="filter-input-container" id="wrap-statusFilter">
                                    <select id="statusFilter" onchange="handleFilterInput(this)" class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="aktif">Aktif</option>
                                        <option value="tidak_aktif">Non-Aktif</option>
                                    </select>
                                    <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('statusFilter')" title="Clear"><x-icon name="close" class="icon-sm" /></button>
                                </div>
                            </th>
                            @endif
                            <th></th>
                        </tr>
                        <tr class="filter-spacer-row">
                            <th colspan="{{ $totalCols }}"></th>
                        </tr>
                        <tr>
                            <th class="text-center w-40 th-min-50">No</th>
                            <th class="sortable text-left th-min-250" onclick="sortTable('nama_asuransi')">
                                <span>Nama Asuransi</span>
                                <x-icon name="sort" class="sort-icon" />
                            </th>
                            @if(in_array('alias_versi_tera', $visibleColumns))
                            <th class="text-left w-120 th-min-140">Alias Versi Tera</th>
                            @endif
                            @if(in_array('penjamin_category', $visibleColumns))
                            <th class="text-center w-100 th-min-110">TPA / PAYOR</th>
                            @endif
                            @if(in_array('is_high_profile', $visibleColumns))
                            <th class="text-center w-80 th-min-90">High Profile</th>
                            @endif
                            @if(in_array('badan_usaha', $visibleColumns))
                            <th class="bu-cell text-left th-min-200">Badan Usaha</th>
                            @endif
                            @if(in_array('periode', $visibleColumns))
                            <th class="sortable period-cell text-left th-min-220" onclick="sortTable('periode')">
                                <span>Periode</span>
                                <x-icon name="sort" class="sort-icon" />
                            </th>
                            @endif
                            @if(in_array('jangka_waktu', $visibleColumns))
                            <th class="text-center w-120 th-min-130">Jangka Waktu</th>
                            @endif
                            @if(in_array('diskon', $visibleColumns))
                            <th class="text-center w-50 th-min-60">Diskon</th>
                            @endif
                            @if(in_array('jenis_layanan', $visibleColumns))
                            <th class="services-cell text-left th-min-200">Jenis Layanan</th>
                            @endif
                            @if(in_array('benefit', $visibleColumns))
                            <th class="benefits-cell text-left th-min-200">Benefit</th>
                            @endif
                            @if(in_array('cakupan', $visibleColumns))
                            <th class="coverage-cell text-left th-min-200">Cakupan</th>
                            @endif
                            @if(in_array('obat', $visibleColumns))
                            <th class="obat-cell text-left th-min-200">Obat</th>
                            @endif
                            @if(in_array('status', $visibleColumns))
                            <th class="sortable text-center w-90 th-min-100" onclick="sortTable('status')">
                                <span>Status</span>
                                <x-icon name="sort" class="sort-icon" />
                            </th>
                            @endif
                            <th class="actions-header aksi-cell text-center w-80 th-min-90">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($ruangLingkup as $index => $item)
                            @php
                                $rl = $item['rl'];
                                $pivot = $item['pivot'];
                                $benefitArray = $item['benefit'];
                                $cakupanArray = $item['cakupan'];
                                $buName = $item['bu_nama'];

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

                                $allBuNames = collect($ruangLingkup)->where('rl.id', $rl->id)->pluck('bu_nama')->filter()->unique();
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
                                data-badge-status="{{ $rl->is_expired ? 'tidak_aktif' : $rl->status }}"
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

                                @if(in_array('alias_versi_tera', $visibleColumns))
                                <td class="text-left text-sm-semibold color-indigo">
                                    @if($rl->alias_versi_tera)
                                        <span class="badge badge-indigo text-xs-bold inline-flex-center letter-spacing-tight">
                                            {{ $rl->alias_versi_tera }}
                                        </span>
                                    @else
                                        <span class="color-muted">-</span>
                                    @endif
                                </td>
                                @endif

                                @if(in_array('penjamin_category', $visibleColumns))
                                <td class="text-center">
                                    <span class="status-badge status-info text-xs-bold min-w-60">
                                        {{ $rl->penjamin_category ?? '-' }}
                                    </span>
                                </td>
                                @endif

                                @if(in_array('is_high_profile', $visibleColumns))
                                <td class="text-center">
                                    @if($rl->is_high_profile)
                                        <span class="status-badge status-expired text-xs-bold">High</span>
                                    @else
                                        <span class="color-muted text-xs-medium">-</span>
                                    @endif
                                </td>
                                @endif

                                @if(in_array('badan_usaha', $visibleColumns))
                                <td class="bu-cell text-left color-slate-700">
                                    <span class="badge badge-bu bu-single">{{ Str::upper($buName) }}</span>
                                    <span class="badge badge-bu bu-group-badge d-none"></span>
                                </td>
                                @endif

                                <td class="period-cell text-left" style="{{ !in_array('periode', $visibleColumns) ? 'display:none' : '' }}">
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

                                @if(in_array('jangka_waktu', $visibleColumns))
                                <td class="text-center text-xs-medium">
                                    @if($rl->penagihanPembayaran && $rl->penagihanPembayaran->count() > 0)
                                        @foreach($rl->penagihanPembayaran as $penagihan)
                                            <div class="font-bold-700 color-slate-900">
                                                {{ $penagihan->jangka_waktu_hari }} {{ $penagihan->tipe_hari }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="color-muted">-</span>
                                    @endif
                                </td>
                                @endif

                                @if(in_array('diskon', $visibleColumns))
                                <td class="discount-cell text-center">
                                    @if($rl->diskon && $rl->diskon->count() > 0)
                                        @foreach($rl->diskon as $d)
                                            <span class="status-badge status-active text-xs-bold inline-flex-center">
                                                {{ $d->persentase_diskon }}%
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="color-muted">-</span>
                                    @endif
                                </td>
                                @endif

                                @if(in_array('jenis_layanan', $visibleColumns))
                                <td class="services-cell text-left">
                                    @if(count($jenisLayananArray) > 0)
                                        <div class="service-badges">
                                            @foreach($jenisLayananArray as $layanan)
                                                <span class="badge-layanan-khusus">{{ $layanan }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </td>
                                @endif

                                @if(in_array('benefit', $visibleColumns))
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
                                @endif

                                @if(in_array('cakupan', $visibleColumns))
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
                                @endif

                                @if(in_array('obat', $visibleColumns))
                                <td class="obat-cell text-left">
                                    @php
                                        $pivotObatIds = $pivot->obat_ids ?? [];
                                        $obats = ! empty($pivotObatIds) && $rl->obatRelasi ? $rl->obatRelasi->whereIn('id', $pivotObatIds) : collect();
                                    @endphp
                                    @if($obats->count() > 0)
                                        <div class="obat-list">
                                            @foreach($obats->take(3) as $obat)
                                                <span class="badge-obat-mini">{{ Str::limit($obat->nama, 25) }}</span>
                                            @endforeach
                                            @if($obats->count() > 3)
                                                <span class="more-obat">+{{ $obats->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </td>
                                @endif

                                <td class="status-cell text-center" style="{{ !in_array('status', $visibleColumns) ? 'display:none' : '' }}">
                                    @if($rl->status === 'aktif' && !$rl->is_expired)
                                        <span class="status-badge status-active">Aktif</span>
                                    @else
                                        <span class="status-badge status-expired">Expired</span>
                                    @endif
                                </td>

                                <td class="actions-cell text-center">
                                    <div class="action-buttons flex-col-center">
                                        <a href="{{ route('user.kerjasama.show', $rl) }}"
                                            title="Lihat Detail" class="action-btn view-btn no-underline inline-flex-center">
                                            <x-icon name="eye" class="icon-sm" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="{{ $totalCols }}" class="empty-state">
                                    <div class="empty-icon">
                                        <x-icon name="document" class="icon-lg" />
                                    </div>
                                    <div class="empty-text">
                                        <h3>Belum ada data kerjasama</h3>
                                        <p>Mohon hubungi administrator jika Anda merasa ini adalah kesalahan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="searchEmptyRow" class="d-none">
                            <td colspan="{{ $totalCols }}" class="empty-state search-empty-padding">
                                <div class="empty-icon search-empty-icon">
                                    <x-icon name="search-empty" class="icon-lg" />
                                </div>
                                <div class="empty-text text-center">
                                    <h3 class="search-empty-title">Tidak ada data yang dicari</h3>
                                    <p class="search-empty-subtitle">Silakan ubah kata kunci pencarian atau sesuaikan filter Anda.</p>
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
        let currentPage = 1;
        let itemsPerPage = 10;
        let allRows = [];
        let filteredRows = [];
        let sortColumn = '';
        let sortDirection = 'asc';
        let isGroupedMode = true;

        function updateGrouping() {
            const searchValue = document.getElementById('searchInput').value.trim();
            const bulanValue = document.getElementById('monthFilter').value;
            const tahunValue = document.getElementById('yearFilter').value;

            const hasActiveFilter = searchValue || bulanValue || tahunValue ||
                document.getElementById('namaAsuransiFilter')?.value ||
                document.getElementById('badanUsahaFilter')?.value ||
                document.getElementById('serviceFilter')?.value ||
                document.getElementById('obatFilter')?.value ||
                document.getElementById('periodFilter')?.value ||
                document.getElementById('statusFilter')?.value ||
                document.getElementById('highProfileFilter')?.value ||
                document.getElementById('payorTpaFilter')?.value ||
                document.getElementById('aliasTeraFilter')?.value ||
                document.getElementById('diskonFilter')?.value;

            isGroupedMode = !hasActiveFilter;

            const groups = {};
            allRows.forEach(function(row) {
                const rlId = row.getAttribute('data-rl-id');
                if (!groups[rlId]) groups[rlId] = [];
                groups[rlId].push(row);
            });

            allRows.forEach(function(row) {
                const rlId = row.getAttribute('data-rl-id');
                const group = groups[rlId];
                const isFirstInGroup = group && group[0] === row;

                if (isGroupedMode) {
                    row.style.display = isFirstInGroup ? '' : 'none';

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

        document.addEventListener('DOMContentLoaded', function () {
            initializeData();
            setupEventListeners();
            updateGrouping();
            updateDisplay();
        });

        function initializeData() {
            allRows = Array.from(document.querySelectorAll('.table-row'));
            filteredRows = [...allRows];
        }

        function setupEventListeners() {
            const searchInput = document.getElementById('searchInput');
            const clearSearchBtn = document.getElementById('clearSearch');
            const autocompleteList = document.getElementById('autocompleteList');

            if (searchInput) {
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
                    }
                });
            }

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                    autocompleteList.style.display = 'none';
                }
            });

            const itemsPerPageSelect = document.getElementById('itemsPerPageSelect');
            if (itemsPerPageSelect) {
                itemsPerPageSelect.addEventListener('change', function() {
                    itemsPerPage = parseInt(this.value);
                    currentPage = 1;
                    updateDisplay();
                });
            }
        }

        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const monthFilter = document.getElementById('monthFilter').value;
            const yearFilter = document.getElementById('yearFilter').value;

            const headerFilters = {
                nama: document.getElementById('namaAsuransiFilter')?.value.toLowerCase() ?? '',
                alias: document.getElementById('aliasTeraFilter')?.value.toLowerCase() ?? '',
                high: document.getElementById('highProfileFilter')?.value ?? '',
                payor: document.getElementById('payorTpaFilter')?.value ?? '',
                bu: document.getElementById('badanUsahaFilter')?.value.toLowerCase() ?? '',
                jangka: document.getElementById('jangkaWaktuFilter')?.value ?? '',
                diskon: document.getElementById('diskonFilter')?.value.toLowerCase() ?? '',
                layanan: document.getElementById('serviceFilter')?.value.toLowerCase() ?? '',
                obat: document.getElementById('obatFilter')?.value.toLowerCase() ?? '',
                period: document.getElementById('periodFilter')?.value ?? '',
                status: document.getElementById('statusFilter')?.value ?? ''
            };

            filteredRows = allRows.filter(row => {
                const data = {
                    nama: row.getAttribute('data-nama') || '',
                    alias: row.getAttribute('data-alias-tera') || '',
                    layanan: row.getAttribute('data-layanan') || '',
                    benefit: row.getAttribute('data-benefit') || '',
                    cakupan: row.getAttribute('data-cakupan') || '',
                    obat: row.getAttribute('data-obat') || '',
                    diskon: row.getAttribute('data-diskon') || '',
                    status: row.getAttribute('data-status') || '',
                    badgeStatus: row.getAttribute('data-badge-status') || '',
                    payor: row.getAttribute('data-penjamin-category') || '',
                    high: row.getAttribute('data-high-profile') || '',
                    bu: (row.getAttribute('data-badan-usaha') || '').toLowerCase(),
                    jangka: row.getAttribute('data-jangka-waktu') || '',
                    tMulai: row.getAttribute('data-tanggal-mulai') || '',
                    tAkhir: row.getAttribute('data-tanggal-berakhir') || ''
                };

                const matchesGlobal = !searchTerm ||
                    data.nama.includes(searchTerm) ||
                    data.alias.includes(searchTerm) ||
                    data.layanan.includes(searchTerm) ||
                    data.benefit.includes(searchTerm) ||
                    data.cakupan.includes(searchTerm) ||
                    data.obat.includes(searchTerm) ||
                    data.bu.includes(searchTerm);

                const matchesHeader =
                    (!headerFilters.nama || data.nama.includes(headerFilters.nama)) &&
                    (!headerFilters.alias || data.alias.includes(headerFilters.alias)) &&
                    (!headerFilters.high || data.high === headerFilters.high) &&
                    (!headerFilters.payor || data.payor === headerFilters.payor) &&
                    (!headerFilters.bu || data.bu.includes(headerFilters.bu)) &&
                    (!headerFilters.jangka || data.jangka.split(',').includes(headerFilters.jangka)) &&
                    (!headerFilters.diskon || data.diskon.includes(headerFilters.diskon)) &&
                    (!headerFilters.layanan || data.layanan.includes(headerFilters.layanan)) &&
                    (!headerFilters.obat || data.obat.includes(headerFilters.obat)) &&
                    (!headerFilters.status || data.badgeStatus === headerFilters.status);

                let matchesPeriod = true;
                if (headerFilters.period) {
                    const now = new Date();
                    now.setHours(0, 0, 0, 0);
                    const end = new Date(data.tAkhir);
                    const diff = Math.ceil((end - now) / (1000 * 60 * 60 * 24));
                    if (headerFilters.period === 'active') matchesPeriod = (data.status === 'aktif' && diff > 0);
                    else if (headerFilters.period === 'expiring30') matchesPeriod = (data.status === 'aktif' && diff > 0 && diff <= 30);
                    else if (headerFilters.period === 'expiring90') matchesPeriod = (data.status === 'aktif' && diff > 0 && diff <= 90);
                    else if (headerFilters.period === 'expired') matchesPeriod = (data.status !== 'aktif' || diff < 0);
                }

                let matchesMonth = true;
                if (monthFilter) {
                    const sM = parseInt(data.tMulai.split('-')[1]);
                    const eM = parseInt(data.tAkhir.split('-')[1]);
                    const sY = parseInt(data.tMulai.split('-')[0]);
                    const eY = parseInt(data.tAkhir.split('-')[0]);
                    const targetM = parseInt(monthFilter);

                    if (sY === eY) {
                        matchesMonth = (targetM >= sM && targetM <= eM);
                    } else if (eY - sY > 1) {
                        matchesMonth = true;
                    } else {
                        matchesMonth = (targetM >= sM || targetM <= eM);
                    }
                }

                let matchesYear = true;
                if (yearFilter) {
                    const sY = data.tMulai.split('-')[0];
                    const eY = data.tAkhir.split('-')[0];
                    matchesYear = (sY === yearFilter || eY === yearFilter);
                }

                return matchesGlobal && matchesHeader && matchesPeriod && matchesMonth && matchesYear;
            });

            const headerFilterIds = ['namaAsuransi', 'aliasTera', 'highProfile', 'payorTpa', 'badanUsaha', 'jangkaWaktu', 'diskon', 'service', 'obat', 'period', 'status'];
            headerFilterIds.forEach(key => {
                const wrap = document.getElementById(`wrap-${key}Filter`);
                if (wrap) {
                    const btn = wrap.querySelector('.clear-filter-btn');
                    const input = document.getElementById(`${key}Filter`);
                    if (btn && input) btn.style.display = input.value ? 'flex' : 'none';
                }
            });

            document.getElementById('clearMonth').style.display = monthFilter ? 'flex' : 'none';
            document.getElementById('clearYear').style.display = yearFilter ? 'flex' : 'none';

            currentPage = 1;
            updateGrouping();
            updateDisplay();
        }

        function handleFilterInput(el) {
            const wrap = document.getElementById('wrap-' + el.id);
            if (wrap) {
                const btn = wrap.querySelector('.clear-filter-btn');
                if (btn) btn.style.display = el.value ? 'flex' : 'none';
            }
            filterTable();
        }

        function clearFilterWrap(id) {
            const input = document.getElementById(id);
            if (input) {
                input.value = '';
                const wrap = document.getElementById('wrap-' + id);
                if (wrap) {
                    const btn = wrap.querySelector('.clear-filter-btn');
                    if (btn) btn.style.display = 'none';
                }
                filterTable();
            }
        }

        function resetAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('monthFilter').value = '';
            document.getElementById('yearFilter').value = '';
            ['namaAsuransiFilter','aliasTeraFilter','highProfileFilter','payorTpaFilter','badanUsahaFilter',
             'jangkaWaktuFilter','diskonFilter','serviceFilter','obatFilter','periodFilter','statusFilter'
            ].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('clearSearch').style.display = 'none';
            filterTable();
        }

        function sortTable(column) {
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }

            updateSortIcons(column, sortDirection);

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
                        aValue = new Date(a.getAttribute('data-tanggal-mulai') + 'T00:00:00');
                        bValue = new Date(b.getAttribute('data-tanggal-mulai') + 'T00:00:00');
                        break;
                    default: return 0;
                }
                if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
                if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
                return 0;
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

        function updateDisplay() {
            updateTableView();
            updatePagination();
        }

        function updateTableView() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            allRows.forEach(row => row.style.display = 'none');

            let visibleCount = 0;
            filteredRows.slice(start, end).forEach((row) => {
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
                searchEmptyRow.style.display = (allRows.length > 0 && filteredRows.length === 0) ? '' : 'none';
            }
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

            const paginationContainer = document.getElementById('paginationContainer');
            if (totalItems === 0) {
                paginationContainer.classList.add('hidden');
                paginationContainer.style.display = 'none';
            } else {
                paginationContainer.classList.remove('hidden');
                paginationContainer.style.display = 'flex';
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

        function clearMonthFilter() {
            document.getElementById('monthFilter').value = '';
            filterTable();
        }

        function clearYearFilter() {
            document.getElementById('yearFilter').value = '';
            filterTable();
        }

        function showAutocomplete(query, container) {
            if (!query || query.length < 2) {
                container.style.display = 'none';
                return;
            }
            const suggestionList = Array.from(asuransiList).filter(function(item) {
                return item.toLowerCase().includes(query.toLowerCase());
            }).slice(0, 8);
            if (suggestionList.length === 0) {
                container.style.display = 'none';
                return;
            }
            container.innerHTML = suggestionList.map(item =>
                `<div class="autocomplete-item" onclick="selectAutocompleteValue('${item}')">${item}</div>`
            ).join('');
            container.style.display = 'block';
        }

        function selectAutocompleteValue(value) {
            document.getElementById('searchInput').value = value;
            document.getElementById('autocompleteList').style.display = 'none';
            document.getElementById('clearSearch').style.display = 'flex';
            filterTable();
        }

        const asuransiList = @json(collect($ruangLingkup)->pluck('rl')->pluck('nama_asuransi')->unique()->values());

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
    </script>
</x-user-layout>
