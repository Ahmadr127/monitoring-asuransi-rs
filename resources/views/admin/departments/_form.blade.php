@php
    $isEdit = isset($unit) && $unit->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.departments.update', $unit) : route('admin.departments.store') }}" class="department-form">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid-form" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem">
        <div class="form-group">
            <label>Nama Departemen <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-input-minimal" required
                   value="{{ old('name', $isEdit ? $unit->name : '') }}" placeholder="mis. Legal">
        </div>
        <div class="form-group">
            <label>Kode <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-input-minimal" required style="text-transform:uppercase"
                   value="{{ old('code', $isEdit ? $unit->code : '') }}" placeholder="mis. LEGAL">
        </div>
        <x-searchable-select
            name="type_id"
            label="Tipe"
            :options="$types->map(fn ($t) => ['value' => $t->id, 'text' => $t->display_name])"
            :value="$isEdit ? $unit->type_id : ''"
            placeholder="Pilih tipe..."
            required
        />
        <x-searchable-select
            name="parent_id"
            label="Induk"
            :options="$unitOptions->map(fn ($o) => ['value' => $o->id, 'text' => $o->name])->when($isEdit, fn ($c) => $c->filter(fn ($o) => (int) $o['value'] !== (int) $unit->id))"
            :value="$isEdit ? $unit->parent_id : ''"
            placeholder="Tanpa induk..."
            nullable
        />
        <x-searchable-select
            name="head_id"
            label="Kepala Departemen"
            :options="$users->map(fn ($u) => ['value' => $u->id, 'text' => $u->name . ' (' . ($u->role?->name ?? '-') . ')'])"
            :value="$isEdit ? $unit->head_id : ''"
            placeholder="Pilih kepala..."
            nullable
        />
        <div class="form-group">
            <label class="flex-between-center">
                <span>Status Aktif</span>
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $isEdit ? $unit->is_active : true) ? 'checked' : '' }}>
            </label>
        </div>
    </div>

    <div class="form-group mb-8">
        <label>Keterangan</label>
        <textarea name="description" class="form-input-minimal" rows="2">{{ old('description', $isEdit ? $unit->description : '') }}</textarea>
    </div>

    <div class="form-actions-premium">
        <a href="{{ $isEdit ? route('admin.departments.show', $unit) : route('admin.departments.index') }}" class="btn-secondary">
            Batal
        </a>
        <button type="submit" class="btn-premium-submit w-auto">
            <x-icon name="check" class="icon-18" />
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Departemen' }}
        </button>
    </div>
</form>