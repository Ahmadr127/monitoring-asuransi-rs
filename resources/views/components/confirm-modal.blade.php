@props([
    'action' => '',
    'label' => 'data ini',
])

@once
    <script>
        (function() {
            if (window._confirmModalInited) return;
            window._confirmModalInited = true;

            const overlay = document.createElement('div');
            overlay.className = 'cm-overlay';
            overlay.innerHTML = `
                <div class="cm-box">
                    <div class="cm-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3>Konfirmasi Kirim</h3>
                    <p>Yakin ingin mengirim pengingat manual ke <strong id="cmName">data ini</strong>?</p>
                    <div class="cm-actions">
                        <button type="button" class="cm-btn-cancel" id="cmBtnCancel">Batal</button>
                        <form id="cmForm" method="POST" action="" class="cm-form-flex">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="cm-btn-yes cm-btn-full">Kirim</button>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-cm-trigger]');
                if (!btn) return;
                const action = btn.getAttribute('data-cm-action');
                const label = btn.getAttribute('data-cm-label') || 'data ini';
                const nameEl = document.getElementById('cmName');
                const formEl = document.getElementById('cmForm');
                if (nameEl) nameEl.textContent = label;
                if (formEl) formEl.action = action;
                overlay.classList.add('cm-active');
            });

            document.getElementById('cmBtnCancel').addEventListener('click', function() {
                overlay.classList.remove('cm-active');
            });

            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) overlay.classList.remove('cm-active');
            });
        })();
    </script>
@endonce

<button type="button" data-cm-trigger data-cm-action="{{ $action }}" data-cm-label="{{ $label }}" {{ $attributes->merge(['class' => '']) }}>
    {{ $slot }}
</button>
