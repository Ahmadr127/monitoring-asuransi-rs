<x-app-layout>
    <x-slot name="title">Pengaturan Kolom</x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { 
                descEl.textContent = 'Atur visibilitas kolom yang tampil di dashboard untuk setiap peran pengguna.'; 
                descEl.style.display = 'block'; 
            }
        })();
    </script>
    @endpush

    <div class="premium-container">

        <div id="alert" class="toast-notification"></div>

        <div class="role-cards-stack">
            @foreach($roles as $role)
                @if($role->name !== 'Administrator')
                    @php
                        $currentSettings = $settings->get($role->id)?->where('table_name', 'ruang_lingkup')->first();
                        $visibleColumns = $currentSettings ? $currentSettings->visible_columns : array_keys($availableColumns['ruang_lingkup'] ?? []);
                        $totalCols = count($availableColumns['ruang_lingkup'] ?? []);
                        $selectedCount = count($visibleColumns);
                    @endphp

                    <div class="role-card" id="panel-{{ $role->id }}">
                        <div class="role-card-header">
                            <div class="role-card-title">
                                <div class="role-initial">{{ substr($role->name, 0, 1) }}</div>
                                <div>
                                    <h3>{{ $role->name }}</h3>
                                    <span class="role-col-count" id="counter-{{ $role->id }}">{{ $selectedCount }}/{{ $totalCols }} kolom</span>
                                </div>
                            </div>
                            <div class="role-card-actions">
                                <button class="btn-ghost-sm" onclick="checkAll({{ $role->id }}, true)">
                                    Pilih Semua
                                </button>
                                <button class="btn-ghost-sm" onclick="checkAll({{ $role->id }}, false)">
                                    Bersihkan
                                </button>
                            </div>
                        </div>

                        <div class="role-card-body">
                            <div class="toggle-grid">
                                @foreach($availableColumns['ruang_lingkup'] ?? [] as $key => $label)
                                    <div class="toggle-item {{ in_array($key, $visibleColumns) ? 'active' : '' }}"
                                        onclick="toggleColumn(this, {{ $role->id }})">
                                        <div class="toggle-track">
                                            <div class="toggle-dot"></div>
                                        </div>
                                        <span class="toggle-text">{{ $label }}</span>
                                        <input type="checkbox" name="cols_{{ $role->id }}[]" value="{{ $key }}" {{ in_array($key, $visibleColumns) ? 'checked' : '' }} class="toggle-checkbox">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="role-card-footer">
                            <span class="footer-hint" id="hint-{{ $role->id }}">Kolom terpilih akan muncul di dashboard untuk peran ini.</span>
                            <div class="footer-actions">
                                <span class="save-status" id="status-{{ $role->id }}"></span>
                                <button class="btn-save" onclick="save({{ $role->id }}, 'ruang_lingkup', this)">
                                    <x-icon name="check" class="icon-16" />
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <script>
        const savedState = {};

        function toggleSaveButton(roleId) {
            const panel = document.getElementById('panel-' + roleId);
            const btn = panel.querySelector('.btn-save');
            const hint = document.getElementById('hint-' + roleId);
            const count = panel.querySelectorAll('.toggle-checkbox:checked').length;
            btn.disabled = count === 0;
            if (count === 0) {
                hint.textContent = 'Pilih minimal 1 kolom';
                hint.className = 'footer-hint validation-error';
            } else {
                hint.textContent = 'Kolom terpilih akan muncul di dashboard untuk peran ini.';
                hint.className = 'footer-hint';
            }
        }

        function toggleColumn(item, roleId) {
            const cb = item.querySelector('.toggle-checkbox');
            cb.checked = !cb.checked;
            item.classList.toggle('active', cb.checked);
            updateCount(roleId);
            markDirty(roleId);
            toggleSaveButton(roleId);
        }

        function updateCount(roleId) {
            const panel = document.getElementById('panel-' + roleId);
            if (!panel) return;
            const count = panel.querySelectorAll('.toggle-checkbox:checked').length;
            const total = panel.querySelectorAll('.toggle-checkbox').length;
            const el = document.getElementById('counter-' + roleId);
            if (el) el.textContent = count + '/' + total + ' kolom';
        }

        function getSnapshot(roleId) {
            const panel = document.getElementById('panel-' + roleId);
            return Array.from(panel.querySelectorAll('.toggle-checkbox:checked')).map(i => i.value).sort().join(',');
        }

        function markDirty(roleId) {
            const btn = document.querySelector('#panel-' + roleId + ' .btn-save');
            const status = document.getElementById('status-' + roleId);
            const snapshot = getSnapshot(roleId);
            const isDirty = snapshot !== (savedState[roleId] || '');
            btn.classList.toggle('dirty', isDirty);
            btn.classList.toggle('saved', false);
            if (isDirty) {
                status.textContent = 'Belum disimpan';
                status.className = 'save-status dirty';
            } else {
                status.textContent = '';
                status.className = 'save-status';
            }
        }

        function checkAll(roleId, state) {
            const panel = document.getElementById('panel-' + roleId);
            panel.querySelectorAll('.toggle-item').forEach(item => {
                const cb = item.querySelector('.toggle-checkbox');
                cb.checked = state;
                item.classList.toggle('active', state);
            });
            updateCount(roleId);
            markDirty(roleId);
            toggleSaveButton(roleId);
        }

        function save(roleId, table, btn) {
            const panel = document.getElementById('panel-' + roleId);
            const cols = Array.from(panel.querySelectorAll('.toggle-checkbox:checked')).map(i => i.value);

            if (cols.length === 0) {
                notify('Pilih minimal satu kolom', 'error');
                return;
            }

            const snapshot = cols.sort().join(',');
            if (snapshot === (savedState[roleId] || '')) {
                notify('Tidak ada perubahan yang perlu disimpan', 'success');
                return;
            }

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="flex-center-gap-2"><svg class="animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...</span>';

            fetch('/admin/table-settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ role_id: roleId, table_name: table, visible_columns: cols })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        savedState[roleId] = snapshot;
                        btn.classList.remove('dirty');
                        btn.classList.add('saved');
                        btn.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor" class="icon-saved-check"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg> Tersimpan';
                        const status = document.getElementById('status-' + roleId);
                        status.textContent = '';
                        status.className = 'save-status';
                        setTimeout(() => {
                            btn.classList.remove('saved');
                            btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="icon-16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Simpan';
                        }, 2500);
                        notify('Pengaturan berhasil disimpan', 'success');
                    } else {
                        notify(data.message || 'Gagal menyimpan pengaturan', 'error');
                    }
                })
                .catch(() => notify('Gagal menyambungkan ke server', 'error'))
                .finally(() => {
                    btn.disabled = false;
                });
        }

        function notify(msg, type) {
            const alert = document.getElementById('alert');
            alert.textContent = msg;
            alert.className = 'toast-notification ' + type + ' active';
            setTimeout(() => alert.classList.remove('active'), 3000);
        }

        // Init: capture initial state as saved and set button state
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-save').forEach(function(btn) {
                const panel = btn.closest('.role-card');
                const roleId = panel.id.replace('panel-', '');
                savedState[roleId] = getSnapshot(roleId);
                toggleSaveButton(roleId);
            });
        });
    </script>
</x-app-layout>
