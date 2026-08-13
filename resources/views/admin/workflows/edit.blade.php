<x-app-layout>
    <x-slot name="title">Kelola Workflow - {{ $workflow->name }}</x-slot>
    @push('navbar-meta')
    <script>
        (function () {
            const d = document.getElementById('navbarPageDesc');
            if (d) { d.textContent = 'Atur langkah persetujuan berjenjang secara dinamis.'; d.style.display = 'block'; }
        })();
    </script>
    @endpush

    @php
        $approverTypeJson = collect($approverTypes)
            ->map(fn($at) => ['value' => $at->value, 'text' => $at->label()])
            ->values();
        $rolesJson = $roles->map(fn($r) => ['value' => $r->key, 'text' => $r->name])->values();
        $usersJson = $users->map(fn($u) => ['value' => (string) $u->id, 'text' => $u->name . ' (' . ($u->role?->name ?? '-') . ')'])->values();
        $deptsJson = $departments->map(fn($d) => ['value' => (string) $d->id, 'text' => $d->name])->values();
        $pool = [
            'role' => $rolesJson,
            'user' => $usersJson,
            'department_head' => $deptsJson,
            'department' => $deptsJson,
        ];
    @endphp

    <div class="wf-wrap">
        <div class="flex-center-gap-2 wf-page-head" style="align-items:center">
            <a href="{{ route('admin.workflows.index') }}" class="btn-secondary">
                <x-icon name="arrow-left" class="icon-16" /> Kembali
            </a>
            <h1 style="margin:0">{{ $workflow->name }}</h1>
            <span class="badge-premium {{ $workflow->is_active ? 'badge-teal' : 'badge-slate' }}">{{ $workflow->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
        </div>

        <div class="wf-grid">
            <aside class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Detail Workflow</h2>
                    <x-icon name="list" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.workflows.update', $workflow) }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ $workflow->name }}" class="form-input-minimal" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-input-minimal" rows="3">{{ $workflow->description }}</textarea>
                        </div>
                        <div class="form-group mb-8">
                            <label class="flex-between-center">
                                <span>Aktif</span>
                                <label class="toggle-switch" style="margin:0">
                                    <input type="checkbox" name="is_active" value="1" {{ $workflow->is_active ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                        <button type="submit" class="btn-premium-submit w-100">Simpan Info Workflow</button>
                    </form>
                </div>
            </aside>

            <!-- Langkah-langkah approval -->
            <section class="premium-card wf-steps-card">
                <div class="card-header flex-between-center">
                    <h2>Langkah Persetujuan</h2>
                    <span class="text-xs-muted">Urutan diproses dari 1 naik</span>
                </div>

                <div class="form-body">
                    @forelse($workflow->steps as $step)
                    <div class="wf-step" data-step-form="{{ $step->id }}">
                        <form method="POST" action="{{ route('admin.workflows.steps.update', $step) }}">
                            @csrf
                            <div class="wf-step-top">
                                <div class="wf-step-badge">{{ $step->step_order }}</div>
                                <div class="wf-step-name">
                                    <label class="wf-label">Nama Langkah <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ $step->name }}" class="form-input-minimal" required>
                                </div>
                                <div class="wf-step-order">
                                    <label class="wf-label">Urutan</label>
                                    <input type="number" name="step_order" value="{{ $step->step_order }}" class="form-input-minimal" min="1">
                                </div>
                                <div class="wf-step-toggles">
                                    <label class="toggle-item">
                                        <span>Aktif</span>
                                        <span class="toggle-switch">
                                            <input type="checkbox" name="is_active" value="1" {{ $step->is_active ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="wf-step-grid">
                                <div class="wf-step-col">
                                    <x-searchable-select
                                        name="approver_type"
                                        input-id="wf_type_{{ $step->id }}"
                                        label="Penilai"
                                        :options="$approverTypeJson"
                                        :value="$step->approver_type"
                                        placeholder="-- Pilih Tipe --"
                                        :onselect="'wfStepTypeChanged'"
                                        required
                                    />
                                </div>
                                <div class="wf-step-col">
                                    <x-searchable-select
                                        name="approver_value"
                                        input-id="wf_value_{{ $step->id }}"
                                        label="Nilai Penilai"
                                        :options="$pool[$step->approver_type] ?? []"
                                        :value="$step->approver_value"
                                        placeholder="-- Pilih --"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="wf-step-foot">
                                <button type="submit" class="btn-premium-submit w-auto">Simpan Langkah</button>
                                <button type="button" class="btn-secondary"
                                    onclick="if(confirm('Hapus langkah ini?')){document.getElementById('delStep_{{ $step->id }}').submit()}">Hapus</button>
                            </div>
                        </form>
                        <form id="delStep_{{ $step->id }}" method="POST" action="{{ route('admin.workflows.steps.destroy', $step) }}" style="display:none">@csrf</form>
                    </div>
                    @empty
                    <p class="empty-state">Belum ada langkah. Tambahkan langkah pertama di bawah.</p>
                    @endforelse

                    <!-- Tambah langkah -->
                    <div class="wf-step wf-step-new" data-step-form="new">
                        <div class="wf-step-top">
                            <div class="wf-step-badge">+</div>
                            <h3 class="wf-new-title">Tambah Langkah Baru</h3>
                        </div>
                        <form method="POST" action="{{ route('admin.workflows.steps.store', $workflow) }}">
                            @csrf
                            <div class="wf-step-grid">
                                <div class="wf-step-col wf-col-wide">
                                    <label class="wf-label">Nama Langkah <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-input-minimal" placeholder="mis. Persetujuan Supervisor" required>
                                </div>
                                <div class="wf-step-col">
                                    <x-searchable-select
                                        name="approver_type"
                                        input-id="wf_type_new"
                                        label="Penilai"
                                        :options="$approverTypeJson"
                                        value="role"
                                        placeholder="-- Pilih Tipe --"
                                        :onselect="'wfStepTypeChanged'"
                                        required
                                    />
                                </div>
                                <div class="wf-step-col">
                                    <x-searchable-select
                                        name="approver_value"
                                        input-id="wf_value_new"
                                        label="Nilai Penilai"
                                        :options="$pool['role'] ?? []"
                                        placeholder="-- Pilih --"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="wf-step-foot">
                                <button type="submit" class="btn-premium-submit w-auto">
                                    <x-icon name="plus-circle-solid" class="icon-16" />
                                    Tambahkan Langkah
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <style>
        .wf-wrap { max-width: 1100px; }
        .wf-page-head { margin-bottom: 1.25rem; }
        .wf-page-head h1 { font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #1e293b; }

        .wf-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (max-width: 1024px) {
            .wf-grid { grid-template-columns: 1fr; }
        }

        .wf-steps-card { min-width: 0; }

        .wf-step {
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 12px;
            background: #ffffff;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .wf-step:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        .wf-step-top { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
        .wf-step-badge {
            flex: none;
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--primary-50, #f0fdfc);
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px;
        }
        .wf-step-name { flex: 1; min-width: 0; }
        .wf-step-order { flex: none; width: 90px; }
        .wf-step-toggles { flex: none; display: flex; gap: 1rem; padding-top: 0.25rem; }
        .toggle-item {
            display: flex; align-items: center; gap: 0.4rem;
            font-size: 11px; font-weight: 600; color: #475569; cursor: pointer;
        }
        .toggle-item .toggle-switch { margin: 0; }

        .wf-label {
            display: block;
            font-size: 10px; font-weight: 600; color: #475569;
            margin-bottom: 0.5rem;
        }

        .wf-step-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 1rem; margin-bottom: 1rem; }
        .wf-col-wide { grid-column: 1 / -1; }
        @media (max-width: 720px) {
            .wf-step-grid { grid-template-columns: 1fr; }
            .wf-step-top { flex-wrap: wrap; }
            .wf-step-order { width: 100%; }
            .wf-step-toggles { width: 100%; justify-content: flex-end; }
        }

        .wf-step-foot {
            display: flex; gap: 0.5rem; justify-content: flex-end;
            border-top: 1px dashed var(--border-color, #e2e8f0);
            padding-top: 1rem;
        }

        .wf-step-new {
            border-style: dashed;
            background: #fcfdfe;
        }
        .wf-step-new:hover { border-color: var(--primary); }
        .wf-new-title {
            margin: 0; align-self: center;
            font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600; color: #1e293b;
        }
        .wf-step-new .wf-step-grid { align-items: end; }
    </style>

    <script>
        const POOL = @json($pool);

        function wfStepTypeChanged(value, label, hiddenInput) {
            const card = hiddenInput ? hiddenInput.closest('[data-step-form]') : null;
            if (!card) return;
            const stepKey = card.dataset.stepForm;
            const comp = window.__searchableSelects && window.__searchableSelects['wf_value_' + stepKey];
            if (!comp) return;
            comp.options = (POOL[value] || []).map(o => ({ value: String(o.value), label: o.text }));
            comp.selectedValue = null;
        }
    </script>
</x-app-layout>
