{{--
    Searchable Dropdown Component
    Bergantung pada Alpine.js (sudah dimuat di layouts) dan public/css/searchable-select.css

    Usage (form):
    <x-searchable-select
        name="field_name"
        label="Label Text"
        :options="$collection"            (Collection/array of models ATAU ['value'=>..,'text'=>..])
        value-field="id"                  (default 'value')
        label-field="name"                (default 'text')
        :selected="$selectedValue"        (atau :value="...")
        placeholder="Select option..."
        :required="true"
        :nullable="true"
        :disabled-values="[1,2]"
        input-class="form-input-minimal"
    />

    Usage (filter, mis. header tabel):
    <x-searchable-select
        name="nama_asuransi"
        input-id="namaAsuransiFilter"
        :options="$list"
        placeholder="Cari..."
        input-class="filter-select"
        onselect="applyNameFilter"
        autosubmit
        nullable
    />
--}}

@props([
    'name' => null,
    'label' => null,
    'options' => [],
    'valueField' => 'value',
    'labelField' => 'text',
    'groupField' => null,
    'selected' => null,
    'value' => null,
    'placeholder' => 'Pilih...',
    'required' => false,
    'disabled' => false,
    'emptyOption' => null,
    'nullable' => false,
    'disabledValues' => [],
    'inputClass' => '',
    'autosubmit' => false,
    'id' => null,
    'inputId' => null,
    'onselect' => null,
    'error' => null
])

@php
    $mapped = collect($options)
        ->filter()
        ->map(function ($opt) use ($valueField, $labelField, $groupField) {
            if (is_array($opt)) {
                return [
                    'value' => $opt[$valueField] ?? $opt['value'] ?? $opt['id'] ?? null,
                    'label' => $opt[$labelField] ?? $opt['text'] ?? $opt['name'] ?? null,
                    'group' => $groupField ? ($opt[$groupField] ?? null) : null,
                ];
            }
            return [
                'value' => data_get($opt, $valueField),
                'label' => data_get($opt, $labelField),
                'group' => $groupField ? data_get($opt, $groupField) : null,
            ];
        })
        ->values()
        ->all();

    $blocked = collect($disabledValues)->map(fn ($v) => (string) $v)->all();
    $mapped = array_values(array_filter($mapped, fn ($opt) => ! in_array((string) $opt['value'], $blocked, true)));

    $resolved = $value ?? $selected;
    $selectedValue = $name ? old($name, $resolved) : $resolved;
    $emptyOption = $emptyOption ?? ($nullable ? ($autosubmit ? $placeholder : '-- Tidak Ada --') : null);
    $triggerId = $inputId ?? ('ssd-' . Str::random(8));
    $wrapperId = $id;
@endphp

<div
    x-data="searchableDropdown({
        options: {{ Js::from($mapped) }},
        selected: {{ Js::from($selectedValue) }},
        placeholder: {{ Js::from($placeholder) }},
        emptyOption: {{ Js::from($emptyOption) }},
        autosubmit: {{ $autosubmit ? 'true' : 'false' }},
        onselect: {{ $onselect ? Js::from($onselect) : 'null' }},
        registerId: {{ $inputId ? Js::from($inputId) : 'null' }}
    })"
    @if($wrapperId) id="{{ $wrapperId }}" @endif
    class="ssd"
    :class="{ 'drop-up': dropUp }"
    @click.away="close()"
