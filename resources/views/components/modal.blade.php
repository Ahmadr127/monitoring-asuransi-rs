{{--
    Modal reusable: header tetap, body scroll, footer tetap terlihat.
    CSS & JS mandiri di dalam komponen (tidak digabung ke file css lain).

    Penggunaan:
    <x-modal id="editModal" title="Edit Role" size="md">
        <form id="editForm" method="POST"> ... </form>

        <x-slot name="footer">
            <button type="submit" form="editForm" class="btn-premium-submit">Simpan</button>
        </x-slot>
    </x-modal>

    Aksi:
    window.XModal.open('editModal');
    window.XModal.close('editModal');
--}}

@props([
    'id' => null,
    'title' => '',
    'size' => 'md',
    'closeable' => true,
    'overlayClose' => true,
])

@php
    $modalId = $id ?? 'modal-' . \Illuminate\Support\Str::random(6);
    $sizeClass = in_array($size, ['sm', 'lg', 'xl'], true) ? 'xmodal-' . $size : '';
@endphp

<div id="{{ $modalId }}" class="xmodal-overlay" role="dialog" aria-modal="true"
     aria-label="{{ $title ?: $modalId }}" data-overlay-close="{{ $overlayClose ? '1' : '0' }}" tabindex="-1">
    <div class="xmodal {{ $sizeClass }}">
        @if($title || $closeable)
        <div class="xmodal-header">
            <h3 class="xmodal-title">{{ $title }}</h3>
            @if($closeable)
            <button type="button" class="xmodal-close" onclick="XModal.close('{{ $modalId }}')" aria-label="Tutup">
                <x-icon name="close" class="icon-20" />
            </button>
            @endif
        </div>
        @endif

        <div class="xmodal-body">
            {{ $slot }}
        </div>

        @isset($footer)
        <div class="xmodal-footer">
            {{ $footer }}
        </div>
        @endisset
    </div>
</div>

@once
<style>
    .xmodal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(2px);
    }

    .xmodal-overlay.xmodal-open {
        display: flex;
    }

    .xmodal {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 560px;
        max-height: calc(100vh - 2rem);
        margin: auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: xmodalIn 0.2s ease-out;
    }

    .xmodal-sm { max-width: 420px; }
    .xmodal-lg { max-width: 800px; }
    .xmodal-xl { max-width: 1024px; }

    .xmodal-header {
        flex: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }

    .xmodal-title {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
    }

    .xmodal-close {
        border: none;
        background: #f1f5f9;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .xmodal-close:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .xmodal-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 1.25rem 1.5rem;
    }

    .xmodal-footer {
        flex: none;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 0.9rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    @keyframes xmodalIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    @media (max-width: 640px) {
        .xmodal-overlay { padding: 0.5rem; }
        .xmodal-header  { padding: 0.9rem 1rem; }
        .xmodal-body    { padding: 1rem; }
        .xmodal-footer  { padding: 0.75rem 1rem; }
    }
</style>

<script>
    window.XModal = (function () {
        let activeId = null;

        function open(id) {
            const el = document.getElementById(id);
            if (!el) return;
            document.querySelectorAll('.xmodal-overlay.xmodal-open').forEach(function (m) {
                if (m.id !== id) close(m.id);
            });
            el.classList.add('xmodal-open');
            activeId = id;
            document.body.style.overflow = 'hidden';
        }

        function close(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('xmodal-open');
            if (activeId === id) {
                activeId = null;
                document.body.style.overflow = '';
            }
        }

        function closeActive() {
            if (activeId) close(activeId);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (e) {
                const overlay = e.target.closest('.xmodal-overlay');
                if (overlay && e.target === overlay && overlay.dataset.overlayClose === '1') {
                    close(overlay.id);
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeActive();
            });
        });

        return { open: open, close: close, closeActive: closeActive };
    })();
</script>
@endonce
