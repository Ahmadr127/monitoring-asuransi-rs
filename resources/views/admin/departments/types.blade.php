<x-app-layout>
    <x-slot name="title">Tipe Departemen</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Kelola tipe level organisasi.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    <div class="premium-container">
        <div class="content-layout" style="grid-template-columns:320px 1fr;align-items:start">
            <!-- Form Tambah Tipe -->
            <div class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Tambah Tipe</h2>
                    <x-icon name="grid" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.departments.types.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Tampilan <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-input-minimal" placeholder="mis. Departemen" required>
                        </div>
                        <div class="form-group">
                            <label>Key <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-input-minimal" placeholder="mis. departemen" required style="text-transform:lowercase">
                        </div>
                        <div class="form-group mb-8">
                            <label>Level <span class="text-danger">*</span></label>
                            <input type="number" name="level" class="form-input-minimal" min="1" value="1" required>
                        </div>
                        <button type="submit" class="btn-premium-submit w-100">
                            <x-icon name="plus-circle-solid" class="icon-18" /> Simpan Tipe
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Tipe -->
            <div class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Daftar Tipe</h2>
                    <a href="{{ route('admin.departments.index') }}" class="btn-secondary">
                        <x-icon name="arrow-left" class="icon-16" /> Kembali
                    </a>
                </div>
                <div class="table-view">
                    <div class="table-wrapper">
                        <table class="page-table">
                            <thead>
                                <tr>
                                    <th class="th-min-160">Tampilan</th>
                                    <th class="th-min-120">Key</th>
                                    <th class="text-center th-min-80">Level</th>
                                    <th class="text-center th-min-100">Jml Unit</th>
                                    <th class="text-center th-min-140">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                <tr class="table-row" data-search="{{ strtolower($type->display_name . ' ' . $type->name) }}">
                                    <form method="POST" action="{{ route('admin.departments.types.update', $type) }}">
                                        @csrf @method('PUT')
                                        <td><input type="text" name="display_name" value="{{ $type->display_name }}" class="form-input-minimal"></td>
                                        <td><input type="text" name="name" value="{{ $type->name }}" class="form-input-minimal" style="text-transform:lowercase"></td>
                                        <td class="text-center"><input type="number" name="level" value="{{ $type->level }}" class="form-input-minimal w-70 text-center" min="1"></td>
                                        <td class="text-center"><span class="badge-premium badge-teal">{{ $type->organization_units_count }}</span></td>
                                        <td class="text-center">
                                            <div class="action-buttons" style="justify-content:center;gap:0.5rem">
                                                <button type="submit" class="btn-secondary" style="padding:0.4rem 0.75rem;font-size:11px">Simpan</button>
                                    </form>
                                    <x-delete-modal
                                        action="{{ route('admin.departments.types.destroy', $type) }}"
                                        label="tipe {{ $type->display_name }}"
                                        class="action-btn delete-btn"
                                        title="Hapus">
                                        <x-icon name="trash" class="icon-14" />
                                    </x-delete-modal>
                                            </div>
                                        </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="empty-state"><p>Belum ada tipe.</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>