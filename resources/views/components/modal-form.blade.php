{{--
    Modal Form general: gabungan <x-modal> dengan <form>.

    Penggunaan (submit biasa, full page reload):
    <x-modal-form id="buModal" title="Tambah Badan Usaha" action="{{ route('admin.master-badan-usaha.store') }}" submit-text="Simpan">
        <label class="rl-label">Nama</label>
        <input type="text" name="nama_bu" class="rl-input" required>
    </x-modal-form>

    Penggunaan (AJAX, halaman tidak reload):
    <x-modal-form id="buModal" title="Tambah Badan Usaha" action="{{ route('admin.master-badan-usaha.store') }}" :ajax="true">
        ...
    </x-modal-form>

    Props:
    - id         : id modal (juga dipakai sebagai prefix form: "<id>-form")
    - action     : URL tujuan submit
    - method     : GET|POST|PUT|PATCH|DELETE (selain GET/POST otomatis pakai @method spoofing)
    - size       : sm|md|lg|xl (diteruskan ke <x-modal>)
    - submitText : teks tombol submit di footer
    - submitIcon : nama icon <x-icon> untuk tombol submit
    - ajax       : true = submit via fetch, halaman tidak reload

    Slot tambahan:
    - footer     : override footer default (tombol Batal + Submit) bila perlu

    Event (khusus ajax) yang di-dispatch ke document setelah sukses:
    'modal-form:success' dengan detail { id: '<id modal>', data: <json response> }

    Aksi JS:
    window.XModal.open('buModal');
    window.XModal.close('buModal');
--}}

@props([
    'id' => null,
    'title' => '',
    'action' => '#',
    'method' => 'POST',
    'size' => 'sm',
    'submitText' => 'Simpan',
    'submitIcon' => 'plus-circle-solid',
    'ajax' => false,
])

@php
    $modalId = $id ?? 'modal-form-' . \Illuminate\Support\Str::random(6);
    $formId = $modalId . '-form';
    $methodUpper = strtoupper($method);
    $spoofMethod = ! in_array($methodUpper, ['GET', 'POST'], true);
@endphp

<x-modal :id="$modalId" :title="$title" :size="$size">
    <form id="{{ $formId }}" action="{{ $action }}" method="{{ $spoofMethod ? 'POST' : $methodUpper }}"
          class="modal-form" data-modal-form data-ajax="{{ $ajax ? '1' : '0' }}">
        @csrf
        @if($spoofMethod)
            @method($methodUpper)
        @endif

        <div class="modal-form-errors" data-modal-form-errors role="alert" hidden></div>

        {{ $slot }}
    </form>

    @isset($footer)
        <x-slot name="footer">{{ $footer }}</x-slot>
    @else
        <x-slot name="footer">
            <button type="button" class="btn-secondary rl-btn-compact" onclick="XModal.close('{{ $modalId }}')">Batal</button>
            <button type="submit" form="{{ $formId }}" class="btn-primary modal-form-submit" data-modal-form-submit>
                <x-icon :name="$submitIcon" class="icon-16" />
                <span>{{ $submitText }}</span>
            </button>
        </x-slot>
    @endisset
</x-modal>

@once
<style>
    .modal-form-errors {
        margin-bottom: .85rem;
        padding: .6rem .8rem;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
        border-radius: 10px;
        font-size: .82rem;
        line-height: 1.45;
    }

    .modal-form-errors ul {
        margin: .25rem 0 0 1.1rem;
        padding: 0;
    }

    .modal-form-submit[disabled] {
        opacity: .6;
        cursor: not-allowed;
    }
</style>

<script>
    (function () {
        function getCsrf() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) return meta.content;
            var input = document.querySelector('input[name="_token"]');
            return input ? input.value : '';
        }

        function showErrors(box, messages) {
            if (!box) return;
            if (!messages || !messages.length) {
                box.hidden = true;
                box.innerHTML = '';
                return;
            }
            box.innerHTML = messages.length === 1
                ? escapeHtmlModalForm(messages[0])
                : '<ul>' + messages.map(function (m) { return '<li>' + escapeHtmlModalForm(m) + '</li>'; }).join('') + '</ul>';
            box.hidden = false;
        }

        function escapeHtmlModalForm(str) {
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        document.addEventListener('submit', function (e) {
            var form = e.target && e.target.closest ? e.target.closest('form[data-modal-form]') : null;
            if (!form || form.dataset.ajax !== '1') return; // submit biasa, biarkan browser yang proses

            e.preventDefault();

            var modal = form.closest('.xmodal-overlay');
            var errBox = form.querySelector('[data-modal-form-errors]');
            var submitBtn = (modal && modal.querySelector('[data-modal-form-submit]'))
                || form.querySelector('[data-modal-form-submit]')
                || document.querySelector('[data-modal-form-submit][form="' + form.id + '"]');

            if (submitBtn) submitBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            })
                .then(function (res) {
                    return res.json()
                        .then(function (data) { return { ok: res.ok, status: res.status, data: data || {} }; })
                        .catch(function () { return { ok: res.ok, status: res.status, data: {} }; });
                })
                .then(function (r) {
                    if (r.ok) {
                        showErrors(errBox, []);
                        form.reset();
                        if (modal && window.XModal) XModal.close(modal.id);
                        document.dispatchEvent(new CustomEvent('modal-form:success', {
                            detail: { id: modal ? modal.id : null, data: r.data },
                        }));
                        return;
                    }

                    var messages = [];
                    if (r.data && r.data.errors) {
                        Object.keys(r.data.errors).forEach(function (key) {
                            var arr = r.data.errors[key];
                            (Array.isArray(arr) ? arr : [arr]).forEach(function (m) { messages.push(m); });
                        });
                    } else if (r.data && r.data.message) {
                        messages.push(r.data.message);
                    } else {
                        messages.push('Terjadi kesalahan (HTTP ' + r.status + ').');
                    }
                    showErrors(errBox, messages);
                })
                .catch(function () {
                    showErrors(errBox, ['Gagal terhubung ke server. Coba lagi.']);
                })
                .finally(function () {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    })();
</script>
@endonce
