<x-app-layout>
    <x-slot name="title">Manajemen Permission</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Kelola identifier permission yang digunakan authorization (database).'; d.style.display = 'block'; } })();
    </script>
    @endpush

    @php
        $trashIcon = '<svg class="icon-14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';

        $permissionRows = $permissions->map(function ($perm) use ($trashIcon) {
            return [
                'search' => strtolower(($perm->name.' '.($perm->display_name ?? '')).' '.(int) $perm->roles_count),
                'cells' => [
                    'name' => '<code class="key-badge">'.e($perm->name).'</code>',
                    'display_name' => e($perm->display_name ?: '-'),
                    'roles_count' => '<span class="badge-premium badge-teal">'.(int) $perm->roles_count.'</span>',
                ],
                'cols' => [
                    'name' => strtolower($perm->name),
                    'display_name' => strtolower($perm->display_name ?? ''),
                ],
                'actions' => '<button type="button" data-dm-trigger data-dm-action="'.route('admin.permissions.destroy', $perm).'" data-dm-label="permission '.e($perm->name).'" class="action-btn delete-btn" title="Hapus">'.$trashIcon.'</button>',
            ];
        })->values();
    @endphp

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

            <x-data-table
                id="permissionsTable"
                title="Daftar Permission"
                subtitle="Identifier permission yang dipakai authorization (database)"
                :badge="$permissions->count()"
                :columns="[
                    ['label' => 'No', 'key' => '__no', 'center' => true, 'width' => 50],
                    ['label' => 'Nama', 'key' => 'name', 'searchable' => true, 'placeholder' => 'Cari nama...'],
                    ['label' => 'Tampilan', 'key' => 'display_name', 'searchable' => true, 'placeholder' => 'Cari tampilan...'],
                    ['label' => 'Jml Role', 'key' => 'roles_count', 'center' => true, 'width' => 90],
                    ['label' => 'Aksi', 'key' => '__actions', 'center' => true, 'width' => 70],
                ]"
                :rows="$permissionRows"
                empty-text="Belum ada permission. Tambahkan permission pertama."
            />
        </div>
    </div>

    <div style="display:none"><x-delete-modal action="#" label=""></x-delete-modal></div>
</x-app-layout>
