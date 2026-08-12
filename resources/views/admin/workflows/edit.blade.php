<x-app-layout>
    <x-slot name="title">Kelola Workflow - {{ $workflow->name }}</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Atur langkah persetujuan berjenjang secara dinamis.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    @php
        $rolesJson = $roles->map(fn($r) => ['value' => $r->key, 'text' => $r->name])->values();
        $usersJson = $users->map(fn($u) => ['value' => (string) $u->id, 'text' => $u->name . ' (' . ($u->role?->name ?? '-') . ')'])->values();
        $deptsJson = $departments->map(fn($d) => ['value' => (string) $d->id, 'text' => $d->name])->values();
    @endphp

    <div class="premium-container">
        <div class="flex-center-gap-2 mb-4" style="align-items:center">
            <a href="{{ route('admin.workflows.index') }}" class="btn-secondary">
                <x-icon name="arrow-left" class="icon-16" /> Kembali
            </a>
            <h1 style="margin:0">{{ $workflow->name }}</h1>
            <span class="badge-premium {{ $workflow->is_active ? 'badge-teal' : 'badge-slate' }}">{{ $workflow->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
        </div>

        @if($errors->any())
            <div class="alert-premium error">
                <div class="font-bold-700 mb-1">Aksi Gagal</div>
                <ul class="pl-5 m-0">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert-premium success"><strong>{{ session('success') }}</strong></div>
        @endif
        @if(session('error'))
            <div class="alert-premium error"><strong>{{ session('error') }}</strong></div>
        @endif

        <div class="content-layout">
            <div class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Detail Workflow</h2>
                    <x-icon name="list" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.workflows.update', $workflow) }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="name" value="{{ $workflow->name }}" class="form-input-minimal" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-input-minimal" rows="2">{{ $workflow->description }}</textarea>
                        </div>
                        <div class="form-group mb-8">
                            <label class="flex-between-center">
                                <span>Aktif</span>
                                <input type="checkbox" name="is_active" value="1" {{ $workflow->is_active ? 'checked' : '' }}>
                            </label>
                        </div>
                        <button type="submit" class="btn-premium-submit w-100">Simpan Info Workflow</button>
                    </form>
                </div>
            </div>

            <!-- Langkah-langkah approval -->
            <div class="data-table-container mb-0">
                <div class="card-header flex-between-center">
                    <h2>Langkah Persetujuan (Urut)</h2>
                    <span class="text-xs-muted">Urutan = step_order, diproses dari 1 naik</span>
                </div>

                <div class="form-body">
                    @forelse($workflow->steps as $step)
                    <form method="POST" action="{{ route('admin.workflows.steps.update', $step) }}" class="step-card" style="border:1px solid #e2e8f0;border-radius:8px;padding:1rem;margin-bottom:1rem">
                        @csrf
                        <div class="flex-center-gap-3" style="gap:0.75rem;margin-bottom:0.75rem">
                            <div style="width:70px">
                                <label class="text-xs-muted">Urutan</label>
                                <input type="number" name="step_order" value="{{ $step->step_order }}" class="form-input-minimal" min="1">
                            </div>
                            <div class="flex-1">
                                <label class="text-xs-muted">Nama Langkah</label>
                                <input type="text" name="name" value="{{ $step->name }}" class="form-input-minimal" required>
                            </div>
                            <div class="flex-col-center gap-1">
                                <label class="text-xs-muted">Aktif</label>
                                <input type="checkbox" name="is_active" value="1" {{ $step->is_active ? 'checked' : '' }}>
                            </div>
                            <div class="flex-col-center gap-1">
                                <label class="text-xs-muted">Bisa Reject</label>
                                <input type="checkbox" name="can_reject" value="1" {{ $step->can_reject ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="flex-center-gap-3" style="gap:0.75rem;margin-bottom:0.75rem">
                            <div style="width:200px">
                                <label class="text-xs-muted">Penilai</label>
                                <select name="approver_type" class="form-input-minimal step-approver-type" data-target="step_value_{{ $step->id }}">
                                    @foreach($approverTypes as $at)
                                        <option value="{{ $at->value }}" {{ $step->approver_type === $at->value ? 'selected' : '' }}>{{ $at->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs-muted">Nilai Penilai</label>
                                <select name="approver_value" id="step_value_{{ $step->id }}" class="form-input-minimal">
                                    <option value="">-- pilih --</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs-muted">Kondisi Skip (JSON, opsional)</label>
                                <input type="text" name="skip_if" class="form-input-minimal" placeholder='{"field":"is_high_profile","op":"!=","value":true}'
                                       value="{{ $step->skip_if ? json_encode($step->skip_if) : '' }}">
                            </div>
                        </div>

                        <div class="flex-center-gap-2">
                            <button type="submit" class="btn-premium-submit w-auto">Simpan Langkah</button>
                            <button type="button" class="btn-secondary"
                                onclick="if(confirm('Hapus langkah ini?')){document.getElementById('delStep_{{ $step->id }}').submit()}">Hapus</button>
                        </div>
                    </form>
                    <form id="delStep_{{ $step->id }}" method="POST" action="{{ route('admin.workflows.steps.destroy', $step) }}" style="display:none">@csrf</form>
                    @empty
                    <p class="empty-state">Belum ada langkah. Tambahkan langkah pertama di bawah.</p>
                    @endforelse
                </div>

                <!-- Tambah langkah -->
                <div class="form-body" style="border-top:1px solid #f1f5f9">
                    <div class="card-header flex-between-center" style="padding:0 0 1rem 0">
                        <h2>Tambah Langkah</h2>
                        <x-icon name="plus-circle" class="icon-18 icon-primary" />
                    </div>
                    <form method="POST" action="{{ route('admin.workflows.steps.store', $workflow) }}">
                        @csrf
                        <div class="flex-center-gap-3" style="gap:0.75rem;margin-bottom:0.75rem">
                            <div class="flex-1">
                                <label class="text-xs-muted">Nama Langkah</label>
                                <input type="text" name="name" class="form-input-minimal" placeholder="mis. Persetujuan Supervisor" required>
                            </div>
                            <div style="width:200px">
                                <label class="text-xs-muted">Penilai</label>
                                <select name="approver_type" id="new_approver_type" class="form-input-minimal">
                                    @foreach($approverTypes as $at)
                                        <option value="{{ $at->value }}">{{ $at->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs-muted">Nilai Penilai</label>
                                <select name="approver_value" id="new_approver_value" class="form-input-minimal">
                                    <option value="">-- pilih --</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex-center-gap-3" style="gap:0.75rem;margin-bottom:0.75rem">
                            <div class="flex-1">
                                <label class="text-xs-muted">Kondisi Skip (JSON, opsional)</label>
                                <input type="text" name="skip_if" class="form-input-minimal" placeholder='{"field":"is_high_profile","op":"!=","value":true}'>
                            </div>
                            <div class="flex-col-center gap-1">
                                <label class="text-xs-muted">Bisa Reject</label>
                                <input type="checkbox" name="can_reject" value="1" checked>
                            </div>
                        </div>
                        <button type="submit" class="btn-premium-submit w-100">
                            <x-icon name="plus-circle-solid" class="icon-18" />
                            Tambahkan Langkah
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const POOL = {
            role: @json($rolesJson),
            user: @json($usersJson),
            department_head: @json($deptsJson),
            department: @json($deptsJson),
        };

        function populateSelect(select, type, selectedValue) {
            select.innerHTML = '<option value="">-- pilih --</option>';
            (POOL[type] || []).forEach(function (o) {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                if (selectedValue !== null && String(o.value) === String(selectedValue)) { opt.selected = true; }
                select.appendChild(opt);
            });
        }

        // Saat tombol ubah type pada form step existing: isi value sesuai type
        document.querySelectorAll('.step-approver-type').forEach(function (sel) {
            sel.addEventListener('change', function () {
                populateSelect(document.getElementById(this.dataset.target), this.value, null);
            });
        });

        // Set nilai awal untuk setiap step (dari approver_value server-side)
        const INITIAL = @json($workflow->steps->mapWithKeys(fn($s) => [$s->id => ['type' => $s->approver_type, 'value' => $s->approver_value]])->all());
        Object.keys(INITIAL).forEach(function (id) {
            const sel = document.querySelector('.step-approver-type[data-target="step_value_' + id + '"]');
            if (sel) {
                populateSelect(document.getElementById('step_value_' + id), INITIAL[id].type, INITIAL[id]?.value ?? '');
            }
        });

        document.getElementById('new_approver_type').addEventListener('change', function () {
            populateSelect(document.getElementById('new_approver_value'), this.value, null);
        });
        populateSelect(document.getElementById('new_approver_value'), document.getElementById('new_approver_type').value, null);
    </script>
</x-app-layout>