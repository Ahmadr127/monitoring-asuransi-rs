<x-app-layout>
    <x-slot name="title">Manajemen Departemen</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Kelola departemen, kepala, dan anggota organisasi.'; d.style.display = 'block'; } })();
    </script>
    @endpush

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

            <!-- Filter -->
            <div class="filter-search-section" style="padding:0.75rem 1.5rem;border-bottom:1px solid #f1f5f9">
                <form method="GET" class="flex-center-gap-2" style="gap:0.75rem;flex-wrap:wrap">
                    <div class="search-box-advanced sb-max-400" style="flex:1;min-width:220px">
                        <div class="search-icon"><x-icon name="search" /></div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode..." class="form-input-search">
                    </div>
                    <x-searchable-select
                        name="type_id"
                        :options="$types->map(fn ($t) => ['value' => $t->id, 'text' => $t->display_name])"
                        :value="request('type_id')"
                        placeholder="Semua Tipe..."
                        input-class="filter-select"
                        autosubmit
                        nullable
                    />
                    <x-searchable-select
                        name="is_active"
                        :options="[['value' => '1', 'text' => 'Aktif'], ['value' => '0', 'text' => 'Non-Aktif']]"
                        :value="request('is_active')"
                        placeholder="Semua Status..."
                        input-class="filter-select"
                        autosubmit
                        nullable
                    />
                    <button type="submit" class="btn-premium-submit w-auto">Filter</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn-secondary">Reset</a>
                </form>
            </div>

            <!-- Tabel Departemen -->
            <div class="table-view">
                <div class="table-wrapper">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th class="th-min-120">Kode</th>
                                <th class="th-min-220">Nama</th>
                                <th class="th-min-140">Tipe</th>
                                <th class="th-min-160">Induk</th>
                                <th class="th-min-180">Kepala</th>
                                <th class="text-center th-min-90">Anggota</th>
                                <th class="text-center th-min-90">Status</th>
                                <th class="text-center th-min-200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $unit)
                            <tr>
                                <td><code class="key-badge">{{ $unit->code }}</code></td>
                                <td class="font-bold-700 color-slate-900">{{ $unit->name }}</td>
                                <td><span class="badge-premium badge-slate">{{ $unit->type?->display_name ?? '-' }}</span></td>
                                <td>{{ $unit->parent?->name ?? '-' }}</td>
                                <td>{{ $unit->head?->name ?? '-' }}</td>
                                <td class="text-center"><span class="badge-premium badge-teal">{{ $unit->members_count }}</span></td>
                                <td class="text-center">
                                    <span class="badge-premium {{ $unit->is_active ? 'badge-teal' : 'badge-slate' }}">{{ $unit->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons" style="justify-content:center;gap:0.5rem">
                                        <a href="{{ route('admin.departments.show', $unit) }}" class="btn-premium-submit w-auto" style="padding:0.4rem 0.75rem;font-size:11px">
                                            <x-icon name="eye" class="icon-14" /> Detail
                                        </a>
                                        <a href="{{ route('admin.departments.edit', $unit) }}" class="btn-secondary" style="padding:0.4rem 0.75rem;font-size:11px">
                                            <x-icon name="edit" class="icon-14" /> Edit
                                        </a>
                                        <x-delete-modal
                                            action="{{ route('admin.departments.destroy', $unit) }}"
                                            label="departemen {{ $unit->name }}"
                                            class="action-btn delete-btn"
                                            title="Hapus">
                                            <x-icon name="trash" class="icon-14" />
                                        </x-delete-modal>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="empty-state"><p>Belum ada departemen.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>