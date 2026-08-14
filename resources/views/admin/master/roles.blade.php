<x-app-layout>
    <x-slot name="title">Manajemen Role</x-slot>

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = 'Kelola tingkatan akses dan wewenang pengguna dalam sistem.'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush


    <div class="premium-container">

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <x-icon name="users" class="icon-24" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $roles->count() }}</span>
                    <span class="label">Total Role</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <x-icon name="user-group" class="icon-24" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $roles->sum('users_count') }}</span>
                    <span class="label">User Terdaftar</span>
                </div>
            </div>
        </div>

        <div class="content-layout">
            <!-- Sidebar: Form -->
            <div class="premium-card">
                <div class="card-header">
                    <h2>Tambah Role</h2>
                    <x-icon name="plus-circle" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.roles.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-input-minimal"
                                placeholder="Contoh: Manager, Staff" required>
                        </div>
                        <div class="form-group mb-8">
                            <label>Key Sistem <span class="text-danger">*</span></label>
                            <input type="text" name="key" class="form-input-minimal"
                                placeholder="Contoh: manager, staff" required>
                            <div class="text-xs-muted mt-1">Kunci internal
                                (huruf kecil, tanpa spasi)</div>
                        </div>

                        <button type="submit" class="btn-premium-submit">
                            <x-icon name="plus-circle-solid" class="icon-18" />
                            Simpan Role
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
                            <input type="text" id="tableSearchInput" oninput="filterDataTable()" placeholder="Cari role..." class="form-input-search">
                            <button id="clearSearch" onclick="clearSearchInput()" class="clear-search-btn">
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
                                    <th class="th-min-250">Role</th>
                                    <th class="th-min-160">Kunci Sistem</th>
                                    <th class="text-center th-min-120">Jumlah User</th>
                                    <th class="text-center th-min-70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($roles as $index => $role)
                                    @php $isDefault = $role->is_system; @endphp
                                    <tr class="table-row" data-search="{{ strtolower($role->name . ' ' . $role->key) }}" data-nama="{{ strtolower($role->name) }}">
                                        <td class="number-cell text-center font-bold-700 text-muted-premium"><span class="row-number">{{ $index + 1 }}</span></td>
                                        <td>
                                            <div class="font-bold-700 color-slate-900">{{ $role->name }}</div>
                                            @if($isDefault)
                                                <div class="badge-default-role">
                                                    <x-icon name="shield-exclamation-solid" />
                                                    Default
                                                </div>
                                            @endif
                                        </td>
                                        <td><code class="key-badge">{{ $role->key }}</code></td>
                                        <td class="text-center">
                                            <span class="badge-premium badge-slate">
                                                <x-icon name="users-solid" />
                                                {{ $role->users_count }}
                                            </span>
                                        </td>
                                        <td class="actions-cell text-center">
                                            <div class="action-buttons flex-col-center">
                                                <button onclick="openEditModal({{ $role->id }}, '{{ addslashes($role->name) }}', '{{ addslashes($role->key) }}', {{ $isDefault ? 'true' : 'false' }})" class="action-btn edit-btn" title="Edit Role">
                                                    <x-icon name="edit" />
                                                </button>
                                                @if(!$isDefault)
                                                    <x-delete-modal
                                                        action="{{ route('admin.roles.destroy', $role) }}"
                                                        label="{{ $role->name }}"
                                                        class="action-btn delete-btn"
                                                        title="Hapus"
                                                    >
                                                        <x-icon name="trash" class="icon-14" />
                                                    </x-delete-modal>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow"><td colspan="5" class="empty-state"><div class="empty-icon"><x-icon name="document" /></div><div class="empty-text"><h3>Belum ada role yang terdaftar</h3><p>Mulai dengan menambahkan role pertama.</p></div></td></tr>
                                @endforelse
                                <tr id="searchEmptyRow" class="hidden"><td colspan="5" class="empty-state"><div class="empty-icon text-muted-premium"><x-icon name="search-empty" class="empty-icon-large-svg" /></div><div class="empty-text text-center"><h3 class="empty-title">Tidak ada data yang cocok</h3><p class="empty-subtitle">Coba ubah kata kunci pencarian Anda.</p></div></td></tr>
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
    <x-modal id="editModal" title="Edit Role">
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Role <span class="text-danger">*</span></label>
                <input type="text" id="edit_name" name="name" class="form-input-minimal" required>
            </div>
            <div class="form-group mb-8">
                <label>Key Sistem <span class="text-danger">*</span></label>
                <input type="text" id="edit_key" name="key" class="form-input-minimal" required>
                <div class="text-xs-muted mt-1">Kunci internal (huruf
                    kecil, tanpa spasi)</div>
            </div>
            <div class="form-group mb-8">
                <label>Permissions</label>
                <div class="flex-between-center mb-1">
                    <span class="text-xs-muted">Centang permission yang dimiliki role ini</span>
                    <span>
                        <button type="button" onclick="toggleAllPerms(true)" class="btn-secondary" style="font-size:11px;padding:2px 8px">Semua</button>
                        <button type="button" onclick="toggleAllPerms(false)" class="btn-secondary" style="font-size:11px;padding:2px 8px">Kosongkan</button>
                    </span>
                </div>
                <div class="perm-checklist" style="max-height:220px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;padding:0.75rem">
                    @forelse($permissions as $perm)
                        <label class="flex-between-center" style="padding:3px 0;cursor:pointer">
                            <span><code class="key-badge">{{ $perm->name }}</code></span>
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="perm-check">
                        </label>
                    @empty
                        <p class="text-xs-muted">Belum ada permission.</p>
                    @endforelse
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="submit" form="editForm" class="btn-premium-submit">Simpan Perubahan</button>
        </x-slot>
    </x-modal>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Pagination Variables
        let allRows = [];
        let filteredRows = [];
        let currentPage = 1;
        let itemsPerPage = 10;

        $(document).ready(function () {
            // Init Pagination
            allRows = Array.from(document.querySelectorAll('.table-row'));
            filteredRows = [...allRows];

            if (allRows.length > 0) {
                updatePagination();
            } else {
                document.getElementById('paginationContainer').style.display = 'none';
            }
        });

        window.filterDataTable = function () {
            const searchValue = document.getElementById('tableSearchInput').value.toLowerCase();
            document.getElementById('clearSearch').style.display = searchValue.length > 0 ? 'flex' : 'none';
            filteredRows = allRows.filter(row => {
                const searchData = row.getAttribute('data-search') || '';
                return searchData.includes(searchValue);
            });
            currentPage = 1;
            updatePagination();
        };

        window.clearSearchInput = function () {
            document.getElementById('tableSearchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            filterDataTable();
        };

        window.changeItemsPerPage = function () {
            itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
            currentPage = 1;
            updatePagination();
        };

        window.goToPage = function (page) {
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

            if (startPage > 1) {
                html += `<button class="page-btn" onclick="goToPage(1)">1</button>`;
                if (startPage > 2) html += `<span class="text-muted-premium mx-1">...</span>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<span class="text-muted-premium mx-1">...</span>`;
                html += `<button class="page-btn" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }

            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">
                        <x-icon name="chevron-right" class="icon-16" />
                     </button>`;

            container.innerHTML = html;
        }

        const rolePerms = @json($roles->mapWithKeys(fn ($r) => [$r->id => $r->permissions->pluck('id')->all()])->all());

        window.toggleAllPerms = function (checked) {
            document.querySelectorAll('.perm-check').forEach(el => { el.checked = checked; });
        };

        window.openEditModal = function (id, name, key, isDefault) {
            $('#editForm').attr('action', '/admin/roles/' + id);
            $('#edit_name').val(name);
            $('#edit_key').val(key);
            $('#edit_key').prop('readonly', isDefault);
            if (isDefault) {
                $('#edit_key').css('background-color', '#f1f5f9');
            } else {
                $('#edit_key').css('background-color', '#f8fafc');
            }
            document.querySelectorAll('.perm-check').forEach(el => { el.checked = false; });
            (rolePerms[id] || []).forEach(function (pid) {
                const el = document.querySelector('.perm-check[value="' + pid + '"]');
                if (el) { el.checked = true; }
            });
            XModal.open('editModal');
        };

        window.closeEditModal = function () {
            XModal.close('editModal');
        };
    </script>
</x-app-layout>