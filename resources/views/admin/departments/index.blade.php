<x-app-layout>
    <x-slot name="title">Manajemen Departemen</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Kelola departemen, kepala, dan anggota organisasi.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    @php
        $eyeIcon = '<svg class="icon-14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>';
        $editIcon = '<svg class="icon-14" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>';
        $trashIcon = '<svg class="icon-14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';

        $typeOptions = $types->map(fn ($t) => ['value' => strtolower($t->display_name), 'text' => $t->display_name])->values()->all();

        $unitRows = $units->map(function ($unit) use ($eyeIcon, $editIcon, $trashIcon) {
            return [
                'search' => strtolower(
                    ($unit->code.' '.$unit->name.' '.
                    ($unit->type?->display_name ?? '').' '.
                    ($unit->parent?->name ?? '').' '.
                    ($unit->head?->name ?? '')).' '.
                    ($unit->is_active ? 'aktif' : 'non-aktif')
                ),
                'cells' => [
                    'code' => '<code class="key-badge">'.e($unit->code).'</code>',
                    'name' => '<span class="font-bold-700 color-slate-900">'.e($unit->name).'</span>',
                    'type' => '<span class="badge-premium badge-slate">'.e($unit->type?->display_name ?? '-').'</span>',
                    'parent' => e($unit->parent?->name ?? '-'),
                    'head' => e($unit->head?->name ?? '-'),
                    'members' => '<span class="badge-premium badge-teal">'.(int) $unit->members_count.'</span>',
                    'status' => $unit->is_active
                        ? '<span class="badge-premium badge-teal">Aktif</span>'
                        : '<span class="badge-premium badge-slate">Non-Aktif</span>',
                ],
                'cols' => [
                    'code' => strtolower($unit->code),
                    'name' => strtolower($unit->name),
                    'type' => strtolower($unit->type?->display_name ?? ''),
                    'head' => strtolower($unit->head?->name ?? ''),
                    'status' => $unit->is_active ? 'aktif' : 'non-aktif',
                ],
                'actions' => '<div class="action-buttons" style="justify-content:center;gap:0.5rem">'
                    .'<a href="'.route('admin.departments.show', $unit).'" class="action-btn view-btn" title="Detail">'.$eyeIcon.'</a>'
                    .'<a href="'.route('admin.departments.edit', $unit).'" class="action-btn edit-btn" title="Edit">'.$editIcon.'</a>'
                    .'<button type="button" data-dm-trigger data-dm-action="'.route('admin.departments.destroy', $unit).'" data-dm-label="departemen '.e($unit->name).'" class="action-btn delete-btn" title="Hapus">'.$trashIcon.'</button>'
                    .'</div>',
            ];
        })->values();
    @endphp

    <div class="premium-container">
        <div class="premium-card">
            <!-- Header + Aksi -->
            <div class="card-header flex-between-center">
                <div>
                    <h2>Departemen</h2>
                    <p class="text-xs-muted mt-1">{{ $units->count() }} unit &middot; {{ $typeCount }} tipe</p>
                </div>
                <div class="flex-center-gap-2">
                    <a href="{{ route('admin.departments.types.index') }}" class="btn-secondary">
                        <x-icon name="grid" class="icon-16" /> Kelola Tipe
                    </a>
                    <a href="{{ route('admin.departments.create') }}" class="btn-premium-submit w-auto">
                        <x-icon name="plus-circle-solid" class="icon-18" /> Tambah Departemen
                    </a>
                </div>
            </div>

            <x-data-table
                id="departmentsTable"
                title="Daftar Departemen"
                subtitle="Unit organisasi beserta kepala dan anggotanya"
                :badge="$units->count()"
                :global-filters="[
                    ['key' => 'type', 'all' => 'Semua tipe', 'options' => $typeOptions],
                    ['key' => 'status', 'all' => 'Semua status', 'options' => [
                        ['value' => 'aktif', 'text' => 'Aktif'],
                        ['value' => 'non-aktif', 'text' => 'Non-Aktif'],
                    ]],
                ]"
                :columns="[
                    ['label' => 'No', 'key' => '__no', 'center' => true, 'width' => 50],
                    ['label' => 'Kode', 'key' => 'code', 'width' => 110, 'searchable' => true, 'placeholder' => 'Cari kode...'],
                    ['label' => 'Nama', 'key' => 'name', 'searchable' => true, 'placeholder' => 'Cari nama...'],
                    ['label' => 'Tipe', 'key' => 'type', 'width' => 130],
                    ['label' => 'Induk', 'key' => 'parent', 'width' => 150],
                    ['label' => 'Kepala', 'key' => 'head', 'searchable' => true, 'placeholder' => 'Cari kepala...'],
                    ['label' => 'Anggota', 'key' => 'members', 'center' => true, 'width' => 90],
                    ['label' => 'Status', 'key' => 'status', 'center' => true, 'width' => 110],
                    ['label' => 'Aksi', 'key' => '__actions', 'center' => true, 'width' => 120],
                ]"
                :rows="$unitRows"
                empty-text="Belum ada departemen."
            />
        </div>
    </div>

    <div style="display:none"><x-delete-modal action="#" label=""></x-delete-modal></div>
</x-app-layout>