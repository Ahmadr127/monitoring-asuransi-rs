{{-- Searchable Select untuk memilih ruang lingkup / asuransi.
     Memakai pola autocomplete bawaan codebase (.input-container + .rl-autocomplete)
     sehingga dropdown menempel tepat di bawah input dan lebarnya sama dengan input. --}}

@props([
    'name' => 'ruang_lingkup_id',
    'id' => null,
    'value' => null,
    'ruangLingkups' => [],
    'disabledIds' => [],
    'placeholder' => '-- Pilih Nama Asuransi --',
    'label' => 'Nama Asuransi',
    'required' => false,
    'allowClear' => false,
])

@php
    $id = $id ?? 'asuransi_select_' . Str::random(6);
    $value = $value ?? request('ruang_lingkup_id');

    // Urutkan: data yang bisa dipilih di atas, yang sudah ada (disabled) di bawah.
    $sorted = collect($ruangLingkups)->sortBy(function ($rl) use ($disabledIds) {
        return in_array($rl->id, $disabledIds) ? 1 : 0;
    })->values();

    $options = $sorted->map(fn ($rl) => [
        'value' => (string) $rl->id,
        'text' => $rl->nama_asuransi,
        'disabled' => in_array($rl->id, $disabledIds),
    ])->all();

    $selected = $sorted->first(fn ($rl) => (string) $rl->id === (string) $value);
    $selectedLabel = $selected?->nama_asuransi ?? '';
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}_input">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    @endif
    <div class="input-container">
        <input type="text" id="{{ $id }}_input"
               class="form-input-minimal rl-search-select-input"
               placeholder="{{ $placeholder }}"
               value="{{ $selectedLabel }}"
               autocomplete="off"
               {{ $required ? 'required' : '' }}>
        <input type="hidden" name="{{ $name }}" id="{{ $id }}" class="rl-search-select-hidden" value="{{ $value }}">
        <i class="rl-search-select-caret rl-search-select-caret-{{ $id }}" aria-hidden="true">
            <x-icon name="chevron-down" class="icon-14" />
        </i>
        <div class="rl-autocomplete rl-search-select-dropdown"></div>
        <script type="application/json" class="rl-search-select-options">@json($options)</script>
    </div>
</div>

@once
<style>
    .rl-search-select-input { padding-right: 2rem !important; }
    .rl-search-select-caret {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        display: flex;
        align-items: center;
    }
    .rl-search-select-dropdown {
        max-height: 200px;
        min-width: 100%;
        width: 100%;
        max-width: none;
        border-radius: 8px;
        border-top: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }
    .rl-search-select-dropdown .hint-existing {
        color: #94a3b8;
        font-size: 10px;
        font-weight: 400;
        margin-left: 4px;
    }
</style>
<script>
    (function () {
        if (window.__rlSearchSelectInit) return;
        window.__rlSearchSelectInit = true;

        function wire(input) {
            var wrap = input.parentElement;                 // .input-container
            var hidden = wrap.querySelector('.rl-search-select-hidden');
            var dropdown = wrap.querySelector('.rl-search-select-dropdown');
            var optionsEl = wrap.querySelector('.rl-search-select-options');
            if (!hidden || !dropdown || !optionsEl) return;

            var options = [];
            try { options = JSON.parse(optionsEl.textContent || '[]'); } catch (e) {}
            var currentIndex = -1;

            function render(query) {
                var q = (query || '').toLowerCase();
                var filtered = options.filter(function (o) {
                    return !q || o.text.toLowerCase().indexOf(q) !== -1;
                });
                if (!filtered.length) {
                    dropdown.style.display = 'none';
                    dropdown.innerHTML = '';
                    return;
                }
                var html = '';
                filtered.forEach(function (o) {
                    html += '<div class="rl-autocomplete-item' + (o.disabled ? ' disabled' : '') + '"' +
                        ' data-value="' + o.value + '"' +
                        ' data-text="' + String(o.text).replace(/"/g, '&quot;') + '"' +
                        (o.disabled ? ' data-disabled="1"' : '') + '>' +
                        o.text + (o.disabled ? ' <span class="hint-existing">(sudah ada)</span>' : '') +
                        '</div>';
                });
                dropdown.innerHTML = html;
                dropdown.style.display = 'block';
                currentIndex = -1;
            }

            function select(item) {
                input.value = item.getAttribute('data-text');
                hidden.value = item.getAttribute('data-value');
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
            }

            input.addEventListener('focus', function () { render(this.value); });
            input.addEventListener('click', function () {
                if (dropdown.style.display !== 'block') render(this.value);
            });
            input.addEventListener('input', function () {
                if (!this.value) hidden.value = '';
                render(this.value);
            });
            input.addEventListener('keydown', function (e) {
                if (dropdown.style.display !== 'block') return;
                var items = dropdown.querySelectorAll('.rl-autocomplete-item:not(.disabled)');
                if (!items.length) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (currentIndex >= 0) items[currentIndex].classList.remove('active');
                    currentIndex = (currentIndex + 1) % items.length;
                    items[currentIndex].classList.add('active');
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (currentIndex >= 0) items[currentIndex].classList.remove('active');
                    currentIndex = currentIndex <= 0 ? items.length - 1 : currentIndex - 1;
                    items[currentIndex].classList.add('active');
                } else if (e.key === 'Enter') {
                    if (currentIndex >= 0 && items[currentIndex]) {
                        e.preventDefault();
                        select(items[currentIndex]);
                    }
                } else if (e.key === 'Escape') {
                    dropdown.style.display = 'none';
                }
            });

            dropdown.addEventListener('click', function (e) {
                var item = e.target.closest('.rl-autocomplete-item');
                if (item && item.getAttribute('data-disabled') !== '1') select(item);
            });

            document.addEventListener('click', function (e) {
                if (!wrap.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            var form = input.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (!hidden.value) input.value = '';
                });
            }
        }

        function init() {
            document.querySelectorAll('.rl-search-select-input').forEach(function (input) {
                wire(input);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
@endonce