<x-app-layout>
    <x-slot name="title">Preview Import</x-slot>

    @php
        $pageTitle = 'Preview Import';
        $pageDescription = 'Review data sebelum diimport ke database';
    @endphp

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = '{{ $pageDescription }}'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush

    <div class="import-card import-preview-card">
        <div class="import-preview-flex">
            <div>
                <h3 class="import-card-title" style="margin-bottom: 0.25rem;">Preview Data Import</h3>
                <p class="import-preview-file">
                    File: <strong>{{ $fileName }}</strong>
                </p>
            </div>
            <div class="import-preview-badges">
                <div class="import-preview-badge-box import-preview-badge-box-new">
                    <div class="import-preview-badge-number import-preview-badge-number-new">{{ $summary['new'] }}</div>
                    <div class="import-preview-badge-label import-preview-badge-label-new">Baru</div>
                </div>

                <div class="import-preview-badge-box import-preview-badge-box-merge">
                    <div class="import-preview-badge-number import-preview-badge-number-merge">{{ $summary['merge'] ?? 0 }}</div>
                    <div class="import-preview-badge-label import-preview-badge-label-merge">Digabung</div>
                </div>
                <div class="import-preview-badge-box import-preview-badge-box-error">
                    <div class="import-preview-badge-number import-preview-badge-number-error">{{ $summary['error'] }}</div>
                    <div class="import-preview-badge-label import-preview-badge-label-error">Error</div>
                </div>
            </div>
        </div>
    </div>

    <div class="import-card">
        <form method="POST" action="{{ route('admin.export.import.confirm') }}">
            @csrf

            @if (count($previewRows) > 0)
            <div class="import-filter-bar">
                <select class="import-filter-select" id="importFilterSelect">
                    <option value="all">All</option>
                    <option value="new">Baru</option>
                    <option value="merge">Digabung</option>

                    <option value="error">Error</option>
                </select>
                <select class="import-filter-select" id="importPerPageSelect">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="import-preview-table-wrap">
                <table class="import-preview-table">
                    <thead class="import-preview-thead">
                        <tr>
                            <th class="import-preview-th">#</th>
                            <th class="import-preview-th">Nama Asuransi</th>
                            <th class="import-preview-th">Badan Usaha</th>
                            <th class="import-preview-th">Periode</th>
                            <th class="import-preview-th import-preview-th-center">Status</th>
                            <th class="import-preview-th">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="importPreviewTbody">
                        @foreach ($previewRows as $index => $row)
                        @php
                            $badge = '';
                            $rowClass = '';
                            $badgeClass = '';
                            if ($row['status'] === 'new') {
                                $badge = 'Baru';
                                $badgeClass = 'import-preview-badge-new';
                            } elseif ($row['status'] === 'merge') {
                                $badge = 'Digabung';
                                $badgeClass = 'import-preview-badge-merge';
                                $rowClass = 'import-preview-tr-merge';
                            } else {
                                $badge = 'Error';
                                $badgeClass = 'import-preview-badge-error';
                                $rowClass = 'import-preview-tr-error';
                            }
                        @endphp
                        <tr class="import-preview-tr {{ $rowClass }}" data-status="{{ $row['status'] }}" data-index="{{ $index }}">
                            <td class="import-preview-td import-preview-td-num">{{ $row['excel_row'] }}</td>
                            <td class="import-preview-td import-preview-td-name">{{ $row['nama_asuransi'] }}</td>
                            <td class="import-preview-td import-preview-td-bu">{{ $row['badan_usaha'] }}</td>
                            <td class="import-preview-td import-preview-td-date">{{ $row['tanggal_mulai'] }} — {{ $row['tanggal_berakhir'] }}</td>
                            <td class="import-preview-td import-preview-td-status">
                                <span class="import-preview-badge {{ $badgeClass }}">{{ $badge }}</span>
                            </td>
                            <td class="import-preview-td import-preview-td-message">
                                @if ($row['status'] === 'error')
                                <span class="import-preview-error-icon" tabindex="0"
                                    onclick="this.classList.toggle('show-tooltip')"
                                    onblur="this.classList.remove('show-tooltip')">
                                    !
                                    <span class="import-preview-error-tooltip">{{ $row['message'] }}</span>
                                </span>
                                @else
                                {{ $row['message'] }}
                                @endif

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-container" id="importPagination" style="display:none">
                <div class="pagination-info" id="importPageInfo">Menampilkan 0-0 dari 0 data</div>
                <div class="pagination-controls" id="importPageControls"></div>
            </div>
            @else
            <div class="import-preview-empty">
                Tidak ada data yang ditemukan dalam file ini.
            </div>
            @endif

            <div class="import-preview-actions-bar">
                <div class="import-actions">
                    @if (($summary['merge'] ?? 0) > 0)
                    <label class="import-preview-overwrite-label">
                        <input type="checkbox" name="overwrite" value="1">
                        Timpa data lama
                    </label>
                    @endif
                    <a href="{{ route('admin.export.import-form') }}" class="btn-secondary import-btn-secondary">
                        Batal
                    </a>
                    @if ($summary['new'] > 0 || ($summary['merge'] ?? 0) > 0)
                    <button type="submit" class="btn-primary import-btn-primary">
                        Konfirmasi Import
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
<script>
(function() {
    var allRows = Array.from(document.querySelectorAll('#importPreviewTbody tr'));
    var filterSelect = document.getElementById('importFilterSelect');
    var perPageSelect = document.getElementById('importPerPageSelect');
    var paginationContainer = document.getElementById('importPagination');
    var pageInfo = document.getElementById('importPageInfo');
    var pageControls = document.getElementById('importPageControls');
    var currentPage = 1;
    var itemsPerPage = 10;

    function getFiltered() {
        var filter = filterSelect.value;
        return allRows.filter(function(row) {
            return filter === 'all' || row.getAttribute('data-status') === filter;
        });
    }

    function updateDisplay() {
        var filtered = getFiltered();
        var perPage = parseInt(perPageSelect.value, 10);
        itemsPerPage = perPage;
        var totalPages = Math.ceil(filtered.length / perPage);
        if (currentPage > totalPages) currentPage = totalPages || 1;
        if (currentPage < 1) currentPage = 1;
        var start = (currentPage - 1) * perPage;
        var end = Math.min(start + perPage, filtered.length);

        allRows.forEach(function(row) { row.style.display = 'none'; });
        filtered.slice(start, end).forEach(function(row) { row.style.display = ''; });

        if (filtered.length > 0) {
            pageInfo.textContent = 'Menampilkan ' + (start + 1) + '-' + end + ' dari ' + filtered.length + ' data';
        } else {
            pageInfo.textContent = 'Menampilkan 0-0 dari 0 data';
        }

        var html = '';
        html += '<button class="page-btn"' + (currentPage === 1 ? ' disabled' : '') + ' onclick="window._impGo(' + (currentPage - 1) + ')">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M15 19l-7-7 7-7"/></svg>' +
                '</button>';
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
        for (var i = startPage; i <= endPage; i++) {
            html += '<button class="page-btn' + (i === currentPage ? ' active' : '') + '" onclick="window._impGo(' + i + ')">' + i + '</button>';
        }
        html += '<button class="page-btn"' + (currentPage === totalPages ? ' disabled' : '') + ' onclick="window._impGo(' + (currentPage + 1) + ')">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M9 5l7 7-7 7"/></svg>' +
                '</button>';
        pageControls.innerHTML = html;

        paginationContainer.style.display = filtered.length > 0 ? 'flex' : 'none';
    }

    window._impGo = function(p) { currentPage = p; updateDisplay(); };

    filterSelect.addEventListener('change', function() { currentPage = 1; updateDisplay(); });
    perPageSelect.addEventListener('change', function() { currentPage = 1; updateDisplay(); });

    if (allRows.length > 10) updateDisplay();
})();
</script>
</x-app-layout>
