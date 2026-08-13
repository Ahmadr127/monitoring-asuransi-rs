<x-app-layout>
    <x-slot name="title">Manajemen Permission</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Kelola identifier permission yang digunakan authorization (database).'; d.style.display = 'block'; } })();
    </script>
    @endpush

    <div class="premium-container">
        <div class="content-layout">
            <div class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Tambah Permission</h2>
                    <x-icon name="shield-check-solid" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.permissions.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-input-minimal" placeholder="mis. laporan" required style="text-transform:lowercase">
                        </div>
                        <div class="form-group mb-8">
                            <label>Tampilan</label>
                            <input type="text" name="display_name" class="form-input-minimal" placeholder="mis. Laporan">
                        </div>
                        <button type="submit" class="btn-premium-submit w-100">
                            <x-icon name="plus-circle-solid" class="icon-18" />
                            Simpan Permission
                        </button>
                    </form>
                </div>
            </div>

            <div class="data-table-container mb-0">
                <div class="table-view">
                    <div class="table-wrapper">
                        <table class="page-table">
                            <thead>
                                <tr>
                                    <th class="text-center th-min-50">No</th>
                                    <th class="th-min-250">Nama</th>
                                    <th class="th-min-250">Tampilan</th>
                                    <th class="text-center th-min-90">Jml Role</th>
                                    <th class="text-center th-min-70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $index => $perm)
                                <tr class="table-row" data-search="{{ strtolower($perm->name . ' ' . $perm->display_name) }}">
                                    <td class="number-cell text-center font-bold-700 text-muted-premium">{{ $index + 1 }}</td>
                                    <td><code class="key-badge">{{ $perm->name }}</code></td>
                                    <td>{{ $perm->display_name ?: '-' }}</td>
                                    <td class="text-center"><span class="badge-premium badge-teal">{{ $perm->roles_count }}</span></td>
                                    <td class="actions-cell text-center">
                                        <x-delete-modal
                                            action="{{ route('admin.permissions.destroy', $perm) }}"
                                            label="permission {{ $perm->name }}"
                                            class="action-btn delete-btn"
                                            title="Hapus">
                                            <x-icon name="trash" class="icon-14" />
                                        </x-delete-modal>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="empty-state"><p>Belum ada permission. Tambahkan permission pertama.</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>