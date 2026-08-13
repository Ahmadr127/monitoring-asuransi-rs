<x-app-layout>
    <x-slot name="title">{{ isset($ruangLingkup) ? 'Edit Kerjasama' : 'Tambah Kerjasama Baru' }}</x-slot>
    @php
        $isEdit = isset($ruangLingkup);
        $pageTitle = $isEdit ? 'Edit Kerjasama' : 'Tambah Kerjasama Baru';
    @endphp

    <div class="rl-wrap">
        <div class="rl-page-head">
            <a href="{{ route('admin.ruang-lingkup.index') }}"
                class="action-icon-btn"
                title="Kembali ke daftar">
                <x-icon name="arrow-left" class="icon-14" />
            </a>
            <h3>{{ $isEdit ? 'Edit Kerjasama Asuransi' : 'Tambah Kerjasama Asuransi Baru' }}</h3>
        </div>

        <form method="POST"
              action="{{ $isEdit ? route('admin.ruang-lingkup.update', $ruangLingkup) : route('admin.ruang-lingkup.store') }}"
              class="rl-card"
              id="ruang_lingkup_form">
            @csrf
            @if($isEdit) @method('PUT') @endif
            @if(!empty($from))
            <input type="hidden" name="from" value="{{ $from }}">
            @endif

            <div id="form_validation_summary" class="rl-validation-summary rl-hidden" role="alert"></div>

            <!-- Step Progress Indicator -->
            <div class="step-indicator-wrapper">
                <div class="step-indicator-line">
                    <div class="step-indicator-line-progress" id="step_line_progress"></div>
                </div>
                <div class="step-indicator-nodes">
                    <div class="step-node active" id="node_step_1">
                        <div class="step-node-circle">1</div>
                        <div class="step-node-label">Informasi Dasar</div>
                    </div>
                    <div class="step-node" id="node_step_2">
                        <div class="step-node-circle">2</div>
                        <div class="step-node-label">Badan Usaha &amp; Layanan</div>
                    </div>
                    <div class="step-node" id="node_step_3">
                        <div class="step-node-circle">3</div>
                        <div class="step-node-label">Review &amp; Konfirmasi</div>
                    </div>
                </div>
            </div>

            <!-- STEP 1: INFORMASI DASAR -->
            <div class="step-content" id="step_1_content">
                <div class="rl-section">
                    <div class="rl-section-header">
                        <div class="rl-section-icon"><x-icon name="information-circle" class="" /></div>
                        <h3>Informasi Dasar</h3>
                    </div>
                    <div class="rl-grid">
                        <div class="rl-field s2">
                            <label class="rl-label req">Nama Asuransi</label>
                            <div class="input-container">
                                <input type="text" name="nama_asuransi" id="form_nama_asuransi" required class="rl-input"
                                       value="{{ old('nama_asuransi', $isEdit ? $ruangLingkup->nama_asuransi : '') }}"
                                       placeholder="Contoh: PT. Medika Yakespen Utama">
                            </div>
                        </div>

                        <div class="rl-field s2">
                            <label class="rl-label req">Alias Versi Tera</label>
                            <input type="text" name="alias_versi_tera" id="alias_versi_tera" class="rl-input"
                                   value="{{ old('alias_versi_tera', $isEdit ? $ruangLingkup->alias_versi_tera : '') }}"
                                   placeholder="Contoh: AIA V2.0">
                        </div>

                        <div class="rl-field s1">
                            <label class="rl-label req">TPA / PAYOR</label>
                            <x-searchable-select
                                name="penjamin_category"
                                input-id="penjamin_category"
                                :options="[['value' => 'PAYOR', 'text' => 'PAYOR'], ['value' => 'TPA', 'text' => 'TPA'], ['value' => 'TPA & PAYOR', 'text' => 'TPA & PAYOR']]"
                                :value="old('penjamin_category', $isEdit ? $ruangLingkup->penjamin_category : '')"
                                placeholder="-- Pilih --"
                                required
                            />
                        </div>

                        <div class="rl-field s1">
                            <label class="rl-label">&nbsp;</label>
                            <div class="rl-toggle-wrap">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="is_high_profile" id="is_high_profile" value="1"
                                        {{ old('is_high_profile', $isEdit ? $ruangLingkup->is_high_profile : false) ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <span>High Profile</span>
                            </div>
                        </div>

                        <div class="rl-field s1">
                            <label class="rl-label req">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="form_tanggal_mulai" required class="rl-input"
                                   value="{{ old('tanggal_mulai', $isEdit ? $ruangLingkup->tanggal_mulai->format('Y-m-d') : '') }}">
                        </div>

                        <div class="rl-field s1">
                            <label class="rl-label req">Tanggal Berakhir</label>
                            <input type="date" name="tanggal_berakhir" id="form_tanggal_berakhir" required class="rl-input"
                                   value="{{ old('tanggal_berakhir', $isEdit ? $ruangLingkup->tanggal_berakhir->format('Y-m-d') : '') }}">
                        </div>

                        <div class="rl-field s1">
                            <label class="rl-label req">Status</label>
                            <div class="rl-toggle-wrap">
                                <label class="toggle-switch toggle-status">
                                    <input type="checkbox" id="status_toggle"
                                        {{ old('status', $isEdit ? $ruangLingkup->status : 'tidak_aktif') == 'aktif' ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <span class="toggle-status-label" id="status_label">Tidak Aktif</span>
                                <input type="hidden" name="status" id="status_hidden" value="{{ old('status', $isEdit ? $ruangLingkup->status : 'tidak_aktif') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: BADAN USAHA & LAYANAN -->
            <div class="step-content rl-hidden" id="step_2_content">
                <!-- BADAN USAHA & CAKUPAN -->
                <div class="rl-section">
                    <div class="rl-section-header">
                        <div class="rl-section-icon"><x-icon name="building" class="" /></div>
                        <h3>Badan Usaha &amp; Cakupan</h3>
                    </div>
                    <div id="bu_groups_container">
                        @php
                            $buGroups = old('bu_groups', $isEdit && $ruangLingkup->relationLoaded('badanUsahaPivot')
                                ? $ruangLingkup->badanUsahaPivot->map(fn($p) => [
                                    'pivot_id' => $p->id,
                                    'badan_usaha_id' => $p->badan_usaha_id,
                                    'badan_usaha_name' => $p->badanUsaha->nama_bu ?? '',
                                    'benefit' => $p->benefit ?? [''],
                                    'cakupan' => $p->cakupan ?? [''],
                                    'jenis_layanan_ids' => $p->jenis_layanan_ids ?? [],
                                    'obat_ids' => $p->obat_ids ?? [],
                                    'deskripsi' => $p->deskripsi ?? '',
                                ])->toArray()
                                : [['pivot_id' => '', 'badan_usaha_id' => '', 'badan_usaha_name' => '', 'benefit' => [''], 'cakupan' => [''], 'jenis_layanan_ids' => [], 'obat_ids' => [], 'deskripsi' => '']]);
                        @endphp
                        @foreach($buGroups as $gi => $group)
                            <div class="rl-card bu-group-card" data-group-index="{{ $gi }}">
                                <div class="rl-section-header rl-section-header-compact">
                                    <h4 class="rl-bu-heading">Badan Usaha #{{ $loop->iteration }}</h4>
                                    @if(!$loop->first)
                                        <button type="button" onclick="removeBuGroup(this)" class="rl-btn-rem" title="Hapus grup BU">
                                            <x-icon name="close-solid" class="" />
                                        </button>
                                    @endif
                                </div>
                                <input type="hidden" name="bu_groups[{{ $gi }}][pivot_id]" value="{{ $group['pivot_id'] }}">
                                <div class="rl-grid">
                                    <div class="rl-field s2">
                                        <label class="rl-label req">Badan Usaha</label>
                                        <div class="input-container">
                                            <input type="text" class="rl-input bu-autocomplete-input"
                                                   value="{{ $group['badan_usaha_name'] }}"
                                                   placeholder="Cari Badan Usaha..."
                                                   data-target-id="bu_groups[{{ $gi }}][badan_usaha_id]"
                                                   autocomplete="off">
                                            <input type="hidden" name="bu_groups[{{ $gi }}][badan_usaha_id]"
                                                   class="bu-autocomplete-hidden"
                                                   value="{{ $group['badan_usaha_id'] }}">
                                            <div class="rl-autocomplete bu-autocomplete-dropdown"></div>
                                        </div>
                                    </div>

                                    <div class="rl-field full">
                                        <div class="rl-grid rl-sub-grid">
                                            <div class="rl-field">
                                                <label class="rl-label">Benefit</label>
                                                <div class="rl-dyn benefit-container">
                                                    @foreach(($group['benefit'] ?: ['']) as $bi => $bval)
                                                        <div class="rl-dyn-row">
                                                            <input type="text" name="bu_groups[{{ $gi }}][benefit][]"
                                                                   value="{{ $bval }}" class="rl-input" placeholder="Contoh: Obat-obatan">
                                                            @if($bi === 0)
                                                                <button type="button" class="rl-btn-add" onclick="addBenefitInGroup(this)" title="Tambah benefit">
                                                                    <x-icon name="plus-circle-solid" class="" />
                                                                </button>
                                                            @else
                                                                <button type="button" onclick="this.parentElement.remove()" class="rl-btn-rem" title="Hapus benefit">
                                                                    <x-icon name="close-solid" class="" />
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="rl-field">
                                                <label class="rl-label">Cakupan</label>
                                                <div class="rl-dyn cakupan-container">
                                                    @foreach(($group['cakupan'] ?: ['']) as $ci => $cval)
                                                        <div class="rl-dyn-row">
                                                            <input type="text" name="bu_groups[{{ $gi }}][cakupan][]"
                                                                   value="{{ $cval }}" class="rl-input" placeholder="Contoh: Karyawan">
                                                            @if($ci === 0)
                                                                <button type="button" class="rl-btn-add" onclick="addCakupanInGroup(this)" title="Tambah cakupan">
                                                                    <x-icon name="plus-circle-solid" class="" />
                                                                </button>
                                                            @else
                                                                <button type="button" onclick="this.parentElement.remove()" class="rl-btn-rem" title="Hapus cakupan">
                                                                    <x-icon name="close-solid" class="" />
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>

                            <!-- Jenis Layanan per BU -->
                            <div class="rl-field full rl-field-mt-bordered">
                                <div class="rl-label-inline-flex">
                                    <label class="rl-label req">Jenis Layanan</label>
                                    <a href="{{ route('admin.master-jenis-layanan.index') }}" target="_blank" rel="noopener" data-return="form"
                                       class="rl-add-link">
                                        <x-icon name="plus-circle-solid-small" class="" />
                                        Tambah
                                    </a>
                                </div>
                                <div class="rl-chk-grid">
                                    @forelse($jenisLayanan as $layanan)
                                        <label class="rl-chk-item">
                                            <input type="checkbox" name="bu_groups[{{ $gi }}][jenis_layanan_ids][]" value="{{ $layanan->id }}"
                                                {{ in_array($layanan->id, ($group['jenis_layanan_ids'] ?? [])) ? 'checked' : '' }}>
                                            <span class="rl-chk-lbl" title="{{ $layanan->nama }}">{{ $layanan->kode }}</span>
                                        </label>
                                    @empty
                                        <div class="rl-empty empty-full-span">
                                            <p>Belum ada data jenis layanan.</p>
                                            <a href="{{ route('admin.master-jenis-layanan.index') }}" target="_blank" rel="noopener" class="btn-primary" data-return="form">
                                                <x-icon name="plus" class="icon-14" />
                                                Tambah Jenis Layanan
                                            </a>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Obat per BU -->
                            <div class="rl-field full rl-field-mt">
                                <div class="rl-label-inline-flex">
                                    <label class="rl-label">Daftar Obat yang Tercakup</label>
                                    <a href="{{ route('admin.master-obat.index') }}" target="_blank" rel="noopener" data-return="form"
                                       class="rl-add-link">
                                        <x-icon name="plus-circle-solid-small" class="" />
                                        Tambah
                                    </a>
                                </div>
                                <div class="obat-group-container" data-gi="{{ $gi }}">
                                    <div id="obat_ids_container_{{ $gi }}">
                                        @foreach(($group['obat_ids'] ?? []) as $oid)
                                            <input type="hidden" name="bu_groups[{{ $gi }}][obat_ids][]" value="{{ $oid }}">
                                        @endforeach
                                    </div>
                                    <div id="selected_obat_container_{{ $gi }}" class="rl-badge-container"></div>
                                    @if($masterObat->isNotEmpty())
                                    <div class="input-container">
                                        <input type="text" id="obat_search_input_{{ $gi }}" class="rl-input"
                                               placeholder="Ketik nama obat..." autocomplete="off">
                                        <div id="form_obat_autocomplete_{{ $gi }}" class="rl-autocomplete"></div>
                                    </div>
                                    @else
                                    <div class="rl-empty">
                                        <p>Belum ada data obat.</p>
                                        <a href="{{ route('admin.master-obat.index') }}" target="_blank" rel="noopener" class="btn-primary" data-return="form">
                                            <x-icon name="plus" class="icon-14" />
                                            Tambah Obat
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Deskripsi per BU -->
                            <div class="rl-field full rl-field-mt">
                                <label class="rl-label">Deskripsi / Catatan Tambahan</label>
                                <textarea name="bu_groups[{{ $gi }}][deskripsi]" rows="2" class="rl-textarea"
                                          placeholder="...">{{ $group['deskripsi'] ?? '' }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="rl-field full rl-field-mt">
                        <button type="button" onclick="addBuGroup()" class="btn-secondary rl-btn-compact">
                            <x-icon name="plus-circle-solid-small" class="" />
                            Tambah Badan Usaha
                        </button>
                    </div>
                </div>

            </div>

            <!-- STEP 3: REVIEW & KONFIRMASI -->
            <div class="step-content rl-hidden" id="step_3_content">
                <div class="rl-section">
                    <div class="rl-section-header">
                        <div class="rl-section-icon"><x-icon name="document-text" class="" /></div>
                        <h3>Review &amp; Konfirmasi Data</h3>
                    </div>
                    <div class="rl-review-container">
                        <div class="rl-review-card">
                            <h4 class="rl-review-heading">
                                I. Informasi Dasar
                            </h4>
                            <div class="rl-review-grid">
                                <div class="rl-review-item">
                                    <span class="rl-review-label">Nama Asuransi</span>
                                    <span class="rl-review-value" id="rev_nama_asuransi">-</span>
                                </div>
                                <div class="rl-review-item">
                                    <span class="rl-review-label">Alias Versi Tera</span>
                                    <span class="rl-review-value" id="rev_alias_versi_tera">-</span>
                                </div>
                                <div class="rl-review-item">
                                    <span class="rl-review-label">TPA / Payor</span>
                                    <span class="rl-review-value" id="rev_penjamin_category">-</span>
                                </div>
                                <div class="rl-review-item">
                                    <span class="rl-review-label">High Profile</span>
                                    <span class="rl-review-value" id="rev_is_high_profile">-</span>
                                </div>
                                <div class="rl-review-item">
                                    <span class="rl-review-label">Periode Kerjasama</span>
                                    <span class="rl-review-value" id="rev_periode">-</span>
                                </div>
                                <div class="rl-review-item" id="rev_status_item">
                                    <span class="rl-review-label">Status</span>
                                    <span class="rl-review-value" id="rev_status">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="rl-review-card">
                            <h4 class="rl-review-heading">
                                II. Badan Usaha &amp; Layanan
                            </h4>
                            <div id="rev_bu_groups"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="rl-actions rl-actions-space-between">
                <div>
                    <button type="button" id="btn_reset_draft" class="btn-secondary rl-btn-reset" onclick="resetForm()" title="Bersihkan form">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M3 9h18M4 9l-1 12h18l-1-12M9 9V3h6v6M6 9h12"/><path d="M10 13l-1 5M12 13v5M14 13l1 5"/></svg>
                    </button>
                </div>
                <div class="rl-btn-group">
                <a href="{{ route('admin.ruang-lingkup.index') }}" class="btn-secondary" id="btn_cancel">Batal</a>
                <button type="button" class="btn-secondary rl-btn-prev" id="btn_prev" onclick="prevStep()">
                    <x-icon name="chevron-left" class="icon-14 rl-btn-nav-svg mr-3" />
                    Sebelumnya
                </button>
                <button type="button" class="btn-primary" id="btn_next" onclick="nextStep()">
                    Selanjutnya
                    <x-icon name="chevron-right" class="icon-14 rl-btn-nav-svg ml-3" />
                </button>
                <button type="submit" name="submit" value="1" class="btn-primary rl-hidden" id="btn_submit_approval">
                    <x-icon name="send" class="icon-14" />
                    {{ $isEdit ? 'Simpan Perubahan & Ajukan Persetujuan' : 'Simpan & Ajukan Persetujuan' }}
                </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Leave confirmation modal -->
    <div class="dm-overlay" id="leaveModal">
        <div class="dm-box">
            <div class="dm-icon">
                <x-icon name="trash" class="icon-20" />
            </div>
            <h3>Konfirmasi</h3>
            <p>Data yang sudah diisi akan hilang. Yakin ingin meninggalkan halaman ini?</p>
            <div class="dm-actions">
                <button type="button" class="dm-btn-cancel" id="leaveBtnCancel">Batal</button>
                <button type="button" class="dm-btn-yes" id="leaveBtnConfirm">Ya, Tinggalkan</button>
            </div>
        </div>
    </div>

</x-app-layout>

<script>
    // Icon helpers (avoid raw SVG path strings in template literals)
    const ICON_PLUS = '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>';
    const ICON_CLOSE = '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>';

    // HTML escape helper (pengganti helper jQuery: $('<span>').text(x).html())
    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    let currentStep = 1;
    let buGroupIndex = {{ count(old('bu_groups', $isEdit && $ruangLingkup->relationLoaded('badanUsahaPivot') ? $ruangLingkup->badanUsahaPivot : [['']])) }};

    // --- localStorage Draft ---
    const DRAFT_KEY = 'ruang_lingkup_draft_{{ $isEdit ? "edit_" . $ruangLingkup->id : "create" }}';
    const DRAFT_EXPIRY_MS = 60 * 60 * 1000;
    const RETURN_INFO_KEY = 'rl_return_to';
    const FORM_RETURN_URL = '{{ $isEdit ? route("admin.ruang-lingkup.edit", $ruangLingkup->id) : route("admin.ruang-lingkup.create") }}';

    const badanUsahaData = @json($badanUsaha->map(fn($bu) => ['id' => $bu->id, 'nama' => $bu->nama_bu]));
    const jenisLayananData = @json($jenisLayanan->map(fn($jl) => ['id' => $jl->id, 'nama' => $jl->nama, 'kode' => $jl->kode]));
    const jenisLayananMap = @json($jenisLayanan->mapWithKeys(fn($jl) => [$jl->id => $jl->nama]));
    const masterObatData = @json($masterObat->map(fn($o) => ['id' => $o->id, 'nama' => $o->nama]));
    const masterObatMap = @json($masterObat->mapWithKeys(fn($o) => [$o->id => $o->nama]));

    function renderLayananCheckboxes(index, selectedIds) {
        selectedIds = selectedIds || [];
        return jenisLayananData.map(function(jl) {
            var checked = selectedIds.indexOf(String(jl.id)) !== -1 ? ' checked' : '';
            return '<label class="rl-chk-item">' +
                '<input type="checkbox" name="bu_groups[' + index + '][jenis_layanan_ids][]" value="' + jl.id + '"' + checked + '>' +
                '<span class="rl-chk-lbl" title="' + jl.nama + '">' + jl.kode + '</span>' +
                '</label>';
        }).join('');
    }

    function getFormData() {
        const buGroupsData = [];
        document.querySelectorAll('#bu_groups_container .bu-group-card').forEach(function(card, gi) {
            const hidden = card.querySelector('.bu-autocomplete-hidden');
            const input = card.querySelector('.bu-autocomplete-input');

            const benefits = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][benefit][]"]').forEach(function(el) { benefits.push(el.value); });

            const cakupans = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][cakupan][]"]').forEach(function(el) { cakupans.push(el.value); });

            const jenislayanan = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][jenis_layanan_ids][]"]:checked').forEach(function(el) { jenislayanan.push(el.value); });

            const obatIds = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][obat_ids][]"]').forEach(function(el) { obatIds.push(el.value); });

            const deskripsiEl = card.querySelector('textarea[name="bu_groups[' + gi + '][deskripsi]"]');

            const pivotIdInput = card.querySelector('input[name="bu_groups[' + gi + '][pivot_id]"]');

            buGroupsData.push({
                pivot_id: pivotIdInput ? pivotIdInput.value : '',
                badan_usaha_id: hidden ? hidden.value : '',
                badan_usaha_name: input ? input.value : '',
                benefit: benefits,
                cakupan: cakupans,
                jenis_layanan_ids: jenislayanan,
                obat_ids: obatIds,
                deskripsi: deskripsiEl ? deskripsiEl.value : '',
            });
        });

        return {
            savedAt: Date.now(),
            step: currentStep,
            nama_asuransi: document.getElementById('form_nama_asuransi')?.value || '',
            alias_versi_tera: document.getElementById('alias_versi_tera')?.value || '',
            penjamin_category: document.getElementById('penjamin_category')?.value || '',
            is_high_profile: document.getElementById('is_high_profile')?.checked || false,
            tanggal_mulai: document.getElementById('form_tanggal_mulai')?.value || '',
            tanggal_berakhir: document.getElementById('form_tanggal_berakhir')?.value || '',
            status: document.getElementById('status_hidden')?.value || 'tidak_aktif',
            bu_groups: buGroupsData,
        };
    }

    function saveFormDraft() {
        try { localStorage.setItem(DRAFT_KEY, JSON.stringify(getFormData())); } catch (e) {}
    }

    function getDraft() {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return null;
            const data = JSON.parse(raw);
            if (data.savedAt && (Date.now() - data.savedAt > DRAFT_EXPIRY_MS)) {
                localStorage.removeItem(DRAFT_KEY);
                return null;
            }
            return data;
        } catch (e) { return null; }
    }

    function clearFormDraft() {
        try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
        showResetButton();
    }

    function showResetButton() {
        const btn = document.getElementById('btn_reset_draft');
        if (btn) btn.style.display = 'inline-flex';
    }

    function resetForm() {
        clearFormDraft();
        window.removeEventListener('beforeunload', saveFormDraft);
        document.getElementById('ruang_lingkup_form').reset();
        if (window.__searchableSelects && window.__searchableSelects['penjamin_category']) {
            window.__searchableSelects['penjamin_category'].select(null);
        }
        document.getElementById('is_high_profile').checked = false;
        const stToggle = document.getElementById('status_toggle');
        const stLabel = document.getElementById('status_label');
        const stHidden = document.getElementById('status_hidden');
        if (stToggle && stLabel && stHidden) {
            stLabel.textContent = stToggle.checked ? 'Aktif' : 'Tidak Aktif';
            stHidden.value = stToggle.checked ? 'aktif' : 'tidak_aktif';
        }
        // Reset BU groups to just one empty
        document.getElementById('bu_groups_container').innerHTML = '';
        buGroupIndex = 0;
        addBuGroup();
        buGroupIndex = 1;
        currentStep = 1;
        showStep(1);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function saveReturnInfo() {
        try {
            localStorage.setItem(RETURN_INFO_KEY, JSON.stringify({
                label: 'Kembali ke Form Kerjasama',
                url: FORM_RETURN_URL
            }));
        } catch (e) {}
    }

    function clearReturnInfo() {
        try {
            const info = JSON.parse(localStorage.getItem(RETURN_INFO_KEY) || 'null');
            if (info && info.url === FORM_RETURN_URL) {
                localStorage.removeItem(RETURN_INFO_KEY);
            }
        } catch (e) {}
    }

    // --- BU Group management ---
    function addBuGroup(group) {
        const container = document.getElementById('bu_groups_container');
        const index = buGroupIndex++;
        const div = document.createElement('div');
        div.className = 'rl-card bu-group-card';
        div.setAttribute('data-group-index', index);

        const benefitHtml = group && group.benefit && group.benefit.length
            ? group.benefit.map(function(val, bi) {
                return '<div class="rl-dyn-row">' +
                    '<input type="text" name="bu_groups[' + index + '][benefit][]" value="' + escapeHtml(val) + '" class="rl-input" placeholder="Contoh: Obat-obatan">' +
                    (bi === 0
                        ? '<button type="button" class="rl-btn-add" onclick="addBenefitInGroup(this)" title="Tambah benefit">' + ICON_PLUS + '</button>'
                        : '<button type="button" onclick="this.parentElement.remove()" class="rl-btn-rem" title="Hapus benefit">' + ICON_CLOSE + '</button>') +
                    '</div>';
            }).join('')
            : '<div class="rl-dyn-row"><input type="text" name="bu_groups[' + index + '][benefit][]" class="rl-input" placeholder="Contoh: Obat-obatan"><button type="button" class="rl-btn-add" onclick="addBenefitInGroup(this)" title="Tambah benefit">' + ICON_PLUS + '</button></div>';

        const cakupanHtml = group && group.cakupan && group.cakupan.length
            ? group.cakupan.map(function(val, ci) {
                return '<div class="rl-dyn-row">' +
                    '<input type="text" name="bu_groups[' + index + '][cakupan][]" value="' + escapeHtml(val) + '" class="rl-input" placeholder="Contoh: Karyawan">' +
                    (ci === 0
                        ? '<button type="button" class="rl-btn-add" onclick="addCakupanInGroup(this)" title="Tambah cakupan">' + ICON_PLUS + '</button>'
                        : '<button type="button" onclick="this.parentElement.remove()" class="rl-btn-rem" title="Hapus cakupan">' + ICON_CLOSE + '</button>') +
                    '</div>';
            }).join('')
            : '<div class="rl-dyn-row"><input type="text" name="bu_groups[' + index + '][cakupan][]" class="rl-input" placeholder="Contoh: Karyawan"><button type="button" class="rl-btn-add" onclick="addCakupanInGroup(this)" title="Tambah cakupan">' + ICON_PLUS + '</button></div>';

        const buName = group ? (group.badan_usaha_name || '') : '';
        const buId = group ? (group.badan_usaha_id || '') : '';
        const pivotId = group ? (group.pivot_id || '') : '';
        const groupJenisLayanan = group ? (group.jenis_layanan_ids || []) : [];
        const groupObatIds = group ? (group.obat_ids || []) : [];
        const groupDeskripsi = group ? (group.deskripsi || '') : '';

        div.innerHTML =
            '<div class="rl-section-header rl-section-header-compact">' +
                '<h4 class="rl-bu-heading">Badan Usaha #' + (index + 1) + '</h4>' +
                '<button type="button" onclick="removeBuGroup(this)" class="rl-btn-rem" title="Hapus grup BU">' +
                    ICON_CLOSE +
                '</button>' +
            '</div>' +
            '<input type="hidden" name="bu_groups[' + index + '][pivot_id]" value="' + pivotId + '">' +
            '<div class="rl-grid">' +
                '<div class="rl-field s2">' +
                    '<label class="rl-label req">Badan Usaha</label>' +
                    '<div class="input-container">' +
                        '<input type="text" class="rl-input bu-autocomplete-input" value="' + escapeHtml(buName) + '" placeholder="Cari Badan Usaha..." data-target-id="bu_groups[' + index + '][badan_usaha_id]" autocomplete="off">' +
                        '<input type="hidden" name="bu_groups[' + index + '][badan_usaha_id]" class="bu-autocomplete-hidden" value="' + buId + '">' +
                        '<div class="rl-autocomplete bu-autocomplete-dropdown"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="rl-field full">' +
                    '<div class="rl-grid rl-sub-grid">' +
                        '<div class="rl-field">' +
                            '<label class="rl-label">Benefit</label>' +
                            '<div class="rl-dyn benefit-container">' + benefitHtml + '</div>' +
                        '</div>' +
                        '<div class="rl-field">' +
                            '<label class="rl-label">Cakupan</label>' +
                            '<div class="rl-dyn cakupan-container">' + cakupanHtml + '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="rl-field full rl-field-mt-bordered">' +
                '<div class="rl-label-inline-flex">' +
                    '<label class="rl-label req">Jenis Layanan</label>' +
                    '<a href="{{ route('admin.master-jenis-layanan.index') }}" target="_blank" rel="noopener" class="rl-add-link">' +
                        ICON_PLUS +
                        ' Tambah' +
                    '</a>' +
                '</div>' +
                '<div class="rl-chk-grid">' +
                    renderLayananCheckboxes(index, groupJenisLayanan) +
                '</div>' +
            '</div>' +
            '<div class="rl-field full rl-field-mt">' +
                '<label class="rl-label">Daftar Obat yang Tercakup</label>' +
                '<div class="obat-group-container" data-gi="' + index + '">' +
                    '<div id="obat_ids_container_' + index + '">' +
                        (groupObatIds.length ? groupObatIds.map(function(oid) {
                            return '<input type="hidden" name="bu_groups[' + index + '][obat_ids][]" value="' + oid + '">';
                        }).join('') : '') +
                    '</div>' +
                    '<div id="selected_obat_container_' + index + '" class="rl-badge-container"></div>' +
                    '<div class="input-container">' +
                        '<input type="text" id="obat_search_input_' + index + '" class="rl-input" placeholder="Ketik nama obat..." autocomplete="off">' +
                        '<div id="form_obat_autocomplete_' + index + '" class="rl-autocomplete"></div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="rl-field full rl-field-mt">' +
                '<label class="rl-label">Deskripsi / Catatan Tambahan</label>' +
                '<textarea name="bu_groups[' + index + '][deskripsi]" rows="2" class="rl-textarea" placeholder="...">' + escapeHtml(groupDeskripsi) + '</textarea>' +
            '</div>';

        container.appendChild(div);
        initBuAutocomplete(div.querySelector('.bu-autocomplete-input'));
        initObatAutocomplete(div.querySelector('.obat-group-container .rl-input[id^="obat_search_input_"]'));
        saveFormDraft();
    }

    function removeBuGroup(btn) {
        const card = btn.closest('.bu-group-card');
        // Don't remove if it's the last one
        const cards = document.querySelectorAll('#bu_groups_container .bu-group-card');
        if (cards.length <= 1) return;
        card.remove();
        // Renumber headers, name attributes, and IDs
        document.querySelectorAll('#bu_groups_container .bu-group-card').forEach(function(c, i) {
            const h4 = c.querySelector('.rl-section-header h4');
            if (h4) h4.textContent = 'Badan Usaha #' + (i + 1);
            // Rename all bu_groups[name] attributes to match new index
            c.querySelectorAll('[name^="bu_groups["]').forEach(function(el) {
                el.name = el.name.replace(/^bu_groups\[\d+\]/, 'bu_groups[' + i + ']');
            });
            // Update data-target-id on autocomplete input
            const auInput = c.querySelector('.bu-autocomplete-input');
            if (auInput) {
                auInput.dataset.targetId = 'bu_groups[' + i + '][badan_usaha_id]';
            }
            // Update obat group container data-gi and container IDs
            const obatContainer = c.querySelector('.obat-group-container');
            if (obatContainer) {
                const oldGi = obatContainer.dataset.gi;
                obatContainer.dataset.gi = i;
                // Update IDs of child elements
                const hiddenContainer = obatContainer.querySelector('[id^="obat_ids_container_"]');
                if (hiddenContainer) hiddenContainer.id = 'obat_ids_container_' + i;
                const badgeContainer = obatContainer.querySelector('[id^="selected_obat_container_"]');
                if (badgeContainer) badgeContainer.id = 'selected_obat_container_' + i;
                const searchInput = obatContainer.querySelector('[id^="obat_search_input_"]');
                if (searchInput) searchInput.id = 'obat_search_input_' + i;
                const dropdown = obatContainer.querySelector('[id^="form_obat_autocomplete_"]');
                if (dropdown) dropdown.id = 'form_obat_autocomplete_' + i;
            }
            // Update layanan checkbox names (already handled by the general rename above)
        });
        buGroupIndex = document.querySelectorAll('#bu_groups_container .bu-group-card').length;
        saveFormDraft();
    }

    function addBenefitInGroup(btn) {
        const container = btn.closest('.benefit-container');
        const nameBase = container.querySelector('input').name.replace(/\[benefit\]\[\]$/, '');
        const div = document.createElement('div');
        div.className = 'rl-dyn-row';
        div.innerHTML = '<input type="text" name="' + nameBase + '[benefit][]" class="rl-input" placeholder="Contoh: Obat-obatan">' +
            '<button type="button" onclick="this.parentElement.remove(); saveFormDraft();" class="rl-btn-rem" title="Hapus benefit">' +
                ICON_CLOSE +
            '</button>';
        container.appendChild(div);
        saveFormDraft();
    }

    function addCakupanInGroup(btn) {
        const container = btn.closest('.cakupan-container');
        const nameBase = container.querySelector('input').name.replace(/\[cakupan\]\[\]$/, '');
        const div = document.createElement('div');
        div.className = 'rl-dyn-row';
        div.innerHTML = '<input type="text" name="' + nameBase + '[cakupan][]" class="rl-input" placeholder="Contoh: Karyawan">' +
            '<button type="button" onclick="this.parentElement.remove(); saveFormDraft();" class="rl-btn-rem" title="Hapus cakupan">' +
                ICON_CLOSE +
            '</button>';
        container.appendChild(div);
        saveFormDraft();
    }

    // --- BU Autocomplete (shared by all groups) ---
    function initBuAutocomplete(inputEl) {
        if (!inputEl || inputEl.dataset.rlInit) return;
        inputEl.dataset.rlInit = '1';
        const container = inputEl.closest('.input-container');
        const hidden = container.querySelector('.bu-autocomplete-hidden');
        const dropdown = container.querySelector('.bu-autocomplete-dropdown');
        let selecting = false;

        function renderDropdown(query) {
            const q = query.toLowerCase();
            const matches = q
                ? badanUsahaData.filter(function(bu) { return bu.nama.toLowerCase().includes(q); }).slice(0, 10)
                : badanUsahaData.slice(0, 10);

            var tambahItem = '<div class="rl-autocomplete-item" data-index="tambah" data-action="tambah" data-nama="">' +
                '<span class="rl-autocomplete-action">' +
                ICON_PLUS +
                'Tambah Badan Usaha</span></div>';

            dropdown.innerHTML = matches.map(function(bu, i) {
                return '<div class="rl-autocomplete-item" data-index="' + i + '" data-id="' + bu.id + '" data-nama="' + bu.nama.replace(/"/g, '&quot;') + '">' + bu.nama + '</div>';
            }).join('') + tambahItem;
            dropdown.style.display = 'block';
            dropdown._index = -1;
        }

        inputEl.addEventListener('focus', function() {
            if (selecting) return;
            renderDropdown(this.value);
        });

        inputEl.addEventListener('input', function() {
            if (selecting) { selecting = false; return; }
            if (!this.value) hidden.value = '';
            renderDropdown(this.value);
        });

        inputEl.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.rl-autocomplete-item');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (dropdown._index >= 0) items[dropdown._index].classList.remove('active');
                dropdown._index = (dropdown._index + 1) % items.length;
                items[dropdown._index].classList.add('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (dropdown._index >= 0) items[dropdown._index].classList.remove('active');
                dropdown._index = dropdown._index <= 0 ? items.length - 1 : dropdown._index - 1;
                items[dropdown._index].classList.add('active');
            } else if (e.key === 'Enter') {
                const active = dropdown.querySelector('.rl-autocomplete-item.active');
                if (active) {
                    e.preventDefault();
                    selectItem(active);
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        dropdown.addEventListener('click', function(e) {
            const item = e.target.closest('.rl-autocomplete-item');
            if (item) selectItem(item);
        });

        document.addEventListener('click', function(e) {
            if (!inputEl.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        function selectItem(item) {
            if (item.dataset.action === 'tambah') {
                saveReturnInfo();
                window.open('{{ route('admin.master-badan-usaha.index') }}', '_blank');
                return;
            }
            selecting = true;
            inputEl.value = item.dataset.nama;
            hidden.value = item.dataset.id;
            dropdown.style.display = 'none';
        }
    }

    // Initialize all BU autocompletes on load
    function initAllBuAutocompletes() {
        document.querySelectorAll('.bu-autocomplete-input').forEach(function(el) {
            initBuAutocomplete(el);
        });
    }

    // --- Obat Autocomplete (per-group) ---
    function initObatAutocomplete(inputEl) {
        if (!inputEl || inputEl.dataset.rlInit) return;
        inputEl.dataset.rlInit = '1';
        const container = inputEl.closest('.input-container');
        const groupContainer = inputEl.closest('.obat-group-container');
        if (!groupContainer) return;
        const gi = groupContainer.dataset.gi;
        const dropdown = container.querySelector('.rl-autocomplete');
        const hiddenContainer = document.getElementById('obat_ids_container_' + gi);
        const badgeContainer = document.getElementById('selected_obat_container_' + gi);
        let selecting = false;

        function renderObatDropdown(query) {
            const q = query.toLowerCase();
            const currentIds = new Set();
            if (hiddenContainer) {
                hiddenContainer.querySelectorAll('input').forEach(function(el) { currentIds.add(el.value); });
            }
            const matches = q
                ? masterObatData.filter(function(o) { return o.nama.toLowerCase().includes(q) && !currentIds.has(String(o.id)); }).slice(0, 10)
                : masterObatData.filter(function(o) { return !currentIds.has(String(o.id)); }).slice(0, 10);

            var tambahItem = '<div class="rl-autocomplete-item" data-index="tambah" data-action="tambah" data-nama="">' +
                '<span class="rl-autocomplete-action">' +
                ICON_PLUS +
                'Tambah Obat</span></div>';

            dropdown.innerHTML = matches.map(function(o, i) {
                return '<div class="rl-autocomplete-item" data-index="' + i + '" data-id="' + o.id + '" data-nama="' + o.nama.replace(/"/g, '&quot;') + '">' + o.nama + '</div>';
            }).join('') + tambahItem;
            dropdown.style.display = 'block';
            dropdown._index = -1;
        }

        function addObatBadge(id, nama) {
            const badge = document.createElement('span');
            badge.className = 'rl-obat-badge';
            badge.textContent = nama;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'rl-obat-remove';
            removeBtn.innerHTML = '×';
            removeBtn.onclick = function() {
                badge.remove();
                if (hiddenContainer) {
                    hiddenContainer.querySelectorAll('input[value="' + id + '"]').forEach(function(el) { el.remove(); });
                }
                saveFormDraft();
            };
            badge.appendChild(removeBtn);
            badgeContainer.appendChild(badge);
        }

        inputEl.addEventListener('focus', function() {
            if (selecting) return;
            renderObatDropdown(this.value);
        });

        inputEl.addEventListener('input', function() {
            if (selecting) { selecting = false; return; }
            renderObatDropdown(this.value);
        });

        inputEl.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.rl-autocomplete-item');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (dropdown._index >= 0) items[dropdown._index].classList.remove('active');
                dropdown._index = (dropdown._index + 1) % items.length;
                items[dropdown._index].classList.add('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (dropdown._index >= 0) items[dropdown._index].classList.remove('active');
                dropdown._index = dropdown._index <= 0 ? items.length - 1 : dropdown._index - 1;
                items[dropdown._index].classList.add('active');
            } else if (e.key === 'Enter') {
                const active = dropdown.querySelector('.rl-autocomplete-item.active');
                if (active) {
                    e.preventDefault();
                    selectObatItem(active);
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        dropdown.addEventListener('click', function(e) {
            const item = e.target.closest('.rl-autocomplete-item');
            if (item) selectObatItem(item);
        });

        document.addEventListener('click', function(e) {
            if (!inputEl.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        function selectObatItem(item) {
            if (item.dataset.action === 'tambah') {
                window.open('{{ route('admin.master-obat.index') }}', '_blank');
                return;
            }
            selecting = true;
            const id = item.dataset.id;
            const nama = item.dataset.nama;
            const currentGi = groupContainer.dataset.gi;
            inputEl.value = '';
            dropdown.style.display = 'none';

            // Add hidden input
            if (hiddenContainer) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'bu_groups[' + currentGi + '][obat_ids][]';
                inp.value = id;
                hiddenContainer.appendChild(inp);
            }
            addObatBadge(id, nama);
            saveFormDraft();
        }

        // Restore badges from existing hidden inputs
        if (hiddenContainer) {
            hiddenContainer.querySelectorAll('input').forEach(function(el) {
                const name = masterObatMap[el.value];
                if (name) addObatBadge(el.value, name);
            });
        }
    }

    function initAllObatAutocompletes() {
        document.querySelectorAll('.obat-group-container .rl-input[id^="obat_search_input_"]').forEach(function(el) {
            initObatAutocomplete(el);
        });
    }

    // --- Wizard navigation ---
    function updateStepIndicator() {
        for (let i = 1; i <= 3; i++) {
            const node = document.getElementById('node_step_' + i);
            if (node) {
                if (i < currentStep) {
                    node.classList.add('completed');
                    node.classList.remove('active');
                } else if (i === currentStep) {
                    node.classList.add('active');
                    node.classList.remove('completed');
                } else {
                    node.classList.remove('active', 'completed');
                }
            }
        }
        const progressPercent = ((currentStep - 1) / 2) * 100;
        const progressLine = document.getElementById('step_line_progress');
        if (progressLine) {
            progressLine.style.width = progressPercent + '%';
        }
    }

    function showStep(step) {
        document.getElementById('step_1_content').style.display = 'none';
        document.getElementById('step_2_content').style.display = 'none';
        document.getElementById('step_3_content').style.display = 'none';

        document.getElementById('step_' + step + '_content').style.display = 'block';

        if (step === 1) {
            document.getElementById('btn_cancel').style.display = 'inline-flex';
            document.getElementById('btn_prev').style.display = 'none';
            document.getElementById('btn_next').style.display = 'inline-flex';
            document.getElementById('btn_submit_approval').style.display = 'none';
        } else if (step === 2) {
            document.getElementById('btn_cancel').style.display = 'none';
            document.getElementById('btn_prev').style.display = 'inline-flex';
            document.getElementById('btn_next').style.display = 'inline-flex';
            document.getElementById('btn_submit_approval').style.display = 'none';
        } else if (step === 3) {
            document.getElementById('btn_cancel').style.display = 'none';
            document.getElementById('btn_prev').style.display = 'inline-flex';
            document.getElementById('btn_next').style.display = 'none';
            document.getElementById('btn_submit_approval').style.display = 'inline-flex';
            renderReviewData();
        }
        updateStepIndicator();
    }

    function nextStep() {
        if (currentStep === 1) {
            clearErrors();
            if (!validateStep(1)) {
                showStep(1);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
        } else if (currentStep === 2) {
            clearErrors();
            const s1ok = validateStep(1);
            const s2ok = validateStep(2);
            if (!s1ok || !s2ok) {
                showStep(!s1ok ? 1 : 2);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
        }
        currentStep++;
        showStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function prevStep() {
        currentStep--;
        clearErrors();
        showStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function clearErrors() {
        document.querySelectorAll('.rl-input.error, .rl-select.error, .rl-textarea.error, .rl-chk-grid.error')
            .forEach(el => el.classList.remove('error'));
        const box = document.getElementById('form_validation_summary');
        if (box) { box.classList.add('rl-hidden'); box.innerHTML = ''; }
    }

    function showValidationMessages(messages) {
        const box = document.getElementById('form_validation_summary');
        if (!box) return;
        if (!messages.length) { box.classList.add('rl-hidden'); box.innerHTML = ''; return; }
        const list = messages.map(m => '<li>' + escapeHtml(m) + '</li>').join('');
        box.innerHTML = '<strong>Periksa kembali data berikut sebelum melanjutkan:</strong><ul>' + list + '</ul>';
        box.classList.remove('rl-hidden');
    }

    function validateStep(step) {
        const messages = [];
        if (step === 1) {
            const asuransiInput = document.getElementById('form_nama_asuransi');
            const aliasInput = document.getElementById('alias_versi_tera');
            const penjaminSelect = document.getElementById('penjamin_category');
            const tglMulai = document.getElementById('form_tanggal_mulai');
            const tglAkhir = document.getElementById('form_tanggal_berakhir');

            if (!asuransiInput.value.trim()) {
                asuransiInput.classList.add('error');
                messages.push('Nama Asuransi wajib diisi.');
            }

            if (!aliasInput.value.trim()) {
                aliasInput.classList.add('error');
                messages.push('Alias Versi Tera wajib diisi.');
            }

            if (!penjaminSelect.value) {
                penjaminSelect.classList.add('error');
                const penjaminTrigger = penjaminSelect.closest('.ssd')?.querySelector('.ssd-trigger');
                if (penjaminTrigger) penjaminTrigger.classList.add('error');
                messages.push('TPA / PAYOR wajib dipilih.');
            }

            if (!tglMulai.value) {
                tglMulai.classList.add('error');
                messages.push('Tanggal Mulai wajib diisi.');
            }

            if (!tglAkhir.value) {
                tglAkhir.classList.add('error');
                messages.push('Tanggal Berakhir wajib diisi.');
            }

            if (tglMulai.value && tglAkhir.value) {
                if (new Date(tglMulai.value) > new Date(tglAkhir.value)) {
                    tglAkhir.classList.add('error');
                    messages.push('Tanggal Berakhir harus sama atau setelah Tanggal Mulai.');
                }
            }
        } else if (step === 2) {
            let gi = 1;
            document.querySelectorAll('#bu_groups_container .bu-group-card').forEach(function(card) {
                const groupLabel = 'Badan Usaha #' + gi;
                const hidden = card.querySelector('.bu-autocomplete-hidden');
                if (!hidden || !hidden.value) {
                    const input = card.querySelector('.bu-autocomplete-input');
                    if (input) input.classList.add('error');
                    messages.push(groupLabel + ': pilih Badan Usaha terlebih dahulu.');
                }

                const checkedLayanan = card.querySelectorAll('input[name$="[jenis_layanan_ids][]"]:checked');
                if (checkedLayanan.length === 0) {
                    const grid = card.querySelector('.rl-chk-grid');
                    if (grid) grid.classList.add('error');
                    messages.push(groupLabel + ': pilih minimal 1 Jenis Layanan.');
                }
                gi++;
            });
        }
        showValidationMessages(messages);
        return messages.length === 0;
    }

    function renderReviewData() {
        // Informasi Dasar
        document.getElementById('rev_nama_asuransi').textContent = document.getElementById('form_nama_asuransi').value || '-';
        document.getElementById('rev_alias_versi_tera').textContent = document.getElementById('alias_versi_tera').value || '-';

        const categoryVal = document.getElementById('penjamin_category').value;
        document.getElementById('rev_penjamin_category').textContent = categoryVal || '-';

        const isHighProfile = document.getElementById('is_high_profile').checked;
        document.getElementById('rev_is_high_profile').innerHTML = isHighProfile
            ? '<span class="rl-high-profile-badge">Ya (High Profile)</span>'
            : 'Tidak';

        const tglMulaiVal = document.getElementById('form_tanggal_mulai').value;
        const tglAkhirVal = document.getElementById('form_tanggal_berakhir').value;

        function formatDateIndo(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
        }

        document.getElementById('rev_periode').textContent = formatDateIndo(tglMulaiVal) + ' s.d. ' + formatDateIndo(tglAkhirVal);

        const statusHidden = document.getElementById('status_hidden');
        if (statusHidden) {
            document.getElementById('rev_status').textContent = statusHidden.value === 'aktif' ? 'Aktif' : 'Tidak Aktif';
            document.getElementById('rev_status_item').style.display = 'block';
        } else {
            document.getElementById('rev_status_item').style.display = 'none';
        }

        // BU Groups Review (includes layanan & obat per group)
        const revBuContainer = document.getElementById('rev_bu_groups');
        revBuContainer.innerHTML = '';
        document.querySelectorAll('#bu_groups_container .bu-group-card').forEach(function(card, gi) {
            const hidden = card.querySelector('.bu-autocomplete-hidden');
            const buName = card.querySelector('.bu-autocomplete-input').value || '-';
            const buId = hidden ? hidden.value : '';

            const benefits = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][benefit][]"]').forEach(function(el) {
                if (el.value.trim()) benefits.push(el.value.trim());
            });
            const cakupans = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][cakupan][]"]').forEach(function(el) {
                if (el.value.trim()) cakupans.push(el.value.trim());
            });

            const layananList = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][jenis_layanan_ids][]"]:checked').forEach(function(chk) {
                const name = jenisLayananMap[chk.value];
                if (name) layananList.push(name);
            });

            const obatList = [];
            card.querySelectorAll('input[name="bu_groups[' + gi + '][obat_ids][]"]').forEach(function(el) {
                const name = masterObatMap[el.value];
                if (name) obatList.push(name);
            });

            const cardReview = document.createElement('div');
            cardReview.className = 'rl-review-card-compact';

            var benefitsHtml = '-';
            if (benefits.length > 0) {
                var ul = document.createElement('ul');
                ul.className = 'rl-review-ul';
                benefits.forEach(function(b) {
                    var li = document.createElement('li');
                    li.textContent = b;
                    ul.appendChild(li);
                });
                benefitsHtml = ul.outerHTML;
            }

            var cakupansHtml = '-';
            if (cakupans.length > 0) {
                var ul = document.createElement('ul');
                ul.className = 'rl-review-ul';
                cakupans.forEach(function(c) {
                    var li = document.createElement('li');
                    li.textContent = c;
                    ul.appendChild(li);
                });
                cakupansHtml = ul.outerHTML;
            }

            var layananHtml = '-';
            if (layananList.length > 0) {
                layananHtml = layananList.map(function(l) {
                    return '<span class="rl-badge-review-layanan">' + l + '</span>';
                }).join('');
            }

            var obatHtml = '-';
            if (obatList.length > 0) {
                obatHtml = obatList.map(function(o) {
                    return '<span class="rl-badge-review-obat">' + o + '</span>';
                }).join('');
            }

            const deskripsiEl = card.querySelector('textarea[name="bu_groups[' + gi + '][deskripsi]"]');
            const deskripsiVal = deskripsiEl ? deskripsiEl.value.trim() : '';

            cardReview.innerHTML =
                '<h4 class="rl-review-card-heading">Badan Usaha ' + (gi + 1) + ': ' + escapeHtml(buName) + '</h4>' +
                '<div class="rl-review-grid-2col">' +
                    '<div><strong>Benefit:</strong> ' + benefitsHtml + '</div>' +
                    '<div><strong>Cakupan:</strong> ' + cakupansHtml + '</div>' +
                '</div>' +
                '<div class="rl-review-grid-2col rl-review-section-bordered">' +
                    '<div><strong>Jenis Layanan:</strong><br>' + layananHtml + '</div>' +
                    '<div><strong>Daftar Obat:</strong><br>' + obatHtml + '</div>' +
                '</div>' +
                (deskripsiVal ? '<div class="rl-review-deskripsi"><strong>Deskripsi:</strong> ' + escapeHtml(deskripsiVal) + '</div>' : '');
            revBuContainer.appendChild(cardReview);
        });
    }

    // --- Document Ready ---
    document.addEventListener('DOMContentLoaded', function () {
        clearReturnInfo();

        const statusToggle = document.getElementById('status_toggle');
        const statusLabel = document.getElementById('status_label');
        const statusHidden = document.getElementById('status_hidden');
        if (statusToggle && statusLabel && statusHidden) {
            statusToggle.addEventListener('change', function () {
                if (this.checked) {
                    statusLabel.textContent = 'Aktif';
                    statusHidden.value = 'aktif';
                } else {
                    statusLabel.textContent = 'Tidak Aktif';
                    statusHidden.value = 'tidak_aktif';
                }
            });
            statusLabel.textContent = statusToggle.checked ? 'Aktif' : 'Tidak Aktif';
        }

        const draft = getDraft();
        if (draft) {
            // Restore simple fields
            const setVal = function(id, val) {
                const el = document.getElementById(id);
                if (el && val !== undefined) el.value = val;
            };
            setVal('form_nama_asuransi', draft.nama_asuransi);
            setVal('alias_versi_tera', draft.alias_versi_tera);
            setVal('penjamin_category', draft.penjamin_category);
            if (window.__searchableSelects && window.__searchableSelects['penjamin_category']) {
                window.__searchableSelects['penjamin_category'].select(draft.penjamin_category || null);
            }
            document.getElementById('is_high_profile').checked = !!draft.is_high_profile;
            setVal('form_tanggal_mulai', draft.tanggal_mulai);
            setVal('form_tanggal_berakhir', draft.tanggal_berakhir);
            const statusHidden = document.getElementById('status_hidden');
            const statusToggle = document.getElementById('status_toggle');
            const statusLabel = document.getElementById('status_label');
            if (statusHidden && draft.status) {
                statusHidden.value = draft.status;
                const isActive = draft.status === 'aktif';
                if (statusToggle) statusToggle.checked = isActive;
                if (statusLabel) statusLabel.textContent = isActive ? 'Aktif' : 'Tidak Aktif';
            }

            // Restore BU groups (includes layanan, obat, deskripsi per group)
            if (draft.bu_groups && draft.bu_groups.length) {
                document.getElementById('bu_groups_container').innerHTML = '';
                buGroupIndex = 0;
                draft.bu_groups.forEach(function(g) {
                    addBuGroup(g);
                });
            }

            // Restore step
            if (draft.step && draft.step >= 1 && draft.step <= 3) {
                currentStep = draft.step;
            }
        }
        // Ensure autocompletes attached to any server-rendered group.
        // (idempotent: initBuAutocomplete/initObatAutocomplete guard with data-rl-init)
        initAllBuAutocompletes();
        initAllObatAutocompletes();
        showResetButton();

        // Auto-save on input changes
        document.getElementById('ruang_lingkup_form')?.addEventListener('input', saveFormDraft);
        document.getElementById('ruang_lingkup_form')?.addEventListener('change', saveFormDraft);

        // Clear localStorage draft on submit (tidak boleh ter-bawa setelah pengajuan).
        document.getElementById('ruang_lingkup_form')?.addEventListener('submit', function () {
            clearFormDraft();
        });

        // Leave confirmation
        var leaveUrl = '';
        document.getElementById('btn_cancel')?.addEventListener('click', function (e) {
            leaveUrl = this.href;
            e.preventDefault();
            document.getElementById('leaveModal').classList.add('dm-active');
        });
        document.getElementById('leaveBtnCancel')?.addEventListener('click', function () {
            document.getElementById('leaveModal').classList.remove('dm-active');
        });
        document.getElementById('leaveBtnConfirm')?.addEventListener('click', function () {
            window.location.href = leaveUrl;
        });
        document.getElementById('leaveModal')?.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('dm-active');
        });

        // Save return info for external links
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-return="form"]');
            if (link) {
                saveReturnInfo();
            }
        });

        // Initialize wizard step
        showStep(currentStep);
    });
</script>
