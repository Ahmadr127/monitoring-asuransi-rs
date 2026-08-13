{{--
    Reusable Data Table with per-column search filters, global search, dynamic pagination
    and an action column. No repeated code across pages.

    Usage:
    <x-data-table
        id="pendingTable"
        title="Menunggu Persetujuan Saya"
        subtitle="Kontrak yang menunggu keputusan Anda"
        :badge="$pending->count()"
        :columns="[
            ['label' => 'No', 'key' => '__no', 'center' => true, 'min' => 50],
            ['label' => 'Kontrak', 'key' => 'nama_asuransi', 'min' => 260, 'searchable' => true, 'placeholder' => 'Cari kontrak...'],
            ['label' => 'Aksi', 'key' => '__actions', 'center' => true, 'min' => 110],
        ]"
        :rows="$pendingRows"
        empty-text="Tidak ada pengajuan."
        :per-page="10"
    />

    $rows: collection of arrays with:
        'cells'   => [columnKey => rendered HTML],
        'cols'    => [columnKey => lowercased searchable text] (only for searchable columns),
        'search'  => lowercased global search text,
        'actions' => rendered HTML for the __actions cell (optional),
        'url'     => optional URL; whole row becomes clickable navigating to it

    $globalFilters: array of ['key', 'all', 'options'] rendered as selects in the
    search toolbar (outside the table). Rows must provide matching 'cols'[key] values.
--}}

@props([
    'id' => null,
    'title' => '',
    'subtitle' => '',
    'badge' => null,
    'columns' => [],
    'rows' => [],
    'emptyText' => 'Tidak ada data.',
    'perPage' => 10,
    'globalFilters' => [],
])

@php
    $colCount = count($columns);
@endphp

<div class="data-table-root" id="{{ $id }}" data-config='@json(['perPage' => $perPage])'>
    <div class="data-table-container mb-0">
        @if($title)
        <div class="data-table-header">
            <div class="data-table-header-left">
                <h1>{{ $title }}</h1>
                @if($subtitle)<p>{{ $subtitle }}</p>@endif
            </div>
            @if($badge !== null)
                <span class="badge-premium badge-teal">{{ $badge }}</span>
            @endif
        </div>
        @endif

        <div class="filter-search-section">
