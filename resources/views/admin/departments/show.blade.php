<x-app-layout>
    <x-slot name="title">Detail Departemen - {{ $unit->name }}</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Kelola kepala dan anggota departemen.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    <div class="premium-container">
        <!-- Header -->
        <div class="premium-card" style="margin-bottom:1.25rem">
            <div class="form-body flex-between-center">
                <div>
                    <div class="flex-center-gap-2" style="gap:0.5rem;margin-bottom:0.5rem">
                        @if($unit->type && $unit->type->level == 1)
                            <span class="badge-premium badge-slate">{{ $unit->type->display_name }}</span>
                        @else
                            <span class="badge-premium badge-teal">{{ $unit->type?->display_name ?? 'Unit' }}</span>
                        @endif
                        <code class="key-badge">{{ $unit->code }}</code>
                        <span class="badge-premium {{ $unit->is_active ? 'badge-teal' : 'badge-slate' }}">{{ $unit->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                    </div>
                    <h1 style="margin:0 0 0.25rem">{{ $unit->name }}</h1>
                    @if($unit->parent)
                        <p class="text-xs-muted">Berada di bawah: {{ $unit->parent->name }}</p>
                    @endif
                    @if($unit->description)
                        <p class="text-xs-muted" style="color:#475569">{{ $unit->description }}</p>
                    @endif
                </div>
                <div class="flex-center-gap-2">
                    <a href="{{ route('admin.departments.index') }}" class="btn-secondary">
                        <x-icon name="arrow-left" class="icon-16" /> Kembali
                    </a>
                    <a href="{{ route('admin.departments.edit', $unit) }}" class="btn-premium-submit w-auto">
                        <x-icon name="edit" class="icon-18" /> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="content-layout" style="grid-template-columns:minmax(260px,360px) 1fr;align-items:start">
            <!-- Kolom Kiri: Kepala & Sub Unit -->
            <div class="space-y-4" style="display:flex;flex-direction:column;gap:1rem">
                <!-- Kepala Departemen -->
                <div class="premium-card">
                    <div class="card-header flex-between-center">
                        <h2>Kepala Departemen</h2>
                        <x-icon name="user" class="icon-18 icon-primary" />
                    </div>
                    <div class="form-body">
                        @if($unit->head)
                            <div class="flex-center-gap-2" style="gap:0.75rem;background:#f0fdfa;border:1px solid #99f6e4;border-radius:8px;padding:0.75rem;margin-bottom:1rem">
                                <div class="w-10 h-10 rounded-full bg-sp-primary flex items-center justify-center text-white">
                                    <x-icon name="user" class="icon-18" />
                                </div>
                                <div>
                                    <div class="font-bold-700">{{ $unit->head->name }}</div>
                                    <div class="text-xs-muted">{{ $unit->head->email ?? '' }}</div>
                                </div>
                            </div>
                        @else
                            <p class="empty-state">Belum ada kepala.</p>
                        @endif

                        <form method="POST" action="{{ route('admin.departments.update-head', $unit) }}">
                            @csrf @method('PATCH')
                            <x-searchable-select
                                name="head_id"
                                label="Pilih Kepala"
                                :options="$allUsers->map(fn ($u) => ['value' => $u->id, 'text' => $u->name . ' (' . ($u->role?->name ?? '-') . ')'])"
                                :value="$unit->head_id"
                                placeholder="Pilih kepala / tanpa kepala..."
                                nullable
                            />
                            <button type="submit" class="btn-premium-submit w-auto" style="padding:0.55rem 1rem">Simpan</button>
                        </form>
                    </div>
                </div>

                <!-- Sub Unit -->
                @if($unit->children->count() > 0)
                <div class="premium-card">
                    <div class="card-header flex-between-center">
                        <h2>Sub Unit ({{ $unit->children->count() }})</h2>
                        <x-icon name="grid" class="icon-18 icon-primary" />
                    </div>
                    <div class="form-body">
                        @foreach($unit->children as $child)
                            <a href="{{ route('admin.departments.show', $child) }}"
                               class="flex-center-gap-2" style="gap:0.75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:0.75rem;margin-bottom:0.5rem;text-decoration:none">
                                <div class="flex-1">
                                    <div class="font-bold-700" style="color:#334155">{{ $child->name }}</div>
                                    <div class="text-xs-muted">{{ $child->type?->display_name }} &middot; {{ $child->code }}</div>
                                </div>
                                <x-icon name="chevron-right" class="icon-14" style="color:#94a3b8" />
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Kolom Kanan: Anggota -->
            <div class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Anggota Departemen ({{ $unit->members->count() }})</h2>
                    <x-icon name="users-solid" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <!-- Tambah Anggota -->
                    <form method="POST" action="{{ route('admin.departments.add-member', $unit) }}" class="mb-3" style="margin-bottom:1rem">
                        @csrf
                        <x-searchable-select
                            name="user_id"
                            label="Pilih User untuk Ditambahkan"
                            :options="$availableUsers->map(fn ($u) => ['value' => $u->id, 'text' => $u->name . ' (' . ($u->email ?? '') . ')'])"
                            placeholder="Pilih user..."
                            required
                        />
                        <button type="submit" class="btn-premium-submit w-auto" style="padding:0.55rem 1rem">
                            <x-icon name="plus-circle-solid" class="icon-14" /> Tambah
                        </button>
                    </form>

                    <!-- Daftar Anggota -->
                    <div class="table-view">
                        <div class="table-wrapper">
                            <table class="page-table">
                                <thead>
                                    <tr>
                                        <th class="th-min-200">Nama</th>
                                        <th class="th-min-200">Email</th>
                                        <th class="th-min-140">Role</th>
                                        <th class="th-min-100">Status</th>
                                        <th class="text-center th-min-120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unit->members as $member)
                                    <tr>
                                        <td>
                                            <div class="font-bold-700 color-slate-900">{{ $member->name }}</div>
                                            @if((int) $unit->head_id === (int) $member->id)
                                                <span class="badge-premium badge-teal">Kepala</span>
                                            @endif
                                        </td>
                                        <td>{{ $member->email ?? '-' }}</td>
                                        <td><span class="badge-premium badge-slate">{{ $member->role?->name ?? '-' }}</span></td>
                                        <td>
                                            @if((int) $unit->head_id === (int) $member->id)
                                                <span style="color:#0f766e;font-weight:600">Kepala</span>
                                            @else
                                                <span class="text-xs-muted">Anggota</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if((int) $unit->head_id !== (int) $member->id)
                                                <x-delete-modal
                                                    action="{{ route('admin.departments.remove-member', [$unit, $member]) }}"
                                                    label="{{ $member->name }} dari anggota ini"
                                                    class="action-btn delete-btn"
                                                    title="Hapus anggota">
                                                    <x-icon name="trash" class="icon-14" />
                                                </x-delete-modal>
                                            @else
                                                <span class="text-xs-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="empty-state"><p>Belum ada anggota.</p></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>