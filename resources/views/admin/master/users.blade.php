<x-app-layout>
    <x-slot name="title">Manajemen User</x-slot>

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = 'Kelola hak akses dan profil pengguna sistem monitoring.'; descEl.style.display = 'block'; }
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
                    <span class="value">{{ $users->count() }}</span>
                    <span class="label">Total User</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <x-icon name="shield-check" class="icon-24" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $users->filter(fn($u) => strtolower($u->role?->key) === 'admin')->count() }}</span>
                    <span class="label">Administrators</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon emerald">
                    <x-icon name="user" class="icon-24" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $users->filter(fn($u) => strtolower($u->role?->key) !== 'admin')->count() }}</span>
                    <span class="label">Staff Members</span>
                </div>
            </div>
        </div>

        <div class="premium-toolbar mb-3 d-flex justify-content-end">
            <form method="POST" action="{{ route('admin.users.sync-simutu') }}" onsubmit="return confirm('Sinkronkan data pegawai dari Simutu? User yang belum ada akan dibuat otomatis.');">
                @csrf
                <button type="submit" class="btn-premium-submit">
                    <x-icon name="refresh" class="icon-16" />
                    Sync dari Simutu
                </button>
            </form>
        </div>

        <div class="content-layout">
            <!-- Sidebar: Form -->
            <div class="premium-card">
                <div class="card-header">
                    <h2>Tambah User</h2>
                    <x-icon name="plus-circle" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-input-minimal" placeholder="Nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label>NIP</label>
                            <input type="text" name="nip" class="form-input-minimal" placeholder="NIP pegawai">
                        </div>
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-input-minimal" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-input-minimal" placeholder="email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-input-minimal" placeholder="********" required minlength="8" oninput="validatePasswordLength(this)">
                            <span class="password-hint" id="password-hint-create">Minimal 8 karakter</span>
                        </div>
                        <div class="form-group">
                            <label>Departemen</label>
                            <x-searchable-select
                                name="organization_unit_id"
                                :options="$departments->map(fn ($d) => ['value' => $d->id, 'text' => $d->name])"
                                :value="old('organization_unit_id', '')"
                                placeholder="Pilih Departemen"
                                nullable
                            />
                        </div>
                        <div class="form-group">
                            <label>Role <span class="text-danger">*</span></label>
                            <x-searchable-select
                                name="role_id"
                                :options="$roles->map(fn ($role) => ['value' => $role->id, 'text' => $role->name])"
                                :value="old('role_id', '')"
                                placeholder="Pilih Role"
                                required
                            />
                        </div>

                        <button type="submit" class="btn-premium-submit">
                            <x-icon name="plus-circle-solid" class="icon-18" />
                            Daftarkan User
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
                            <input type="text" id="tableSearchInput" oninput="filterDataTable()" placeholder="Cari user..." class="form-input-search">
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
                                    <th class="th-min-300">Profil Pengguna</th>
                                    <th class="th-min-120">NIP</th>
                                    <th class="th-min-140">Username</th>
                                    <th class="th-min-140">Departemen</th>
                                    <th class="text-center th-min-120">Role</th>
                                    <th class="text-center th-min-70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr class="table-filter-row">
                                    <td></td>
                                    <td>
                                        <div class="filter-input-container" id="wrap-filterNama">
                                            <input type="text" id="filterNama" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari nama...">
                                            <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('filterNama')" title="Clear"><x-icon name="close" class="" /></button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="filter-input-container" id="wrap-filterNip">
                                            <input type="text" id="filterNip" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari NIP...">
                                            <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('filterNip')" title="Clear"><x-icon name="close" class="" /></button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="filter-input-container" id="wrap-filterUsername">
                                            <input type="text" id="filterUsername" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari username...">
                                            <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('filterUsername')" title="Clear"><x-icon name="close" class="" /></button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="filter-input-container" id="wrap-filterDept">
                                            <input type="text" id="filterDept" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari departemen...">
                                            <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('filterDept')" title="Clear"><x-icon name="close" class="" /></button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="filter-input-container" id="wrap-filterRole">
                                            <input type="text" id="filterRole" oninput="handleFilterInput(this)" autocomplete="off" class="filter-select" placeholder="Cari role...">
                                            <button type="button" class="clear-filter-btn" onclick="clearFilterWrap('filterRole')" title="Clear"><x-icon name="close" class="" /></button>
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                                @forelse($users as $index => $user)
                                <tr class="table-row"
                                    data-nama="{{ strtolower($user->name . ' ' . $user->email) }}"
                                    data-nip="{{ strtolower($user->nip ?? '') }}"
                                    data-username="{{ strtolower($user->username) }}"
                                    data-dept="{{ $user->organization_unit_id ?? '' }}"
                                    data-role="{{ $user->role_id }}"
                                    data-dept-nama="{{ strtolower($user->organizationUnit?->name ?? '') }}"
                                    data-role-nama="{{ strtolower($user->role?->name ?? '') }}"
                                    data-search="{{ strtolower($user->name . ' ' . $user->username . ' ' . $user->email . ' ' . ($user->nip ?? '') . ' ' . ($user->organizationUnit?->name ?? '')) }}">
                                    <td class="number-cell text-center font-bold-700 text-muted-premium"><span class="row-number">{{ $index + 1 }}</span></td>
                                    <td>
                                        <div class="flex-col">
                                            <div class="font-bold-700 color-slate-900">{{ $user->name }}</div>
                                            <div class="text-xs-muted">{{ $user->email }}</div>
                                        </div>
                                    </td>
                                    <td><code class="username-tag">{{ $user->nip ?? '-' }}</code></td>
                                    <td><code class="username-tag">@<span>{{ $user->username }}</span></code></td>
                                    <td>
                                        @if($user->organizationUnit)
                                            <span class="badge-premium badge-slate">{{ $user->organizationUnit->name }}</span>
                                        @else
                                            <span class="text-xs-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $isAdmin = $user->isAdmin(); @endphp
                                        <span class="badge-premium {{ $isAdmin ? 'badge-purple' : 'badge-teal' }}">
                                            @if($isAdmin)
                                                <x-icon name="shield-check-solid" />
                                            @endif
                                            {{ $user->role?->name ?? 'No Role' }}
                                        </span>
                                    </td>
                                    <td class="actions-cell text-center">
                                        <div class="action-buttons flex-col-center">
                                            <button onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->username) }}', '{{ addslashes($user->email) }}', '{{ $user->role_id }}', '{{ addslashes($user->nip ?? '') }}', '{{ $user->organization_unit_id }}')" class="action-btn edit-btn" title="Edit User">
                                                <x-icon name="edit" />
                                            </button>
                                            @if($user->id !== auth()->id())
                                                <x-delete-modal
                                                    action="{{ route('admin.users.destroy', $user) }}"
                                                    label="{{ $user->name }}"
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
                                <tr id="emptyRow"><td colspan="7" class="empty-state"><div class="empty-icon"><x-icon name="document" /></div><div class="empty-text"><h3>Belum ada user yang terdaftar</h3><p>Mulai dengan menambahkan user pertama.</p></div></td></tr>
                                @endforelse
                                <tr id="searchEmptyRow" class="hidden"><td colspan="7" class="empty-state"><div class="empty-icon text-muted-premium"><x-icon name="search-empty" class="empty-icon-large-svg" /></div><div class="empty-text text-center"><h3 class="empty-title">Tidak ada data yang cocok</h3><p class="empty-subtitle">Coba ubah kata kunci pencarian Anda.</p></div></td></tr>
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
                <h2>Edit User Profile</h2>
                <button onclick="closeEditModal()" class="modal-close-btn">
                    <x-icon name="close" class="icon-20" />
                </button>
            </div>
            <div class="form-body">
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" id="edit_name" name="name" class="form-input-minimal" required>
                    </div>
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" id="edit_nip" name="nip" class="form-input-minimal">
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="edit_username" name="username" class="form-input-minimal" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="edit_email" name="email" class="form-input-minimal" required>
                    </div>
                    <div class="form-group">
                        <label>Departemen</label>
                        <x-searchable-select
                            name="organization_unit_id"
                            id="edit_dept_select"
                            input-id="edit_dept"
                            :options="$departments->map(fn ($d) => ['value' => $d->id, 'text' => $d->name])"
                            placeholder="Pilih Departemen"
                            nullable
                        />
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <x-searchable-select
                            name="role_id"
                            id="edit_role_select"
                            input-id="edit_role"
                            :options="$roles->map(fn ($role) => ['value' => $role->id, 'text' => $role->name])"
                            placeholder="Pilih Role"
                            required
                        />
                    </div>
                    <div class="form-group mb-8">
                        <label>Password Baru <small class="text-xs-muted">(Kosongkan jika tidak ganti)</small></label>
                        <input type="password" name="password" class="form-input-minimal" placeholder="********" minlength="8" oninput="validatePasswordLength(this)">
                        <span class="password-hint" id="password-hint-edit">Minimal 8 karakter</span>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .page-table tr.table-filter-row td {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.4rem 0.4rem !important;
        }
        .page-table .table-filter-row .filter-input-container {
            width: 100%;
        }
        .page-table .table-filter-row .filter-input-container input.filter-select,
        .page-table .table-filter-row .filter-input-container select.filter-select {
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
        .page-table .table-filter-row .ssd-trigger.filter-select {
            min-height: 30px;
            padding: 0.35rem 0.75rem;
            font-size: 11px;
            background: #ffffff;
            border: 1px solid #94a3b8 !important;
            color: #000000 !important;
        }
        .page-table .table-filter-row .ssd-trigger.filter-select .ssd-value.is-placeholder {
            color: #000000 !important;
        }
    </style>
    <script>
        // Pagination Variables
        let allRows = [];
        let filteredRows = [];
        let currentPage = 1;
        let itemsPerPage = 10;

        $(document).ready(function() {
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
            document.getElementById('clearSearch').style.display = searchValue.length > 0 ? 'flex' : 'none';

            const fNama = (document.getElementById('filterNama').value || '').toLowerCase();
            const fNip = (document.getElementById('filterNip').value || '').toLowerCase();
            const fUsername = (document.getElementById('filterUsername').value || '').toLowerCase();
            const fDept = (document.getElementById('filterDept').value || '').toLowerCase();
            const fRole = (document.getElementById('filterRole').value || '').toLowerCase();

            filteredRows = allRows.filter(row => {
                const searchData = row.getAttribute('data-search') || '';
                if (searchValue && !searchData.includes(searchValue)) return false;
                if (fNama && !(row.getAttribute('data-nama') || '').includes(fNama)) return false;
                if (fNip && !(row.getAttribute('data-nip') || '').includes(fNip)) return false;
                if (fUsername && !(row.getAttribute('data-username') || '').includes(fUsername)) return false;
                if (fDept && !(row.getAttribute('data-dept-nama') || '').includes(fDept)) return false;
                if (fRole && !(row.getAttribute('data-role-nama') || '').includes(fRole)) return false;
                return true;
            });
            currentPage = 1;
            updatePagination();
        };

        window.handleFilterInput = function(el) {
            const wrap = document.getElementById('wrap-' + el.id);
            if (wrap) {
                const btn = wrap.querySelector('.clear-filter-btn');
                if (btn) btn.style.display = el.value ? 'flex' : 'none';
            }
            filterDataTable();
        };

        window.applyUserFilter = function(value, text, inputEl) {
            handleFilterInput(inputEl);
        };

        window.clearFilterWrap = function(id) {
            const el = document.getElementById(id);
            if (el && el.value !== undefined) el.value = '';
            const wrap = document.getElementById('wrap-' + id);
            if (wrap) {
                const btn = wrap.querySelector('.clear-filter-btn');
                if (btn) btn.style.display = 'none';
            }
            if (window.__searchableSelects && window.__searchableSelects[id]) {
                window.__searchableSelects[id].select(null);
            }
            filterDataTable();
        };

        window.clearSearchInput = function() {
            document.getElementById('tableSearchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            filterDataTable();
        };

        window.validatePasswordLength = function(input) {
            const hint = input.nextElementSibling;
            const len = input.value.length;
            if (len === 0) {
                hint.className = 'password-hint';
                hint.textContent = 'Minimal 8 karakter';
            } else if (len < 8) {
                hint.className = 'password-hint invalid';
                hint.textContent = 'terlalu pendek';
            } else {
                hint.className = 'password-hint valid';
                hint.textContent = 'cukup';
            }
        };

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

        window.openEditModal = function(id, name, username, email, role, nip, dept) {
            $('#editForm').attr('action', '{{ route('admin.users.update', '__ID__') }}'.replace('__ID__', id));
            $('#edit_name').val(name);
            $('#edit_username').val(username);
            $('#edit_email').val(email);
            $('#edit_nip').val(nip || '');
            if (window.__searchableSelects && window.__searchableSelects['edit_role']) {
                window.__searchableSelects['edit_role'].select(role);
            }
            if (window.__searchableSelects && window.__searchableSelects['edit_dept']) {
                window.__searchableSelects['edit_dept'].select(dept || null);
            }
            const pwInput = document.querySelector('#editForm input[name="password"]');
            if (pwInput) {
                pwInput.value = '';
                pwInput.dispatchEvent(new Event('input'));
            }
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
