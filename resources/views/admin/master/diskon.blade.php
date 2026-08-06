<x-app-layout>
    <x-slot name="title">Diskon Asuransi</x-slot>

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = 'Kelola pengaturan dan persentase diskon asuransi rekanan rumah sakit.'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush




    <div class="premium-container">
        <!-- Header -->
        {{-- Header moved to Navbar --}}

        <div class="content-layout">
            <!-- Sidebar: Form -->
            <div class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Tambah Diskon</h2>
                    <x-icon name="tag" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.diskon.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nama Asuransi <span class="text-danger">*</span></label>
                            <input type="hidden" name="ruang_lingkup_id" id="ruang_lingkup_id" value="">
                            <div class="input-container">
                                <input type="text" id="ruang_lingkup_input" class="form-input-minimal"
                                       placeholder="Ketik nama asuransi..."
                                       autocomplete="off">
                                <div id="form_rl_autocomplete" class="rl-autocomplete"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Persentase Diskon (%) <span class="text-danger">*</span></label>
                            <input type="number" name="persentase_diskon" class="form-input-minimal" placeholder="Contoh: 15" min="0" max="100" step="1" required>
                        </div>
                        <div class="form-group mb-8">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-input-minimal" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
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
                                    <th class="text-center th-min-120">Diskon (%)</th>
                                    <th class="th-min-300">Keterangan</th>
                                    <th class="text-center th-min-70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($diskons as $index => $item)
                                <tr class="table-row" data-search="{{ strtolower(($item->ruangLingkup->nama_asuransi ?? '') . ' ' . $item->persentase_diskon . ' ' . $item->keterangan) }}" data-nama="{{ strtolower($item->ruangLingkup->nama_asuransi ?? '') }}">
                                    <td class="number-cell text-center font-bold-700 text-muted-premium"><span class="row-number">{{ $index + 1 }}</span></td>
                                    <td class="font-bold-700 color-slate-900">{{ $item->ruangLingkup->nama_asuransi ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge-premium badge-teal">{{ number_format($item->persentase_diskon, 0) }}%</span>
                                    </td>
                                    <td class="color-slate-600">{{ $item->keterangan ?: '-' }}</td>
                                    <td class="actions-cell text-center">
                                        <div class="action-buttons flex-col-center">
                                            <button onclick='openEditModal(@json($item))' class="action-btn edit-btn" title="Edit">
                                                <x-icon name="edit" />
                                            </button>
                                            <x-delete-modal
                                                action="{{ route('admin.diskon.destroy', $item) }}"
                                                label="{{ $item->ruangLingkup->nama_asuransi ?? 'diskon ini' }}"
                                                class="action-btn delete-btn"
                                                title="Hapus"
                                            >
                                                <x-icon name="trash" class="icon-14" />
                                            </x-delete-modal>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow"><td colspan="5" class="empty-state"><div class="empty-icon"><x-icon name="document" /></div><div class="empty-text"><h3>Belum Ada Data Diskon</h3><p>Mulai dengan menambahkan diskon pertama.</p></div></td></tr>
                                @endforelse
                                <tr id="searchEmptyRow" class="hidden"><td colspan="5" class="empty-state"><div class="empty-icon text-muted-premium"><x-icon name="search-empty" /></div><div class="empty-text text-center"><h3 class="empty-title">Tidak ada data yang cocok</h3><p class="empty-subtitle">Coba ubah kata kunci pencarian Anda.</p></div></td></tr>
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
                <h2>Edit Diskon Asuransi</h2>
                <button onclick="closeEditModal()" class="modal-close-btn">
                    <x-icon name="close" class="icon-20" />
                </button>
            </div>
            <div class="form-body">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Pilih Asuransi <span class="text-danger">*</span></label>
                        <select id="edit_ruang_lingkup_id" name="ruang_lingkup_id" class="searchable-select-edit" required>
                            @foreach($ruangLingkups as $rl)
                                <option value="{{ $rl->id }}">{{ $rl->nama_asuransi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Persentase Diskon (%) <span class="text-danger">*</span></label>
                        <input type="number" id="edit_persentase_diskon" name="persentase_diskon" class="form-input-minimal" min="0" max="100" step="1" required>
                    </div>

                    <div class="form-group mb-8">
                        <label>Keterangan</label>
                        <textarea id="edit_keterangan" name="keterangan" class="form-input-minimal" rows="3"></textarea>
                    </div>

                    <div class="form-actions-premium">
                        <button type="button" onclick="closeEditModal()" class="btn-secondary">
                            Batal
                        </button>
                        <button type="submit" class="btn-premium-submit w-auto">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Data and Pagination Variables
        let allRows = [];
        let filteredRows = [];
        let currentPage = 1;
        let itemsPerPage = 10;

        document.addEventListener('DOMContentLoaded', function() {
            // Init Pagination
            allRows = Array.from(document.querySelectorAll('.table-row'));
            filteredRows = [...allRows];
            
            if (allRows.length > 0) {
                updatePagination();
            } else {
                document.getElementById('paginationContainer').style.display = 'none';
            }
        });

        function filterDataTable() {
            const searchTerm = document.getElementById('tableSearchInput').value.toLowerCase();
            document.getElementById('clearSearch').style.display = searchTerm.length > 0 ? 'flex' : 'none';
            showAutocomplete(searchTerm);
            filteredRows = allRows.filter(row => {
                const searchData = row.getAttribute('data-search') || '';
                return searchData.includes(searchTerm);
            });
            currentPage = 1;
            updatePagination();
        }

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

        // Change items per page
        function changeItemsPerPage() {
            itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
            currentPage = 1;
            updatePagination();
        }

        // Change page
        function goToPage(page) {
            currentPage = page;
            updatePagination();
        }

        // Pagination Logic
        function updatePagination() {
            const totalItems = filteredRows.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            
            // Hide all rows first
            allRows.forEach(row => row.style.display = 'none');
            
            // Show empty state if no results
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

            // Show paginated rows and update row numbers
            filteredRows.slice(startIdx, endIdx).forEach((row, index) => {
                row.style.display = '';
                const noCell = row.querySelector('.row-number');
                if (noCell) {
                    noCell.textContent = startIdx + index + 1;
                }
            });

            // Update info text
            document.getElementById('paginationInfo').textContent = `Menampilkan ${startIdx + 1}-${endIdx} dari ${totalItems} data`;

            // Build pagination buttons
            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            const container = document.getElementById('paginationControls');
            let html = '';

            // Prev Button
            html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">
                        <x-icon name="chevron-left" class="icon-16" />
                     </button>`;

            // Page Numbers
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            if (startPage > 1) {
                html += `<button class="page-btn" onclick="goToPage(1)">1</button>`;
                if (startPage > 2) html += `<span class="page-ellipsis">...</span>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<span class="page-ellipsis">...</span>`;
                html += `<button class="page-btn" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }

            // Next Button
            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">
                        <x-icon name="chevron-right" class="icon-16" />
                     </button>`;

            container.innerHTML = html;
        }

        // Modal Functions
        function openEditModal(item) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            
            form.action = `/admin/master-diskon/${item.id}`;
            
            document.getElementById('edit_persentase_diskon').value = item.persentase_diskon;
            document.getElementById('edit_keterangan').value = item.keterangan || '';
            
            modal.classList.add('active');

            // Initialize select2 inside modal after it's shown
            $('#edit_ruang_lingkup_id').val(item.ruang_lingkup_id).trigger('change');
            $('#edit_ruang_lingkup_id').select2({
                dropdownParent: $('#editModal')
            });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
            if ($('#edit_ruang_lingkup_id').hasClass("select2-hidden-accessible")) {
                $('#edit_ruang_lingkup_id').select2('destroy');
            }
        }

        // Close modal when clicking outside
        $(document).click(function(e) {
            if ($(e.target).is('#editModal')) closeEditModal();
        });

        // Autocomplete for Nama Asuransi
        (function() {
            const input = document.getElementById('ruang_lingkup_input');
            const hidden = document.getElementById('ruang_lingkup_id');
            const ac = document.getElementById('form_rl_autocomplete');
            if (!input) return;

            @php
                $rlJsonData = $ruangLingkups->map(fn($rl) => [
                    'id' => $rl->id,
                    'nama' => $rl->nama_asuransi,
                    'existing' => in_array($rl->id, $existingRlIds),
                ])->values();
            @endphp
            const ruangLingkupData = @json($rlJsonData);
            let selecting = false;

            @if($ruangLingkups->isEmpty())
                console.warn('Tidak ada asuransi aktif tersedia untuk dipilih.');
            @endif

            function renderDropdown(query) {
                const q = query.toLowerCase();
                const filtered = q
                    ? ruangLingkupData.filter(rl => rl.nama.toLowerCase().includes(q))
                    : ruangLingkupData;
                const sorted = [...filtered].sort((a, b) => (a.existing === b.existing ? 0 : a.existing ? 1 : -1));
                const matches = q ? sorted.slice(0, 10) : sorted;

                if (matches.length === 0) {
                    ac.style.display = 'none';
                    return;
                }

                ac.innerHTML = matches.map((rl, i) =>
                    `<div class="rl-autocomplete-item ${rl.existing ? 'disabled' : ''}" data-index="${i}" data-id="${rl.id}" data-nama="${rl.nama}" ${rl.existing ? 'data-disabled="1"' : ''}>${rl.nama}${rl.existing ? ' <span class="hint-existing">(sudah ada)</span>' : ''}</div>`
                ).join('');
                ac.style.display = 'block';
                ac._index = -1;
            }

            input.addEventListener('focus', function () {
                if (selecting) return;
                renderDropdown(this.value);
            });

            input.addEventListener('click', function () {
                if (selecting) return;
                if (ac.style.display !== 'block') {
                    renderDropdown(this.value);
                }
            });

            input.addEventListener('input', function () {
                if (selecting) { selecting = false; return; }
                if (!this.value) hidden.value = '';
                renderDropdown(this.value);
            });

            input.addEventListener('keydown', function (e) {
                const items = ac.querySelectorAll('.rl-autocomplete-item:not(.disabled)');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (ac._index >= 0) items[ac._index].classList.remove('active');
                    ac._index = (ac._index + 1) % items.length;
                    items[ac._index].classList.add('active');
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (ac._index >= 0) items[ac._index].classList.remove('active');
                    ac._index = ac._index <= 0 ? items.length - 1 : ac._index - 1;
                    items[ac._index].classList.add('active');
                } else if (e.key === 'Enter') {
                    const active = ac.querySelector('.rl-autocomplete-item.active');
                    if (active) {
                        e.preventDefault();
                        selectItem(active);
                    }
                } else if (e.key === 'Escape') {
                    ac.style.display = 'none';
                }
            });

            ac.addEventListener('click', function (e) {
                const item = e.target.closest('.rl-autocomplete-item');
                if (item && !item.classList.contains('disabled')) selectItem(item);
            });

            document.addEventListener('click', function (e) {
                if (!input.contains(e.target) && !ac.contains(e.target)) {
                    ac.style.display = 'none';
                }
            });

            function selectItem(item) {
                selecting = true;
                input.value = item.dataset.nama;
                hidden.value = item.dataset.id;
                ac.style.display = 'none';
            }

            input.closest('form')?.addEventListener('submit', function () {
                if (input.value && !hidden.value) {
                    input.value = '';
                }
            });

        })();
    </script>
</x-app-layout>