<div class="search-container">
                    <div class="search-box-advanced sb-max-400">
                        <div class="search-icon"><x-icon name="search" /></div>
                        <input type="text" id="{{ $id }}-search" oninput="window.DataTable?.search('{{ $id }}', this.value)" placeholder="Cari..." class="form-input-search">
                        <button id="{{ $id }}-clearSearch" onclick="window.DataTable?.clearSearch('{{ $id }}')" class="clear-search-btn"><x-icon name="close" /></button>
                    </div>
                    <div class="search-tools">
                        @foreach($globalFilters as $gf)
                        <select class="filter-select gf-select" data-gf-key="{{ $gf['key'] }}" onchange="window.DataTable?.filterGlobal(this)">
                            <option value="">{{ $gf['all'] ?? $gf['key'] }}</option>
                            @foreach($gf['options'] as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['text'] }}</option>
                            @endforeach
                        </select>
                        @endforeach
                        <select id="{{ $id }}-perPage" class="filter-select fs-110" onchange="window.DataTable?.setPerPage('{{ $id }}', this.value)">
                            @foreach([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" @selected((int) $perPage === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
        </div>

        <div class="table-view">
            <div class="table-wrapper">
                <table class="page-table">
                    <thead>
                        <tr>
                            @foreach($columns as $col)
                                <th @class([
                                    'text-center' => $col['center'] ?? false,
                                    'th-min-' . ($col['min'] ?? 0) => isset($col['min']),
                                ]) @if(isset($col['width'])) style="width:{{ $col['width'] }}px;max-width:{{ $col['width'] }}px" @endif>{{ $col['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-filter-row">
                            @foreach($columns as $i => $col)
                                <td>
                                    @if($col['searchable'] ?? false)
                                        <div class="filter-input-container">
                                            @if(($col['filter'] ?? '') === 'select')
                                                <select data-col="{{ $i }}" onchange="window.DataTable?.filterCol(this)" class="filter-select">
                                                    @foreach($col['options'] ?? [] as $opt)
                                                        <option value="{{ $opt['value'] ?? '' }}">{{ $opt['text'] ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" data-col="{{ $i }}" oninput="window.DataTable?.filterCol(this)" autocomplete="off" class="filter-select" placeholder="{{ $col['placeholder'] ?? 'Cari...' }}">
                                                <button type="button" class="clear-filter-btn" onclick="window.DataTable?.clearFilter(this)" title="Clear"><x-icon name="close" /></button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        @foreach($rows as $row)
                            <tr class="data-table-row"
                                data-search="{{ strtolower($row['search'] ?? '') }}"
                                @if(!empty($row['url']))
                                onclick="location.href='{{ $row['url'] }}'"
                                style="cursor:pointer"
                                @endif
                                @foreach($columns as $i => $col)
                                    @if(isset($col['key']) && isset($row['cols'][$col['key']]))
                                        data-c{{ $i }}="{{ strtolower($row['cols'][$col['key']]) }}"
                                    @endif
                                @endforeach
                                @foreach($globalFilters as $gf)
                                    @if(isset($row['cols'][$gf['key']]))
                                        data-gf-{{ $gf['key'] }}="{{ strtolower($row['cols'][$gf['key']]) }}"
                                    @endif
                                @endforeach
                            >
                                @foreach($columns as $i => $col)
                                    @if(($col['key'] ?? '') === '__no')
                                        <td class="number-cell text-center font-bold-700 text-muted-premium"><span class="row-number"></span></td>
                                    @elseif(($col['key'] ?? '') === '__actions')
                                        <td class="actions-cell text-center"><div class="action-buttons flex-col-center">{!! $row['actions'] ?? '' !!}</div></td>
                                    @else
                                        <td @if($col['center'] ?? false) class="text-center" @endif>{!! $row['cells'][$col['key']] ?? '' !!}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach

                        <tr class="data-empty-row" style="display:none">
                            <td colspan="{{ $colCount }}" class="empty-state">
                                <div class="empty-icon"><x-icon name="document" /></div>
                                <div class="empty-text"><h3>{{ $emptyText }}</h3></div>
                            </td>
                        </tr>
                        <tr class="data-empty-search" style="display:none">
                            <td colspan="{{ $colCount }}" class="empty-state">
                                <div class="empty-icon text-muted-premium"><x-icon name="search-empty" class="empty-icon-large-svg" /></div>
                                <div class="empty-text text-center"><h3 class="empty-title">Tidak ada data yang cocok</h3><p class="empty-subtitle">Coba ubah kata kunci pencarian Anda.</p></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-container" id="{{ $id }}-pagination">
            <div class="pagination-info" id="{{ $id }}-paginationInfo">Menampilkan 0-0 dari 0 data</div>
            <div class="pagination-controls" id="{{ $id }}-paginationControls"></div>
        </div>
    </div>
</div>

@once
    <style>
        .page-table tr.table-filter-row td {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.4rem 0.4rem !important;
        }
        .page-table .table-filter-row .filter-input-container {
            width: 100%;
        }
        .page-table .table-filter-row .filter-input-container input.filter-select {
            min-height: 30px;
            padding: 0.35rem 2rem 0.35rem 0.75rem;
            font-size: 11px;
            border: 1px solid #94a3b8 !important;
            color: #000000 !important;
        }
        .page-table .table-filter-row .filter-input-container input.filter-select::placeholder {
            color: #000000 !important;
            opacity: 1;
        }
        .page-table .table-filter-row .filter-input-container .filter-select {
            min-width: 0;
        }
        .th-min-240 { min-width: 240px; }
        .pagination-ellipsis {
            padding: 0 0.25rem;
            color: #94a3b8;
            font-size: 12px;
        }
        .search-tools {
            display: flex;
            gap: 0.6rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .gf-select {
            width: 150px;
        }
        .data-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            background: #ffffff;
            padding: 0.8rem 1.25rem 0.4rem;
        }
        .data-table-header h1 {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
        }
        .data-table-header p {
            margin: 0.1rem 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .data-table-row[onclick] {
            transition: background 0.15s ease;
        }
        .data-table-row[onclick]:hover {
            background: #f0fdfa !important;
        }
    </style>

    @push('scripts')
    <script>
        window.DataTable = (function () {
            const tables = {};

            function register(id, cfg) {
                const root = document.getElementById(id);
                if (!root || tables[id]) return;
                const body = root.querySelector('tbody');
                const rows = Array.from(body.querySelectorAll('.data-table-row'));
                tables[id] = {
                    cfg: cfg || {},
                    rows,
                    filtered: [...rows],
                    page: 1,
                    perPage: (cfg && cfg.perPage) || 10,
                    searchInput: document.getElementById(id + '-search'),
                    clearBtn: document.getElementById(id + '-clearSearch'),
                };
                render(id);
            }

            function get(id) {
                return tables[id];
            }

            function search(id, val) {
                const t = get(id);
                if (!t) return;
                if (t.clearBtn) t.clearBtn.style.display = val.length ? 'flex' : 'none';
                filter(id);
            }

            function clearSearch(id) {
                const t = get(id);
                if (!t) return;
                t.searchInput.value = '';
                if (t.clearBtn) t.clearBtn.style.display = 'none';
                filter(id);
            }

            function filterCol(inputEl) {
                const root = inputEl.closest('.data-table-root');
                if (!root) return;
                const wrap = inputEl.closest('.filter-input-container');
                if (wrap && inputEl.tagName !== 'SELECT') {
                    const b = wrap.querySelector('.clear-filter-btn');
                    if (b) b.style.display = inputEl.value ? 'flex' : 'none';
                }
                filter(root.id);
            }

            function clearFilter(btn) {
                const wrap = btn.closest('.filter-input-container');
                const input = wrap && wrap.querySelector('input');
                if (input) input.value = '';
                btn.style.display = 'none';
                const root = btn.closest('.data-table-root');
                if (root) filter(root.id);
            }

            function setPerPage(id, val) {
                const t = get(id);
                if (!t) return;
                t.perPage = parseInt(val, 10) || 10;
                t.page = 1;
                render(id);
            }

            function goto(id, page) {
                const t = get(id);
                if (!t) return;
                t.page = page;
                render(id);
            }

            function filterGlobal(selectEl) {
                const root = selectEl.closest('.data-table-root');
                if (root) filter(root.id);
            }

            function filter(id) {
                const t = get(id);
                if (!t) return;
                const q = (t.searchInput ? t.searchInput.value : '').toLowerCase();
                const colInputs = Array.from(document.querySelectorAll('#' + id + ' .table-filter-row input[data-col]'));
                const gfEls = Array.from(document.querySelectorAll('#' + id + ' [data-gf-key]'));
                t.filtered = t.rows.filter(row => {
                    const s = row.getAttribute('data-search') || '';
                    if (q && !s.includes(q)) return false;
                    for (const el of colInputs) {
                        const ci = el.getAttribute('data-col');
                        const v = el.value.trim().toLowerCase();
                        if (!v) continue;
                        const cell = (row.getAttribute('data-c' + ci) || '').toLowerCase();
                        if (el.tagName === 'SELECT' ? cell !== v : !cell.includes(v)) return false;
                    }
                    for (const el of gfEls) {
                        const key = el.getAttribute('data-gf-key');
                        const v = el.value.trim().toLowerCase();
                        if (v && (row.getAttribute('data-gf-' + key) || '') !== v) return false;
                    }
                    return true;
                });
                t.page = 1;
                render(id);
            }

            function render(id) {
                const t = get(id);
                if (!t) return;
                const total = t.filtered.length;
                const totalPages = Math.max(1, Math.ceil(total / t.perPage));
                if (t.page > totalPages) t.page = totalPages;

                t.rows.forEach(r => r.style.display = 'none');

                const tbody = t.rows.length ? t.rows[0].closest('tbody') : null;
                const emptyRow = tbody && tbody.querySelector('.data-empty-row');
                const emptySearch = tbody && tbody.querySelector('.data-empty-search');
                if (emptyRow) emptyRow.style.display = t.rows.length === 0 ? '' : 'none';
                if (emptySearch) emptySearch.style.display = (t.rows.length > 0 && total === 0) ? '' : 'none';

                const pag = document.getElementById(id + '-pagination');
                if (!pag) return;
                if (total === 0) {
                    pag.style.display = 'none';
                    return;
                }
                pag.style.display = 'flex';

                const start = (t.page - 1) * t.perPage;
                const end = Math.min(start + t.perPage, total);
                t.filtered.slice(start, end).forEach((r, i) => {
                    r.style.display = '';
                    const n = r.querySelector('.row-number');
                    if (n) n.textContent = start + i + 1;
                });

                const info = document.getElementById(id + '-paginationInfo');
                if (info) info.textContent = 'Menampilkan ' + (start + 1) + '-' + end + ' dari ' + total + ' data';
                renderButtons(id, totalPages);
            }

            const CHEV_LEFT = '<svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>';
            const CHEV_RIGHT = '<svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L11.586 10 7.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>';

            function renderButtons(id, totalPages) {
                const t = get(id);
                const c = document.getElementById(id + '-paginationControls');
                if (!c) return;
                const cur = t.page;
                let html = '<button class="page-btn"' + (cur === 1 ? ' disabled' : '') + ' onclick="DataTable.goto(\'' + id + '\',' + (cur - 1) + ')">' + CHEV_LEFT + '</button>';
                let s = Math.max(1, cur - 2);
                let e = Math.min(totalPages, s + 4);
                if (e - s < 4) s = Math.max(1, e - 4);
                if (s > 1) {
                    html += '<button class="page-btn" onclick="DataTable.goto(\'' + id + '\',1)">1</button>';
                    if (s > 2) html += '<span class="pagination-ellipsis">...</span>';
                }
                for (let i = s; i <= e; i++) {
                    html += '<button class="page-btn' + (i === cur ? ' active' : '') + '" onclick="DataTable.goto(\'' + id + '\',' + i + ')">' + i + '</button>';
                }
                if (e < totalPages) {
                    if (e < totalPages - 1) html += '<span class="pagination-ellipsis">...</span>';
                    html += '<button class="page-btn" onclick="DataTable.goto(\'' + id + '\',' + totalPages + ')">' + totalPages + '</button>';
                }
                html += '<button class="page-btn"' + (cur === totalPages ? ' disabled' : '') + ' onclick="DataTable.goto(\'' + id + '\',' + (cur + 1) + ')">' + CHEV_RIGHT + '</button>';
                c.innerHTML = html;
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.data-table-root').forEach(function (el) {
                    var cfg = {};
                    try {
                        cfg = JSON.parse(el.getAttribute('data-config') || '{}');
                    } catch (e) {}
                    register(el.id, cfg);
                });
            });

            return { search: search, clearSearch: clearSearch, filterCol: filterCol, clearFilter: clearFilter, filterGlobal: filterGlobal, setPerPage: setPerPage, goto: goto };
        })();
    </script>
    @endpush
@endonce