<x-app-layout>
    <x-slot name="title">Workflow Persetujuan</x-slot>
    @push('navbar-meta')
    <script>
        (function () {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = 'Definisikan alur persetujuan berjenjang yang dinamis.'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush

    <div class="premium-container">
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
                <div class="card-header flex-between-center">
                    <h2>Buat Workflow Baru</h2>
                    <x-icon name="plus-circle" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.workflows.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Kode <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-input-minimal" placeholder="mis. kerjasama_baru" required style="text-transform:lowercase">
                        </div>
                        <div class="form-group">
                            <label>Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-input-minimal" placeholder="mis. Persetujuan Kontrak Baru" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-input-minimal" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="flex-between-center">
                                <span>Aktif</span>
                                <input type="checkbox" name="is_active" value="1" checked>
                            </label>
                        </div>
                        <button type="submit" class="btn-premium-submit w-100">
                            <x-icon name="plus-circle-solid" class="icon-18" />
                            Simpan Workflow
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
                                    <th class="th-min-200">Nama</th>
                                    <th class="th-min-120">Kode</th>
                                    <th class="text-center th-min-80">Jml Step</th>
                                    <th class="text-center th-min-80">Status</th>
                                    <th class="text-center th-min-140">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflows as $index => $wf)
                                <tr class="table-row" data-search="{{ strtolower($wf->name . ' ' . $wf->code) }}">
                                    <td class="number-cell text-center font-bold-700 text-muted-premium">{{ $index + 1 }}</td>
                                    <td class="font-bold-700 color-slate-900">{{ $wf->name }}</td>
                                    <td><span class="badge-premium badge-slate">{{ $wf->code }}</span></td>
                                    <td class="text-center"><span class="badge-premium badge-teal">{{ $wf->steps_count }}</span></td>
                                    <td class="text-center">
                                        <span class="badge-premium {{ $wf->is_active ? 'badge-teal' : 'badge-slate' }}">{{ $wf->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                    </td>
                                    <td class="actions-cell text-center">
                                        <div class="action-buttons flex-center-gap-2">
                                            <a href="{{ route('admin.workflows.edit', $wf) }}" class="action-btn edit-btn" title="Kelola Step">
                                                <x-icon name="edit" />
                                            </a>
                                            <x-delete-modal
                                                action="{{ route('admin.workflows.destroy', $wf) }}"
                                                label="workflow {{ $wf->name }}"
                                                class="action-btn delete-btn"
                                                title="Hapus">
                                                <x-icon name="trash" class="icon-14" />
                                            </x-delete-modal>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="empty-state"><p>Belum ada workflow. Buat workflow pertama.</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>