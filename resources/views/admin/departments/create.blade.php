<x-app-layout>
    <x-slot name="title">Tambah Departemen</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Buat departemen baru.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    <div class="premium-container">
        <div class="premium-card">
            <div class="card-header flex-between-center">
                <h2>Tambah Departemen</h2>
                <a href="{{ route('admin.departments.index') }}" class="btn-secondary">
                    <x-icon name="arrow-left" class="icon-16" /> Kembali
                </a>
            </div>
            <div class="form-body">
                @include('admin.departments._form', ['unit' => null])
            </div>
        </div>
    </div>
</x-app-layout>