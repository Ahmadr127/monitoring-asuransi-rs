<x-app-layout>
    <x-slot name="title">Manajemen User</x-slot>

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = 'Kelola hak akses dan profil pengguna sistem monitoring.'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush

    @php
        $editIcon = '<svg class="icon-14" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>';
        $trashIcon = '<svg class="icon-14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
        $shieldIcon = '<svg class="icon-14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';

        $userRows = $users->map(function ($user) use ($editIcon, $trashIcon, $shieldIcon) {
            $isAdmin = $user->isAdmin();

            $actions = '<button onclick="openEditModal('
                .(int) $user->id.', \''
                .e(addslashes($user->name)).'\', \''
                .e(addslashes($user->username)).'\', \''
                .e(addslashes($user->email)).'\', \''
                .(string) $user->role_id.'\', \''
                .e(addslashes($user->nip ?? '')).'\', \''
                .(string) ($user->organization_unit_id ?? '').'\', \''
                .e(addslashes($user->no_telpon ?? '')).'\')" class="action-btn edit-btn" title="Edit User">'.$editIcon.'</button>';

            if ($user->id !== auth()->id()) {
                $actions .= '<button type="button" data-dm-trigger data-dm-action="'.route('admin.users.destroy', $user).'" data-dm-label="'.e($user->name).'" class="action-btn delete-btn" title="Hapus">'.$trashIcon.'</button>';
            }

            return [
                'search' => strtolower(
                    $user->name.' '.$user->username.' '.$user->email.' '.($user->nip ?? '').' '.($user->no_telpon ?? '').' '.($user->organizationUnit?->name ?? '')
                ),
                'cells' => [
                    'name' => '<div class="flex-col"><div class="font-bold-700 color-slate-900">'.e($user->name).'</div><div class="text-xs-muted">'.e($user->email).($user->no_telpon ? ' · '.e($user->no_telpon) : '').'</div></div>',
                    'nip' => '<code class="username-tag">'.e($user->nip ?? '-').'</code>',
                    'username' => '<code class="username-tag">@<span>'.e($user->username).'</span></code>',
                    'dept' => $user->organizationUnit
                        ? '<span class="badge-premium badge-slate">'.e($user->organizationUnit->name).'</span>'
                        : '<span class="text-xs-muted">-</span>',
                    'role' => '<span class="badge-premium '.($isAdmin ? 'badge-purple' : 'badge-teal').'">'
                        .($isAdmin ? $shieldIcon : '').e($user->role?->name ?? 'No Role').'</span>',
                ],
                'cols' => [
                    'name' => strtolower($user->name.' '.$user->email),
                    'nip' => strtolower($user->nip ?? ''),
                    'username' => strtolower($user->username),
                    'dept' => strtolower($user->organizationUnit?->name ?? ''),
                    'role' => strtolower($user->role?->name ?? ''),
                ],
                'actions' => '<div class="action-buttons flex-col-center">'.$actions.'</div>',
            ];
        })->values();
    @endphp

    <div class="premium-container">

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <x-icon name="users" class="icon-24" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $users->count() }}</span>
                    <span class="label">Total User</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <x-icon name="shield-check" class="icon-24" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $users->filter(fn($u) => strtolower($u->role?->key) === 'admin')->count() }}</span>
                    <span class="label">Administrators</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon emerald">
                    <x-icon name="user" class="icon-24" />
                </div>
                <div class="stat-info">
                    <span class="value">{{ $users->filter(fn($u) => strtolower($u->role?->key) !== 'admin')->count() }}</span>
                    <span class="label">Staff Members</span>
                </div>
            </div>
        </div>

        <div class="premium-toolbar mb-3 d-flex justify-content-end">
            <form method="POST" action="{{ route('admin.users.sync-simutu') }}" onsubmit="return confirm('Sinkronkan data pegawai dari Simutu? User yang belum ada akan dibuat otomatis.');">
                @csrf
                <button type="submit" class="btn-premium-submit">
                    <x-icon name="refresh" class="icon-16" />
                    Sync dari Simutu
                </button>
            </form>
        </div>

        <div class="content-layout">
            <!-- Sidebar: Form -->
            <div class="premium-card">
                <div class="card-header">
                    <h2>Tambah User</h2>
                    <x-icon name="plus-circle" class="icon-18 icon-primary" />
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-input-minimal" placeholder="Nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label>NIP</label>
                            <input type="text" name="nip" class="form-input-minimal" placeholder="NIP pegawai">
                        </div>
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-input-minimal" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-input-minimal" placeholder="email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon / WA</label>
                            <input type="tel" name="no_telpon" class="form-input-minimal" placeholder="08xxxxxxxxxx" maxlength="30">
                        </div>
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-input-minimal" placeholder="********" required minlength="8" oninput="validatePasswordLength(this)">
                            <span class="password-hint" id="password-hint-create">Minimal 8 karakter</span>
                        </div>
                        <div class="form-group">
                            <label>Departemen</label>
                            <x-searchable-select
                                name="organization_unit_id"
                                :options="$departments->map(fn ($d) => ['value' => $d->id, 'text' => $d->name])"
                                :value="old('organization_unit_id', '')"
                                placeholder="Pilih Departemen"
                                nullable
                            />
                        </div>
                        <div class="form-group">
                            <label>Role <span class="text-danger">*</span></label>
                            <x-searchable-select
                                name="role_id"
                                :options="$roles->map(fn ($role) => ['value' => $role->id, 'text' => $role->name])"
                                :value="old('role_id', '')"
                                placeholder="Pilih Role"
                                required
                            />
                        </div>

                        <button type="submit" class="btn-premium-submit">
                            <x-icon name="plus-circle-solid" class="icon-18" />
                            Daftarkan User
                        </button>
                    </form>
                </div>
            </div>

            <!-- Main Panel: Table -->
            <x-data-table
                id="usersTable"
                title="Daftar User"
                subtitle="Profil dan hak akses seluruh pengguna"
                :badge="$users->count()"
                :columns="[
                    ['label' => 'No', 'key' => '__no', 'center' => true, 'width' => 50],
                    ['label' => 'Profil Pengguna', 'key' => 'name', 'searchable' => true, 'placeholder' => 'Cari nama/email...'],
                    ['label' => 'NIP', 'key' => 'nip', 'width' => 120, 'searchable' => true, 'placeholder' => 'Cari NIP...'],
                    ['label' => 'Username', 'key' => 'username', 'width' => 140, 'searchable' => true, 'placeholder' => 'Cari username...'],
                    ['label' => 'Departemen', 'key' => 'dept', 'width' => 160, 'searchable' => true, 'placeholder' => 'Cari departemen...'],
                    ['label' => 'Role', 'key' => 'role', 'center' => true, 'width' => 130, 'searchable' => true, 'placeholder' => 'Cari role...'],
                    ['label' => 'Aksi', 'key' => '__actions', 'center' => true, 'width' => 100],
                ]"
                :rows="$userRows"
                empty-text="Belum ada user yang terdaftar. Mulai dengan menambahkan user pertama."
            />
        </div>
    </div>

    <!-- Edit Modal -->
    <x-modal id="editModal" title="Edit User Profile">
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" class="form-input-minimal" required>
            </div>
            <div class="form-group">
                <label>NIP</label>
                <input type="text" id="edit_nip" name="nip" class="form-input-minimal">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="edit_username" name="username" class="form-input-minimal" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="edit_email" name="email" class="form-input-minimal" required>
            </div>
            <div class="form-group">
                <label>No. Telepon / WA</label>
                <input type="tel" id="edit_no_telpon" name="no_telpon" class="form-input-minimal" placeholder="08xxxxxxxxxx" maxlength="30">
            </div>
            <div class="form-group">
                <label>Departemen</label>
                <x-searchable-select
                    name="organization_unit_id"
                    id="edit_dept_select"
                    input-id="edit_dept"
                    :options="$departments->map(fn ($d) => ['value' => $d->id, 'text' => $d->name])"
                    placeholder="Pilih Departemen"
                    nullable
                />
            </div>
            <div class="form-group">
                <label>Role</label>
                <x-searchable-select
                    name="role_id"
                    id="edit_role_select"
                    input-id="edit_role"
                    :options="$roles->map(fn ($role) => ['value' => $role->id, 'text' => $role->name])"
                    placeholder="Pilih Role"
                    required
                />
            </div>
            <div class="form-group mb-8">
                <label>Password Baru <small class="text-xs-muted">(Kosongkan jika tidak ganti)</small></label>
                <input type="password" name="password" class="form-input-minimal" placeholder="********" minlength="8" oninput="validatePasswordLength(this)">
                <span class="password-hint" id="password-hint-edit">Minimal 8 karakter</span>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" onclick="closeEditModal()" class="btn-secondary">
                Batal
            </button>
            <button type="submit" form="editForm" class="btn-premium-submit w-auto">
                Simpan Perubahan
            </button>
        </x-slot>
    </x-modal>

    <div style="display:none"><x-delete-modal action="#" label=""></x-delete-modal></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        window.validatePasswordLength = function(input) {
            const hint = input.nextElementSibling;
            const len = input.value.length;
            if (len === 0) {
                hint.className = 'password-hint';
                hint.textContent = 'Minimal 8 karakter';
            } else if (len < 8) {
                hint.className = 'password-hint invalid';
                hint.textContent = 'terlalu pendek';
            } else {
                hint.className = 'password-hint valid';
                hint.textContent = 'cukup';
            }
        };

        window.openEditModal = function(id, name, username, email, role, nip, dept, noTelpon) {
            $('#editForm').attr('action', '{{ route('admin.users.update', '__ID__') }}'.replace('__ID__', id));
            $('#edit_name').val(name);
            $('#edit_username').val(username);
            $('#edit_email').val(email);
            $('#edit_nip').val(nip || '');
            $('#edit_no_telpon').val(noTelpon || '');
            if (window.__searchableSelects && window.__searchableSelects['edit_role']) {
                window.__searchableSelects['edit_role'].select(role);
            }
            if (window.__searchableSelects && window.__searchableSelects['edit_dept']) {
                window.__searchableSelects['edit_dept'].select(dept || null);
            }
            const pwInput = document.querySelector('#editForm input[name="password"]');
            if (pwInput) {
                pwInput.value = '';
                pwInput.dispatchEvent(new Event('input'));
            }
            XModal.open('editModal');
        };

        window.closeEditModal = function() {
            XModal.close('editModal');
        };
    </script>
</x-app-layout>