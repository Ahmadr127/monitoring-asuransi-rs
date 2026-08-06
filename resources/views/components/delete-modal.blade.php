@props([
    'action' => '',
    'label' => 'data ini',
])

@once
    <script>
        (function() {
            if (window._deleteModalInited) return;
            window._deleteModalInited = true;



            const overlay = document.createElement('div');
            overlay.className = 'dm-overlay';
            overlay.innerHTML = `
                <div class="dm-box">
                    <div class="dm-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7M10 11v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3>Konfirmasi Hapus</h3>
                    <p>Yakin ingin menghapus <strong id="dmName">data ini</strong>? Tindakan ini tidak bisa dibatalkan.</p>
                    <div class="dm-actions">
                        <button type="button" class="dm-btn-cancel" id="dmBtnCancel">Batal</button>
                        <form id="dmForm" method="POST" action="" class="dm-form-flex">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="dm-btn-yes dm-btn-full">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-dm-trigger]');
                if (!btn) return;
                const action = btn.getAttribute('data-dm-action');
                const label = btn.getAttribute('data-dm-label') || 'data ini';
                const nameEl = document.getElementById('dmName');
                const formEl = document.getElementById('dmForm');
                if (nameEl) nameEl.textContent = label;
                if (formEl) formEl.action = action;
                overlay.classList.add('dm-active');
            });

            document.getElementById('dmBtnCancel').addEventListener('click', function() {
                overlay.classList.remove('dm-active');
            });

            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) overlay.classList.remove('dm-active');
            });
        })();
    </script>
@endonce

<button type="button" data-dm-trigger data-dm-action="{{ $action }}" data-dm-label="{{ $label }}" {{ $attributes->merge(['class' => '']) }}>
    {{ $slot }}
</button>
