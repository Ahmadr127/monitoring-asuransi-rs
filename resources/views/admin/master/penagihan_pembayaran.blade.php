<x-app-layout>
    <x-slot name="title">Penagihan & Pembayaran</x-slot>

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = 'Kelola termin penagihan dan status pembayaran asuransi.'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush


    <div class="premium-container">

        <div class="content-layout">
            <!-- Sidebar: Form -->
            <div class="premium-card">
                <div class="card-header">
                    <h2>Tambah Penagihan</h2>
                    <x-icon name="plus-circle" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.penagihan-pembayaran.store') }}">
                        @csrf
                        <x-asuransi-select
                            :ruangLingkups="$ruangLingkups"
                            :disabled-ids="$existingRlIds"
                            required
                        />
                        <div class="form-group">
                            <label>Jangka Waktu (Hari) <span class="text-danger">*</span></label>
                            <input type="number" name="jangka_waktu_hari" class="form-input-minimal" placeholder="Contoh: 45" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Tipe Hari <span class="text-danger">*</span></label>
                            <select name="tipe_hari" class="form-input-minimal" required>
                                <option value="Kalender">Hari Kalender</option>
                                <option value="Hari Kerja">Hari Kerja</option>
                            </select>
                        </div>
                        <div class="form-group mb-8">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-input-minimal" rows="3" placeholder="Contoh: Pembayaran 45 hari setelah tagihan..."></textarea>
                        </div>

                        <button type="submit" class="btn-premium-submit">
                            <x-icon name="plus-circle-solid" class="icon-18" />
                            Simpan Data
                        </button>
                    </form>
                </div>
            </div>

            <!-- Main Panel: Table -->
            <div class="data-table-container mb-0">
                <div class="filter-search-section">
                    <div class="search-container">
                        <div class="search-box-advanced sb-max-400">
                            <div class="search-icon">
                                <x-icon name="search" />
                            </div>
                            <input type="text" id="tableSearchInput" oninput="filterDataTable()" placeholder="Cari asuransi..." class="form-input-search">
                            <button id="clearSearch" onclick="clearSearchInput()" class="clear-search-btn">
                                <x-icon name="close" />
                            </button>
                            <div id="autocompleteList" class="autocomplete-dropdown"></div>
                        </div>
                        <div class="filter-input-container w-145">
                            <select id="monthFilter" class="filter-select" onchange="filterDataTable()">
                                <option value="">Semua Bulan</option>
                                <option value="01">Januari</option><option value="02">Februari</option>
                                <option value="03">Maret</option><option value="04">April</option>
                                <option value="05">Mei</option><option value="06">Juni</option>
                                <option value="07">Juli</option><option value="08">Agustus</option>
                                <option value="09">September</option><option value="10">Oktober</option>
                                <option value="11">November</option><option value="12">Desember</option>
                            </select>
                            <button type="button" id="clearMonthFilter" class="filter-x-btn hidden" onclick="clearMonthYearFilter('month')" title="Clear">
                                <x-icon name="close" />
                            </button>
                        </div>
                        <div class="filter-input-container w-120">
                            <select id="yearFilter" class="filter-select" onchange="filterDataTable()">
                                <option value="">Semua Tahun</option>
                                @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                            <button type="button" id="clearYearFilter" class="filter-x-btn hidden" onclick="clearMonthYearFilter('year')" title="Clear">
                                <x-icon name="close" />
                            </button>
                        </div>
                        <div class="filter-input-container w-145">
                            <select id="tipeHariFilter" class="filter-select" onchange="filterDataTable()">
                                <option value="">Semua Tipe</option>
                                <option value="Hari Kerja">Hari Kerja</option>
                                <option value="Kalender">Hari Kalender</option>
                            </select>
                            <button type="button" id="clearTipeHariFilter" class="filter-x-btn hidden" onclick="clearTipeHariFilter()" title="Clear">
                                <x-icon name="close" />
                            </button>
                        </div>
                        <select id="itemsPerPage" class="filter-select fs-110" onchange="changeItemsPerPage()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="table-view">
                    <div class="table-wrapper">
                        <table class="page-table">
                            <thead>
                                <tr>
                                    <th class="text-center th-min-50">No</th>
                                    <th class="th-min-300">Nama Asuransi</th>
                                    <th class="text-center th-min-150">Jangka Waktu</th>
                                    <th class="text-center th-min-160">Tanggal Reminder</th>
                                    <th class="text-center th-min-70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($penagihans as $index => $item)
                                @php
                                    $bulan = $item->tanggal_reminder ? $item->tanggal_reminder->format('m') : '';
                                    $tahun = $item->tanggal_reminder ? $item->tanggal_reminder->format('Y') : '';
                                @endphp
                                <tr class="table-row"
                                    data-search="{{ strtolower($item->ruangLingkup->nama_asuransi . ' ' . $item->keterangan) }}"
                                    data-nama="{{ strtolower($item->ruangLingkup->nama_asuransi) }}"
                                    data-bulan="{{ $bulan }}"
                                    data-tahun="{{ $tahun }}"
                                    data-tipe-hari="{{ $item->tipe_hari ?? '' }}">
                                    <td class="number-cell text-center font-bold-700 text-muted-premium"><span class="row-number">{{ $index + 1 }}</span></td>
                                    <td>
                                        <div class="font-bold-700 color-slate-900">{{ $item->ruangLingkup->nama_asuransi }}</div>
                                        @if($item->keterangan)
                                            <div class="text-xs-muted line-clamp-1 max-w-300">{{ $item->keterangan }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-premium badge-slate">{{ $item->jangka_waktu_hari }} {{ $item->tipe_hari }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->tanggal_reminder)
                                            <div class="flex-center-gap-2">
                                                <x-icon name="calendar-solid" class="icon-14 color-amber-600" />
                                                <span class="font-bold-700 color-slate-700">{{ $item->tanggal_reminder->format('d M Y') }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs-muted italic">-</span>
                                        @endif
                                    </td>
                                    <td class="actions-cell text-center">
                                        <div class="action-buttons flex-col-center">
                                            <button onclick='openEditModal(@json($item))' class="action-btn edit-btn" title="Edit">
                                                <x-icon name="edit" />
                                            </button>
                                            <x-delete-modal
                                                action="{{ route('admin.penagihan-pembayaran.destroy', $item) }}"
                                                label="{{ $item->nama_asuransi ?? 'data ini' }}"
                                                class="action-btn delete-btn"
                                                title="Hapus"
                                            >
                                                <x-icon name="trash" class="icon-14" />
                                            </x-delete-modal>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow"><td colspan="5" class="empty-state"><div class="empty-icon"><x-icon name="document" /></div><div class="empty-text"><h3>Belum Ada Data Penagihan</h3><p>Mulai dengan menambahkan data penagihan pertama.</p></div></td></tr>
                                @endforelse
                                <tr id="searchEmptyRow" class="hidden"><td colspan="5" class="empty-state"><div class="empty-icon text-muted-premium"><x-icon name="search-empty" class="empty-icon-large-svg" /></div><div class="empty-text text-center"><h3 class="empty-title">Tidak ada data yang cocok</h3><p class="empty-subtitle">Coba ubah kata kunci atau filter Anda.</p></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pagination-container" id="paginationContainer">
                    <div class="pagination-info" id="paginationInfo">Menampilkan 0-0 dari 0 data</div>
                    <div class="pagination-controls" id="paginationControls"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-container">
            <div class="card-header rounded-t-16">
                <h2>Edit Penagihan & Pembayaran</h2>
                <button onclick="closeEditModal()" class="modal-close-btn">
                    <x-icon name="close" class="icon-20" />
                </button>
            </div>
            <div class="form-body">
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label>Nama Asuransi <span class="text-danger">*</span></label>
                        <select id="edit_ruang_lingkup_id" name="ruang_lingkup_id" class="searchable-select" required>
                            @foreach($ruangLingkups as $rl)
                                <option value="{{ $rl->id }}">{{ $rl->nama_asuransi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jangka Waktu (Hari) <span class="text-danger">*</span></label>
                        <input type="number" id="edit_jangka_waktu_hari" name="jangka_waktu_hari" class="form-input-minimal" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Hari <span class="text-danger">*</span></label>
                        <select id="edit_tipe_hari" name="tipe_hari" class="form-input-minimal" required>
                            <option value="Kalender">Hari Kalender</option>
                            <option value="Hari Kerja">Hari Kerja</option>
                        </select>
                    </div>
                    <div class="form-group mb-8">
                        <label>Keterangan</label>
                        <textarea id="edit_keterangan" name="keterangan" class="form-input-minimal" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions-premium">
                        <button type="button" onclick="closeEditModal()" class="btn-secondary">
                            Batal
                        </button>
                        <button type="submit" class="btn-premium-submit">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery & Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Pagination Variables
        let allRows = [];
        let filteredRows = [];
        let currentPage = 1;
        let itemsPerPage = 10;

        $(document).ready(function() {
            $('.searchable-select').select2({ width: '100%' });

            // Init Pagination
            allRows = Array.from(document.querySelectorAll('.table-row'));
            filteredRows = [...allRows];
            
            if (allRows.length > 0) {
                updatePagination();
            } else {
                document.getElementById('paginationContainer').style.display = 'none';
            }
        });

        window.filterDataTable = function() {
            const searchValue = document.getElementById('tableSearchInput').value.toLowerCase();
            const monthValue = $('#monthFilter').val();
            const yearValue = $('#yearFilter').val();
            const tipeHariValue = $('#tipeHariFilter').val();
                            document.getElementById('clearSearch').style.display = searchValue.length > 0 ? 'flex' : 'none';
                            document.getElementById('clearMonthFilter').classList.toggle('hidden', !monthValue);
                            document.getElementById('clearYearFilter').classList.toggle('hidden', !yearValue);
                            document.getElementById('clearTipeHariFilter').classList.toggle('hidden', !tipeHariValue);
            showAutocomplete(searchValue);
            filteredRows = allRows.filter(row => {
                const searchData = row.getAttribute('data-search') || '';
                const rawBulan = row.getAttribute('data-bulan') || '';
                const rawTahun = row.getAttribute('data-tahun') || '';
                // Pad bulan only when it has a value, empty means no tanggal_reminder
                const rowBulan = rawBulan ? rawBulan.toString().padStart(2, '0') : '';
                const rowTahun = rawTahun.toString();
                let show = true;
                if(searchValue && !searchData.includes(searchValue)) show = false;
                // If filter is active but row has no date, hide the row
                if(monthValue && rowBulan !== monthValue) show = false;
                if(yearValue && rowTahun !== yearValue) show = false;
                if(tipeHariValue) {
                    const rowTipeHari = row.getAttribute('data-tipe-hari') || '';
                    if(rowTipeHari !== tipeHariValue) show = false;
                }
                return show;
            });
            currentPage = 1;
            updatePagination();
        };

        window.clearSearchInput = function() {
            document.getElementById('tableSearchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            document.getElementById('autocompleteList').style.display = 'none';
            filterDataTable();
        };

        window.clearTipeHariFilter = function() {
            document.getElementById('tipeHariFilter').value = '';
            document.getElementById('clearTipeHariFilter').classList.add('hidden');
            filterDataTable();
        };

        window.clearMonthYearFilter = function(type) {
            if (type === 'month') {
                document.getElementById('monthFilter').value = '';
                document.getElementById('clearMonthFilter').classList.add('hidden');
            } else if (type === 'year') {
                document.getElementById('yearFilter').value = '';
                document.getElementById('clearYearFilter').classList.add('hidden');
            }
            filterDataTable();
        };

        function buildSuggestions() {
            const set = new Set();
            allRows.forEach(row => {
                const s = row.getAttribute('data-search') || '';
                s.split(' ').forEach(w => { if (w.length > 1) set.add(w.charAt(0).toUpperCase() + w.slice(1)); });
            });
            return Array.from(set).sort();
        }

        function showAutocomplete(query) {
            const list = document.getElementById('autocompleteList');
            if (!query || query.length < 1) { list.style.display = 'none'; return; }
            const suggestions = buildSuggestions().filter(s => s.toLowerCase().includes(query)).slice(0, 8);
            if (!suggestions.length) { list.style.display = 'none'; return; }
            list.innerHTML = suggestions.map(s => `<div class="autocomplete-item" onclick="selectSuggestion('${s.replace(/'/g, "\\'")}')">${s}</div>`).join('');
            list.style.display = 'block';
        }

        function selectSuggestion(val) {
            document.getElementById('tableSearchInput').value = val;
            document.getElementById('autocompleteList').style.display = 'none';
            document.getElementById('clearSearch').style.display = 'flex';
            filterDataTable();
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-box-advanced')) {
                const list = document.getElementById('autocompleteList');
                if (list) list.style.display = 'none';
            }
        });

        window.changeItemsPerPage = function() {
            itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
            currentPage = 1;
            updatePagination();
        };

        window.goToPage = function(page) {
            currentPage = page;
            updatePagination();
        };

        function updatePagination() {
            const totalItems = filteredRows.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            
            allRows.forEach(row => row.style.display = 'none');
            
            const emptyRow = document.getElementById('emptyRow');
            const searchEmptyRow = document.getElementById('searchEmptyRow');
            if (emptyRow) emptyRow.style.display = (allRows.length === 0) ? '' : 'none';
            if (searchEmptyRow) searchEmptyRow.style.display = (allRows.length > 0 && filteredRows.length === 0) ? '' : 'none';

            if (totalItems === 0) {
                document.getElementById('paginationContainer').style.display = 'none';
                return;
            }

            document.getElementById('paginationContainer').style.display = 'flex';

            const startIdx = (currentPage - 1) * itemsPerPage;
            const endIdx = Math.min(startIdx + itemsPerPage, totalItems);

            filteredRows.slice(startIdx, endIdx).forEach((row, index) => {
                row.style.display = '';
                const noCell = row.querySelector('.row-number');
                if (noCell) noCell.textContent = startIdx + index + 1;
            });

            document.getElementById('paginationInfo').textContent = `Menampilkan ${startIdx + 1}-${endIdx} dari ${totalItems} data`;
            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            const container = document.getElementById('paginationControls');
            let html = '';

            html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">
                        <x-icon name="chevron-left" class="icon-16" />
                    </button>`;

            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            if (startPage > 1) {
                html += `<button class="page-btn" onclick="goToPage(1)">1</button>`;
                if (startPage > 2) html += `<span class="pagination-ellipsis">...</span>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<span class="pagination-ellipsis">...</span>`;
                html += `<button class="page-btn" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }

            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">
                        <x-icon name="chevron-right" class="icon-16" />
                     </button>`;

            container.innerHTML = html;
        }

        window.openEditModal = function(item) {
            $('#editForm').attr('action', '/admin/penagihan-pembayaran/' + item.id);
            $('#edit_ruang_lingkup_id').val(item.ruang_lingkup_id).trigger('change');
            $('#edit_jangka_waktu_hari').val(item.jangka_waktu_hari);
            $('#edit_tipe_hari').val(item.tipe_hari || 'Kalender');
            $('#edit_keterangan').val(item.keterangan || '');
            $('#editModal').addClass('active');
        };

        window.closeEditModal = function() {
            $('#editModal').removeClass('active');
        };

        $(document).click(function(e) {
            if ($(e.target).is('#editModal')) closeEditModal();
        });
    </script>
</x-app-layout>
