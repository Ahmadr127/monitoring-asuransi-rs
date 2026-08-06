<x-app-layout>
    <x-slot name="title">Master Obat</x-slot>

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = 'Kelola daftar registry obat-obatan yang terintegrasi dengan sistem.'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush



    <div class="premium-container">
        <div id="return_banner" class="return-banner">
            <a id="return_banner_link" href="#" class="return-banner-link">
                <x-icon name="chevron-left" class="icon-14" />
                Kembali ke Form Kerjasama
            </a>
        </div>
        <script>
        (function() {
            var info = (function() {
                try { return JSON.parse(localStorage.getItem('rl_return_to') || 'null'); } catch(e) { return null; }
            })();
            var banner = document.getElementById('return_banner');
            var link = document.getElementById('return_banner_link');
            if (info && info.url && banner && link) {
                link.href = info.url;
                banner.style.display = 'flex';
            }
        })();
        </script>

        <div class="content-layout">
            <!-- Sidebar: Form -->
            <div class="premium-card">
                <div class="card-header">
                    <h2>Tambah Obat</h2>
                    <x-icon name="plus-circle" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.master-obat.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nama Obat <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-input-minimal @error('nama') is-invalid @enderror"
                                placeholder="Contoh: Paracetamol 500mg" value="{{ old('nama') }}" required style="text-transform: uppercase">
                            @error('nama')
                                <div class="invalid-feedback">Obat sudah tersedia dalam sistem</div>
                            @enderror
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
                            <input type="text" id="tableSearchInput" oninput="filterDataTable()"
                                placeholder="Cari obat..."
                                class="form-input-search">
                            <button id="clearSearch" onclick="clearSearchInput()" class="clear-search-btn">
                                <x-icon name="close" />
                            </button>
                            <div id="autocompleteList" class="autocomplete-dropdown"></div>
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
                                    <th class="th-min-300">Nama Obat</th>
                                    <th class="text-center th-min-70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($obat as $index => $item)
                                <tr class="table-row" data-search="{{ strtolower($item->nama) }}" data-nama="{{ strtolower($item->nama) }}">
                                    <td class="number-cell text-center font-bold-700 text-muted-premium">
                                        <span class="row-number">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="font-bold-700 color-slate-900">{{ Str::upper($item->nama) }}</div>
                                    </td>
                                    <td class="actions-cell text-center">
                                        <div class="action-buttons flex-col-center">
                            <button onclick='openEditModal(@json($item))' class="action-btn edit-btn" title="Edit">
                                <x-icon name="edit" />
                            </button>
                                            <x-delete-modal
                                                action="{{ route('admin.master-obat.destroy', $item) }}"
                                                label="{{ Str::upper($item->nama) }}"
                                                class="action-btn delete-btn"
                                                title="Hapus"
                                            >
                                                <x-icon name="trash" class="icon-14" />
                                            </x-delete-modal>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow"><td colspan="3" class="empty-state"><div class="empty-icon"><x-icon name="document" /></div><div class="empty-text"><h3>Belum ada data obat</h3><p>Mulai dengan menambahkan obat pertama.</p></div></td></tr>
                                @endforelse
                                <tr id="searchEmptyRow" class="hidden"><td colspan="3" class="empty-state"><div class="empty-icon text-muted-premium"><x-icon name="search-empty" class="empty-icon-large-svg" /></div><div class="empty-text text-center"><h3 class="empty-title">Tidak ada data yang cocok</h3><p class="empty-subtitle">Coba ubah kata kunci pencarian Anda.</p></div></td></tr>
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
                <h2>Edit Obat</h2>
                <button onclick="closeEditModal()" class="modal-close-btn">
                    <x-icon name="close" class="icon-20" />
                </button>
            </div>
            <div class="form-body">
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label>Nama Obat <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="edit_nama" class="form-input-minimal" required style="text-transform: uppercase">
                    </div>
                    <button type="submit" class="btn-premium-submit">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let allRows = [];
        let filteredRows = [];
        let currentPage = 1;
        let itemsPerPage = 10;

        $(document).ready(function() {
            allRows = Array.from(document.querySelectorAll('.table-row'));
            filteredRows = [...allRows];
            
            if (allRows.length > 0) {
                updatePagination();
            }
        });

        window.filterDataTable = function() {
            const searchValue = document.getElementById('tableSearchInput').value.toLowerCase();
            document.getElementById('clearSearch').style.display = searchValue.length > 0 ? 'flex' : 'none';
            showAutocomplete(searchValue);
            filteredRows = allRows.filter(row => {
                const searchData = row.getAttribute('data-search') || '';
                return searchData.includes(searchValue);
            });
            currentPage = 1;
            updatePagination();
        };

        window.filterByColumn = function(el, attr) { filterDataTable(); };

        window.clearSearchInput = function() {
            document.getElementById('tableSearchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            document.getElementById('autocompleteList').style.display = 'none';
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
            if (searchEmptyRow) searchEmptyRow.style.display = (allRows.length > 0 && totalItems === 0) ? '' : 'none';
            
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

            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }

            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">
                        <x-icon name="chevron-right" class="icon-16" />
                     </button>`;

            container.innerHTML = html;
        }

        window.openEditModal = function(item) {
            $('#editForm').attr('action', '/admin/master-obat/' + item.id);
            $('#edit_nama').val(item.nama);
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