>
    @if($label)
    <label class="block text-sm font-semibold text-sp-navy mb-1" style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:0.5rem">
        {{ $label }}
        @if($required)
            <span style="color:#ef4444">*</span>
        @endif
    </label>
    @endif

    <input type="hidden" @if($name) name="{{ $name }}" @endif :value="selectedValue ?? ''" x-ref="hidden" @if($inputId) id="{{ $inputId }}" @endif>

    <button
        type="button"
        id="{{ $triggerId }}"
        @click="toggle()"
        :disabled="{{ $disabled ? 'true' : 'false' }}"
        class="ssd-trigger {{ $inputClass }}"
        :class="{ 'ssd-open': open }"
    >
        <span x-text="displayText" class="ssd-value" :class="{ 'is-placeholder': !selectedValue }"></span>
        <span class="ssd-caret">
            <x-icon name="chevron-down" class="icon-14" />
        </span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-ref="dropdown"
        class="ssd-panel"
        x-cloak
    >
        <div class="ssd-search">
            <x-icon name="search" class="icon-14" />
            <input
                type="text"
                x-model="search"
                x-ref="searchInput"
                @keydown.escape="close()"
                @keydown.enter.prevent="selectFirst()"
                placeholder="Cari..."
            >
        </div>

        <ul class="ssd-list">
            <template x-if="emptyOption !== null">
                <li
                    @click="select(null)"
                    class="ssd-option"
                    :class="{ 'is-active': selectedValue === null || selectedValue === '' }"
                >
                    <span class="ssd-option-label" x-text="emptyOption"></span>
                    <span x-show="selectedValue === null || selectedValue === ''" class="ssd-option-check">
                        <x-icon name="check" class="icon-14" />
                    </span>
                </li>
            </template>

            <template x-for="option in filteredOptions" :key="option.value">
                <li
                    @click="select(option.value)"
                    class="ssd-option"
                    :class="{ 'is-active': selectedValue == option.value }"
                >
                    <span class="ssd-option-label" x-text="option.label"></span>
                    <span x-show="option.group" class="ssd-option-group" x-text="'(' + option.group + ')'"></span>
                    <span x-show="selectedValue == option.value" class="ssd-option-check">
                        <x-icon name="check" class="icon-14" />
                    </span>
                </li>
            </template>

            <template x-if="filteredOptions.length === 0 && search">
                <li class="ssd-noresults">
                    Tidak ada hasil untuk "<span x-text="search"></span>"
                </li>
            </template>
        </ul>
    </div>

    @if($error)
        <p class="mt-1 text-xs text-red-500" style="margin-top:0.25rem;font-size:10px;color:#ef4444">{{ $error }}</p>
    @elseif($name)
        @error($name)
            <p class="mt-1 text-xs text-red-500" style="margin-top:0.25rem;font-size:10px;color:#ef4444">{{ $message }}</p>
        @enderror
    @endif
</div>

@once
@push('scripts')
<script>
function searchableDropdown(config) {
    return {
        open: false,
        search: '',
        dropUp: false,
        options: config.options || [],
        selectedValue: config.selected,
        placeholder: config.placeholder || 'Pilih...',
        emptyOption: config.emptyOption || null,
        autosubmit: !!config.autosubmit,
        onselect: config.onselect || null,
        registerId: config.registerId || null,

        init() {
            if (this.registerId) {
                window.__searchableSelects = window.__searchableSelects || {};
                window.__searchableSelects[this.registerId] = this;
            }
        },

        get filteredOptions() {
            if (!this.search) return this.options;
            const query = this.search.toLowerCase();
            return this.options.filter(opt =>
                (opt.label && opt.label.toLowerCase().includes(query)) ||
                (opt.group && opt.group.toLowerCase().includes(query))
            );
        },

        get displayText() {
            if (this.selectedValue === null || this.selectedValue === '') {
                return this.placeholder;
            }
            const found = this.options.find(opt => String(opt.value) === String(this.selectedValue));
            return found ? found.label : this.placeholder;
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                const rect = this.$el.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                const spaceAbove = rect.top;
                this.dropUp = spaceBelow < 250 && spaceAbove > spaceBelow;
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                });
            }
        },

        close() {
            this.open = false;
            this.search = '';
        },

        select(value) {
            this.selectedValue = value;
            if (this.$refs.hidden) this.$refs.hidden.value = value === null || value === undefined ? '' : value;
            this.close();
            if (this.onselect && typeof window[this.onselect] === 'function') {
                const found = this.options.find(opt => String(opt.value) === String(value));
                window[this.onselect](value, found ? found.label : '', this.$refs.hidden);
            }
            if (this.autosubmit) {
                const form = this.$el.closest('form');
                if (form) form.submit();
            }
        },

        selectFirst() {
            if (this.filteredOptions.length > 0) {
                this.select(this.filteredOptions[0].value);
            }
        }
    }
}
</script>
@endpush
@endonce
