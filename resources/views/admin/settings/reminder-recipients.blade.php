<x-app-layout>
    <x-slot name="title">Penerima Reminder</x-slot>
    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) {
                descEl.textContent = 'Kelola daftar penerima pengingat (reminder) otomatis untuk masa berlaku kontrak.';
                descEl.style.display = 'block';
            }
        })();
    </script>
    @endpush

    <div class="premium-container">

        <div class="stats-grid">
            <div class="stat-card">
                <div class="header-icon-wrapper mb-3">
                    <x-icon name="user-group" class="icon-20" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $recipients->count() }}</span>
                    <span class="label">Total Penerima</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="header-icon-wrapper mb-3">
                    <x-icon name="shield-check" class="icon-20" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $recipients->where('category', 'internal')->count() }}</span>
                    <span class="label">Internal</span>
                </div>
            </div>

        </div>

        @if($errors->any())
            <div class="alert-premium error">
                <div class="font-bold-700 mb-1">Aksi Gagal</div>
                <ul class="pl-5 m-0">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="content-layout">
            <div class="premium-card">
                <div class="card-header">
                    <div>
                        <h2>Tambah Penerima</h2>
                        <p class="text-xs-muted mt-1">Daftarkan penerima pengingat kontrak baru ke dalam sistem</p>
                    </div>
                    <x-icon name="plus-circle" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.reminder-recipients.store') }}" id="mainForm">
                        @csrf
                        <input type="hidden" name="category" value="internal">

                        <div id="manualFields">
                            <div class="form-group">
                                <label>Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="real_name" class="form-input-minimal" placeholder="Nama lengkap" required>
                            </div>
                            <div class="form-group mb-8">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="real_email" class="form-input-minimal" placeholder="contoh@email.com" required>
                            </div>
                            <div class="form-group mb-8">
                                <label>Tujuan <span class="text-danger">*</span></label>
                                <x-searchable-select
                                    name="purpose"
                                    input-id="real_purpose"
                                    :options="[['value' => 'admin', 'text' => 'Administrasi'], ['value' => 'legal', 'text' => 'Legal'], ['value' => 'marketing', 'text' => 'Marketing'], ['value' => 'farmasi', 'text' => 'Farmasi'], ['value' => 'kasir', 'text' => 'Kasir'], ['value' => 'pendaftaran', 'text' => 'Pendaftaran']]"
                                    :value="old('purpose', '')"
                                    placeholder="Pilih tujuan..."
                                    required
                                />
                            </div>
                        </div>

                        <div class="flex-center-gap-2 mt-2">
                            <button type="submit" class="btn-premium-submit flex-1" id="btnSubmit" onclick="this.disabled=true; this.querySelector('.btn-text').textContent='Menyimpan...'; this.querySelector('.icon-18').style.display='none'; this.querySelector('.spinner-loader').style.display='inline-block'; this.form.submit();">
                                <x-icon name="plus-circle-solid" class="icon-18" />
                                <span class="spinner-loader"></span>
                                <span class="btn-text">Tambahkan ke Registri</span>
                            </button>
                            <button type="button" class="btn-secondary flex-1" onclick="resetFullForm()">
                                <x-icon name="trash" class="icon-18" />
                                Bersihkan Formulir
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="data-table-container">
                <div class="filter-search-section">
                    <div class="search-container">
                        <div class="search-box-advanced sb-max-400">
                            <div class="search-icon">
                                <x-icon name="search" />
                            </div>
                            <input type="text" id="tableSearchInput" onkeyup="filterDataTable()" placeholder="Cari penerima..." class="form-input-search">
                            <button id="clearSearch" onclick="clearSearchInput()" class="clear-search-btn">
                                <x-icon name="close" />
                            </button>
                        </div>
                        <div class="flex-center-gap-2">
                            <div class="table-filter-group">
                                <button onclick="setTableFilter('')" class="table-filter-btn active" id="filter_all">Semua</button>
                            </div>
                            <select id="itemsPerPage" class="filter-select fs-110" onchange="changeItemsPerPage()">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-view">
                    <div class="table-wrapper">
                        <table class="page-table">
                            <thead>
                                <tr>
                                    <th class="text-center th-min-50">No</th>
                                    <th class="th-min-250">Penerima</th>
                                    <th class="th-min-100">Kategori</th>
                                    <th class="th-min-150">Target Monitoring</th>
                                    <th class="th-min-120">Pengingat Terakhir</th>
                                    <th class="text-center th-min-80">Aktif</th>
                                    <th class="text-center th-min-70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($recipients as $index => $recipient)
                                <tr class="table-row" data-category="{{ $recipient->category }}" data-search="{{ strtolower($recipient->name . ' ' . $recipient->email) }}">
                                    <td class="number-cell text-center font-bold-700 text-muted-premium">
                                        <span class="row-number">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="font-bold-700 color-slate-900">{{ $recipient->name }}</div>
                                            <div class="text-xs-muted">{{ $recipient->email }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-premium badge-teal">Internal</span>
                                    </td>
                                    <td>
                                        <span class="badge-premium badge-slate">Global (Semua Kontrak)</span>
                                    </td>
                                    <td>
                                        @if($recipient->last_manual_sent_at)
                                            <div class="font-semibold color-slate-800">{{ $recipient->last_manual_sent_at->translatedFormat('d M Y') }}</div>
                                            <div class="text-xs-muted">{{ $recipient->last_manual_sent_at->diffForHumans() }}</div>
                                        @else
                                            <span class="text-xs-muted italic">Belum pernah</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.reminder-recipients.toggle', $recipient) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <label class="toggle-switch">
                                                <input type="checkbox" onchange="this.form.submit()" {{ $recipient->is_active ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-buttons flex-col-center">
                                            <x-confirm-modal
                                                action="{{ route('admin.reminder-recipients.send-manual', $recipient) }}"
                                                label="{{ $recipient->name }}"
                                                class="action-btn success-btn"
                                                title="Kirim Pengingat Manual"
                                            >
                                                <x-icon name="send" class="icon-14" />
                                            </x-confirm-modal>
                                            <x-delete-modal
                                                action="{{ route('admin.reminder-recipients.destroy', $recipient) }}"
                                                label="{{ $recipient->name }}"
                                                class="action-btn delete-btn"
                                                title="Hapus"
                                            >
                                                <x-icon name="trash" />
                                            </x-delete-modal>

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow">
                                    <td colspan="7" class="empty-state">
                                        <div class="empty-icon">
                                            <x-icon name="document" />
                                        </div>
                                        <div class="empty-text">
                                            <h3>Registri Kosong</h3>
                                            <p>Belum ada penerima pengingat yang terdaftar. Gunakan formulir di atas untuk menambahkan penerima baru.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                <tr id="searchEmptyRow" class="hidden">
                                    <td colspan="7" class="empty-state">
                                        <div class="empty-icon text-muted-premium">
                                            <x-icon name="search-empty" class="empty-icon-large-svg" />
                                        </div>
                                        <div class="empty-text text-center">
                                            <h3 class="empty-title">Tidak ada data yang cocok</h3>
                                            <p class="empty-subtitle">Coba ubah kata kunci pencarian Anda.</p>
                                        </div>
                                    </td>
                                </tr>
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

        window.resetFullForm = function() {
            $('#real_name').val('');
            $('#real_email').val('');
            $('#real_purpose').val('');
            if (window.__searchableSelects && window.__searchableSelects['real_purpose']) {
                window.__searchableSelects['real_purpose'].select(null);
            }
        };

        window.setTableFilter = function(cat) {
            $('.table-filter-btn').removeClass('active');
            if (cat === '') $('#filter_all').addClass('active');
            filterDataTable(cat);
        };

        window.filterDataTable = function(categoryOverride = null) {
            const searchValue = $('#tableSearchInput').val().toLowerCase();
            const categoryValue = categoryOverride !== null ? categoryOverride : ($('.table-filter-btn.active').attr('id') || 'filter_all').replace('filter_', '');
            const finalCategory = categoryValue === 'all' ? '' : categoryValue;

            $('#clearSearch').toggle(searchValue.length > 0);

            filteredRows = allRows.filter(row => {
                const searchData = row.getAttribute('data-search') || '';
                const category = row.getAttribute('data-category');
                return searchData.includes(searchValue) && (finalCategory === '' || category === finalCategory);
            });

            currentPage = 1;
            updatePagination();
        };

        window.clearSearchInput = function() {
            $('#tableSearchInput').val('');
            $('#clearSearch').hide();
            filterDataTable();
        };

        window.changeItemsPerPage = function() {
            itemsPerPage = parseInt($('#itemsPerPage').val());
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
                $('#paginationContainer').hide();
                return;
            }

            $('#paginationContainer').show();

            const startIdx = (currentPage - 1) * itemsPerPage;
            const endIdx = Math.min(startIdx + itemsPerPage, totalItems);

            filteredRows.slice(startIdx, endIdx).forEach((row, index) => {
                row.style.display = '';
                const noCell = row.querySelector('.row-number');
                if (noCell) noCell.textContent = startIdx + index + 1;
            });

            $('#paginationInfo').text('Menampilkan ' + (startIdx + 1) + '-' + endIdx + ' dari ' + totalItems + ' data');
            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            let html = '';
html += '<button class="page-btn"' + (currentPage === 1 ? ' disabled' : '') + ' onclick="goToPage(' + (currentPage - 1) + ')">'
                      + '<svg viewBox="0 0 20 20" fill="currentColor" class="icon-16"><path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>'
                      + '</button>';

            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            for (let i = startPage; i <= endPage; i++) {
                html += '<button class="page-btn' + (i === currentPage ? ' active' : '') + '" onclick="goToPage(' + i + ')">' + i + '</button>';
            }

            html += '<button class="page-btn"' + (currentPage === totalPages ? ' disabled' : '') + ' onclick="goToPage(' + (currentPage + 1) + ')">'
                  + '<svg viewBox="0 0 20 20" fill="currentColor" class="icon-16"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L11.586 10 7.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>'
                  + '</button>';

            document.getElementById('paginationControls').innerHTML = html;
        }
    </script>
</x-app-layout>
