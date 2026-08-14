<x-app-layout>
    <x-slot name="title">Persetujuan Kontrak</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Antrean persetujuan berjenjang kontrak asuransi.'; d.style.display = 'block'; } })();
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

        $myStatusBadge = function ($status) {
            $map = [
                'approved' => ['badge-teal', 'SUDAH ANDA SETUJUI'],
                'pending'  => ['badge-warning', 'MENUNGGU KEPUTUSAN ANDA'],
                'skipped'  => ['badge-slate', 'DILEWATI'],
            ];
            [$cls, $label] = $map[$status] ?? ['badge-slate', '—'];
            return '<span class="badge-premium ' . $cls . '">' . $label . '</span>';
        };

        $pendingRows = $pending->map(function ($req) use ($statusBadge, $myStatusBadge) {
            $subject = $req->subject;
            $totalSteps = $req->steps_count ?? $req->steps->count();

            $myStatusLabel = $req->status !== 'in_review'
                ? str_replace('_', ' ', $req->status)
                : ($req->my_step_status ?? '-');

            $myStatusHtml = $req->status !== 'in_review'
                ? $statusBadge($req->status)
                : $myStatusBadge($req->my_step_status);

            return [
                'search' => strtolower(
                    ($subject?->nama_asuransi ?? '') . ' ' .
                    ($req->requestor?->name ?? '') . ' ' .
                    ($req->submitted_at?->format('d M Y H:i') ?? '') . ' ' .
                    $myStatusLabel
                ),
                'cells' => [
                    'nama_asuransi' => '<div class="font-bold-700 color-slate-900 dt-truncate" title="' . e($subject?->nama_asuransi ?? 'Kontrak') . '">' . e($subject?->nama_asuransi ?? 'Kontrak') . '</div>',
                    'requestor' => '<span class="text-xs-muted dt-truncate" title="' . e($req->requestor?->name ?? '-') . '">' . e($req->requestor?->name ?? '-') . '</span>',
                    'tanggal' => '<span class="text-xs-muted">' . e($req->submitted_at?->translatedFormat('d M Y H:i') ?? '-') . '</span>',
                    'step' => '<span class="badge-premium badge-slate">' . (int) $req->current_step . '/' . (int) $totalSteps . '</span>',
                    'my_status' => $myStatusHtml,
                    'status' => $statusBadge($req->status),
                ],
                'cols' => [
                    'nama_asuransi' => strtolower($subject?->nama_asuransi ?? ''),
                    'requestor' => strtolower($req->requestor?->name ?? ''),
                    'tanggal' => strtolower($req->submitted_at?->format('d M Y H:i') ?? ''),
                    'my_status' => strtolower($myStatusLabel),
                    'status' => strtolower($req->status),
                ],
                'actions' => '<a href="' . route('admin.approvals.show', $req) . '" class="btn-premium-submit w-auto">' . ($req->my_step_status === 'approved' ? 'Lihat' : 'Tinjau') . '</a>',
            ];
        })->values();
    @endphp

    <style>
        .approval-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.5rem;
            align-items: start;
            width: 100%;
            max-width: 100%;
        }
        .approval-grid > .data-table-root {
            min-width: 0;
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
        }
        .approval-grid .data-table-root .data-table-container {
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden;
            border-radius: 0;
        }
        .approval-grid .data-table-root .table-wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }
        .approval-grid .data-table-root .page-table {
            min-width: max-content;
            table-layout: fixed;
        }
        .approval-grid .data-table-root .page-table td.text-center {
            white-space: nowrap;
        }
        @media (max-width: 1100px) {
            .approval-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="premium-container approval-page">
        <div class="approval-grid">
            <x-data-table
                id="pendingTable"
                title="Menunggu Persetujuan Saya"
                subtitle="Pengajuan yang terkait dengan persetujuan Anda"
                :badge="$pending->count()"
                :global-filters="[
                    ['key' => 'status', 'all' => 'Semua status', 'options' => [
                        ['value' => 'in_review', 'text' => 'IN REVIEW'],
                        ['value' => 'approved', 'text' => 'APPROVED'],
                        ['value' => 'rejected', 'text' => 'REJECTED'],
                        ['value' => 'cancelled', 'text' => 'CANCELLED'],
                    ]],
                ]"
                :columns="[
                    ['label' => 'No', 'key' => '__no', 'center' => true, 'width' => 50],
                    ['label' => 'Kontrak', 'key' => 'nama_asuransi', 'searchable' => true, 'placeholder' => 'Cari kontrak...'],
                    ['label' => 'Pengaju', 'key' => 'requestor', 'width' => 140, 'searchable' => true, 'placeholder' => 'Cari pengaju...'],
                    ['label' => 'Tgl Pengajuan', 'key' => 'tanggal', 'width' => 150, 'searchable' => true, 'placeholder' => 'Cari tanggal...'],
                    ['label' => 'Langkah', 'key' => 'step', 'center' => true, 'width' => 80],
                    ['label' => 'Status Anda', 'key' => 'my_status', 'center' => true, 'width' => 170, 'searchable' => true, 'placeholder' => 'Cari status Anda...'],
                    ['label' => 'Status', 'key' => 'status', 'center' => true, 'width' => 110, 'searchable' => true, 'placeholder' => 'Cari status...'],
                    ['label' => 'Aksi', 'key' => '__actions', 'center' => true, 'width' => 120],
                ]"
                :rows="$pendingRows"
                empty-text="Tidak ada pengajuan yang menunggu Anda."
            >
                <x-slot name="headerAction">
                    <a href="{{ route('admin.approvals.my') }}" class="btn-secondary">
                        <x-icon name="document" class="icon-16" /> Pengajuan Saya
                    </a>
                    <a href="{{ route('admin.ruang-lingkup.create') }}" class="btn-premium-submit w-auto">
                        <x-icon name="plus-circle-solid" class="icon-18" /> Tambah Kerjasama
                    </a>
                </x-slot>
            </x-data-table>
        </div>
    </div>
</x-app-layout>