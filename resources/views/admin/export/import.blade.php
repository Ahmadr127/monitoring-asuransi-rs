<x-app-layout>
    <x-slot name="title">Import Data</x-slot>

    @php
        $pageTitle = 'Import Data';
        $pageDescription = 'Upload file Excel untuk menambah data kerjasama secara massal';
    @endphp

    @push('navbar-meta')
    <script>
        (function() {
            const descEl = document.getElementById('navbarPageDesc');
            if (descEl) { descEl.textContent = '{{ $pageDescription }}'; descEl.style.display = 'block'; }
        })();
    </script>
    @endpush

    @if(session('warning'))
    <div class="alert alert-warning import-mb">{{ session('warning') }}</div>
    @endif

    @if(session('import_errors'))
    <div class="alert alert-error import-mb">
        <strong>Error details:</strong>
        <ul class="import-error-list">
            @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="import-card">
        <div class="import-flex-wrap">
            <div class="import-flex-item">
                <h3 class="import-card-title">Upload File Excel</h3>
                <p class="import-card-desc">
                    Format file: <strong>.xlsx</strong> atau <strong>.xls</strong> (maks. 5MB)
                </p>
                <form method="POST" action="{{ route('admin.export.import.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="import-mb">
                        <label class="import-label">Pilih File</label>
                        <input type="file" name="file" accept=".xlsx,.xls" required
                            class="import-file-input">
                        @error('file')
                        <span class="import-error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="import-actions">
                        <button type="submit" class="btn-primary import-btn-primary">
                            Preview Data
                        </button>
                        <a href="{{ route('admin.ruang-lingkup.index') }}" class="btn-secondary import-btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
            <div class="import-info-card">
                <h4 class="import-info-title">Petunjuk</h4>
                <ol class="import-info-list">
                    <li>Download template Excel terlebih dahulu.</li>
                    <li>Isi data sesuai format template.</li>
                    <li>1 baris = 1 kontrak. Multi BU: <code>(BU 1)(BU 2)</code> di kolom Badan Usaha.</li>
                    <li>Detail per BU: <code>(item BU1)(item BU2)</code>. Di dalam <code>()</code> gunakan koma sebagai pemisah item.</li>
                    <li>Contoh: Layanan <code>(Rawat Inap, Lab)(Rawat Jalan)</code> = BU1: Rawat Inap+Lab, BU2: Rawat Jalan.</li>
                    <li>Kolom bertanda <strong class="import-required-mark">wajib diisi</strong>: Nama Asuransi. Badan Usaha dan tanggal bersifat opsional; kontrak tanpa tanggal akan berstatus <strong>tidak_aktif</strong>.</li>
                    <li>Upload file yang sudah diisi.</li>
                </ol>
                <div class="import-mt">
                    <a href="{{ route('admin.export.template') }}" class="btn-secondary import-btn-secondary">
                        Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
