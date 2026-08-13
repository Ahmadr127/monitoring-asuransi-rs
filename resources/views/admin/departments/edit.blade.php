<x-app-layout>
    <x-slot name="title">Edit Departemen - {{ $unit->name }}</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Ubah data departemen.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    <div class="premium-container">
        <div class="premium-card">
            <div class="card-header flex-between-center">
                <h2>Edit Departemen: {{ $unit->name }}</h2>
                <a href="{{ route('admin.departments.show', $unit) }}" class="btn-secondary">
                    <x-icon name="arrow-left" class="icon-16" /> Kembali
                </a>
            </div>
            <div class="form-body">
                @include('admin.departments._form', ['unit' => $unit])
            </div>
        </div>
    </div>
</x-app-layout>