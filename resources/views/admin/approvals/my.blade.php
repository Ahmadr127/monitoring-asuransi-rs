<x-app-layout>
    <x-slot name="title">Pengajuan Saya</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Daftar pengajuan kontrak yang pernah Anda buat.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    @php
        $statusBadge = function ($status) {
            $map = [
                'in_review' => ['badge-warning', 'IN REVIEW'],
                'approved'  => ['badge-teal', 'APPROVED'],
                'rejected'  => ['badge-danger', 'REJECTED'],
                'cancelled' => ['badge-slate', 'CANCELLED'],
            ];
            [$cls, $label] = $map[$status] ?? ['badge-slate', strtoupper(str_replace('_', ' ', $status))];
            return '<span class="badge-premium ' . $cls . '">' . $label . '</span>';
        };

        $myRows = $requestedByMe->map(function ($req) use ($statusBadge) {
            $subject = $req->subject;
            $totalSteps = $req->steps_count ?? $req->steps->count();
            $isRunning = $req->status === 'in_review';

            return [
                'search' => strtolower(
                    ($subject?->nama_asuransi ?? '') . ' ' .
                    ($req->submitted_at?->format('d M Y H:i') ?? '') . ' ' .
                    str_replace('_', ' ', $req->status)
                ),
                'cells' => [
                    'nama_asuransi' => '<div class="font-bold-700 color-slate-900 dt-truncate" title="' . e($subject?->nama_asuransi ?? 'Kontrak') . '">' . e($subject?->nama_asuransi ?? 'Kontrak') . '</div>',
                    'tanggal' => '<span class="text-xs-muted">' . e($req->submitted_at?->translatedFormat('d M Y H:i') ?? '-') . '</span>',
                    'step' => '<span class="badge-premium badge-slate">' . (int) $req->current_step . '/' . (int) $totalSteps . '</span>',
                    'status' => $isRunning
                        ? '<span class="badge-premium badge-warning">SEDANG PROSES</span>'
                        : $statusBadge($req->status),
                ],
                'cols' => [
                    'nama_asuransi' => strtolower($subject?->nama_asuransi ?? ''),
                    'tanggal' => strtolower($req->submitted_at?->format('d M Y H:i') ?? ''),
                    'status' => strtolower($req->status),
                ],
                'actions' => '<a href="' . route('admin.approvals.show', $req) . '" class="btn-premium-submit w-auto">Lihat</a>',
            ];
        })->values();
    @endphp

    <div class="premium-container approval-page">
        <x-data-table
            id="myRequestsTable"
            title="Pengajuan Saya"
            subtitle="Pengajuan kontrak yang pernah Anda buat"
            :badge="$requestedByMe->count()"
            :columns="[
                ['label' => 'No', 'key' => '__no', 'center' => true, 'width' => 50],
                ['label' => 'Kontrak', 'key' => 'nama_asuransi', 'searchable' => true, 'placeholder' => 'Cari kontrak...'],
                ['label' => 'Tgl Pengajuan', 'key' => 'tanggal', 'width' => 150, 'searchable' => true, 'placeholder' => 'Cari tanggal...'],
                ['label' => 'Progress', 'key' => 'step', 'center' => true, 'width' => 90],
                ['label' => 'Status', 'key' => 'status', 'center' => true, 'width' => 150, 'searchable' => true, 'placeholder' => 'Cari status...'],
                ['label' => 'Aksi', 'key' => '__actions', 'center' => true, 'width' => 100],
            ]"
            :rows="$myRows"
            empty-text="Belum ada pengajuan."
        >
            <x-slot name="headerAction">
                <a href="{{ route('admin.approvals.index') }}" class="btn-secondary">
                    <x-icon name="arrow-left" class="icon-16" /> Persetujuan
                </a>
                <a href="{{ route('admin.ruang-lingkup.create') }}" class="btn-premium-submit w-auto">
                    <x-icon name="plus-circle-solid" class="icon-18" /> Tambah Kerjasama
                </a>
            </x-slot>
        </x-data-table>
    </div>
</x-app-layout>