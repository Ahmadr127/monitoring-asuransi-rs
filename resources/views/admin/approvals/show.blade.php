<x-app-layout>
    <x-slot name="title">Detail Pengajuan</x-slot>

    @php
        $contract = $request->subject;
        $currentPending = $request->steps->first(fn($s) => $s->status === 'pending');
        $user = auth()->user();
        $canDecide = $request->status === 'in_review' && $currentPending && $user->canApprove($currentPending);
        $isRequestor = (int) $request->requested_by === (int) $user->id;
        $showDecision = $request->status === 'in_review' && $currentPending;

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

        $summaryItems = [];
        if ($contract) {
            $jenisLayanan = is_array($contract->jenis_layanan)
                ? implode(', ', array_map('ucwords', $contract->jenis_layanan))
                : ($contract->jenis_layanan ?? '-');
            $summaryItems = [
                'Kontrak' => e($contract->nama_asuransi),
                'Badan Usaha' => e($contract->badanUsaha?->name ?? '-'),
                'Jenis Layanan' => e($jenisLayanan),
                'Periode' => e($contract->tanggal_mulai->format('d M Y')) . ' &mdash; ' . e($contract->tanggal_berakhir->format('d M Y')),
                'Kategori' => e($contract->penjamin_category ?? '-'),
                'Alias / Versi' => e($contract->alias_versi_tera ?? '-'),
                'High Profile' => $contract->is_high_profile ? 'Ya' : 'Tidak',
            ];
        }
    @endphp

    <style>
        .approval-show-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1.5rem;
            align-items: stretch;
            width: 100%;
            max-width: 100%;
        }
        .approval-show-grid > * {
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
        }
        .approval-show-grid > .premium-card {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 210px);
            min-height: 380px;
        }
        .approval-show-grid > .premium-card .card-header {
            flex: none;
        }
        .approval-show-grid > .premium-card .form-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
        }
        @media (max-width: 1024px) {
            .approval-show-grid {
                grid-template-columns: 1fr;
            }
            .approval-show-grid > .premium-card {
                height: auto;
                min-height: 0;
            }
        }

        .detail-list {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }
        .detail-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }
        .detail-value {
            font-size: 13px;
            color: #1e293b;
        }

        .decision-field label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            margin-bottom: 0.4rem;
        }
        .decision-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .decision-actions .btn-premium-submit,
        .decision-actions .btn-secondary {
            justify-content: center;
        }

        .approval-timeline {
            position: relative;
            margin-top: 0.5rem;
        }
        .timeline-step {
            position: relative;
            display: flex;
            gap: 1rem;
            padding-bottom: 1.5rem;
        }
        .timeline-step:last-child {
            padding-bottom: 0;
        }
        .timeline-step::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 36px;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-step:last-child::before {
            display: none;
        }
        .timeline-marker {
            flex: none;
            z-index: 1;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }
        .timeline-step.approved .timeline-marker {
            border-color: var(--success);
            color: var(--success);
            background: var(--success-bg);
        }
        .timeline-step.rejected .timeline-marker {
            border-color: var(--danger);
            color: var(--danger);
            background: var(--danger-bg);
        }
        .timeline-step.pending .timeline-marker {
            border-color: var(--warning);
            color: var(--warning);
            background: var(--warning-bg);
        }
        .timeline-step.skipped .timeline-marker,
        .timeline-step.cancelled .timeline-marker {
            border-color: var(--neutral-border);
            color: var(--neutral);
            background: var(--neutral-bg);
        }
        .timeline-content {
            flex: 1;
            min-width: 0;
        }
        .timeline-title {
            font-weight: 700;
            color: #1e293b;
            font-size: 13px;
        }
        .timeline-meta {
            margin-top: 0.35rem;
        }
        .password-hint.invalid {
            color: #ef4444;
        }
    </style>

    <div class="premium-container">
        <div class="flex-center-gap-2 mb-4" style="align-items:center;flex-wrap:wrap">
            <a href="{{ route('admin.approvals.index') }}" class="btn-secondary"><x-icon name="arrow-left" class="icon-16" /> Kembali</a>
            <h1 style="margin:0">{{ $contract?->nama_asuransi ?? 'Kontrak' }}</h1>
            <span class="badge-premium {{ $request->status === 'in_review' ? 'badge-warning' : ($request->status === 'approved' ? 'badge-teal' : 'badge-slate') }}">
                {{ strtoupper(str_replace('_', ' ', $request->status)) }}
            </span>
        </div>

        <div class="approval-show-grid">
            <div class="premium-card">
                <div class="card-header flex-between-center">
                    <h2>Ringkasan Kontrak</h2>
                    @if($contract)
                    <a href="{{ route('user.kerjasama.show', $contract) }}" target="_blank" rel="noopener" class="btn-secondary">
                        <x-icon name="eye" class="icon-16" /> Lihat Detail Kontrak
                    </a>
                    @endif
                </div>
                <div class="form-body">
                    @if($contract)
                    <div class="detail-list">
                        @foreach($summaryItems as $label => $value)
                        <div class="detail-item">
                            <span class="detail-label">{{ $label }}</span>
                            <span class="detail-value">{!! $value !!}</span>
                        </div>
                        @endforeach
                        <div class="detail-item">
                            <span class="detail-label">Tanggal Pengajuan</span>
                            <span class="detail-value">{{ $request->submitted_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Pengaju</span>
                            <span class="detail-value">{{ $request->requestor?->name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">{!! $statusBadge($request->status) !!}</span>
                        </div>
                    </div>
                    @else
                    <p class="empty-state" style="margin:0">Kontrak tidak ditemukan.</p>
                    @endif
                </div>
            </div>

            @if($showDecision)
            <div class="premium-card decision-card">
                <div class="card-header"><h2>Keputusan pada: {{ $currentPending->title }}</h2></div>
                <div class="form-body">
                    @if($canDecide)
                        <div class="decision-field">
                            <label id="decisionLabel">Catatan Keputusan <span class="text-xs-muted">(Opsional)</span></label>
                            <textarea id="decisionNote" class="form-input-minimal" rows="3" placeholder="Tambahkan catatan keputusan..."></textarea>
                            <span class="password-hint" id="decisionHint"></span>
                        </div>

                        <form id="approveForm" method="POST" action="{{ route('admin.approvals.approve', $request) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="catatan_keputusan" id="approveNote">
                            @if($currentPending->requires_attachment)
                            <div class="form-group" style="margin-top:1rem">
                                <label>Lampiran PDF <span class="text-danger">*</span></label>
                                <input type="file" name="attachment" accept=".pdf,application/pdf" class="form-input-minimal" required>
                                <span class="password-hint">Hanya file PDF yang diizinkan (maks 5MB).</span>
                            </div>
                            @endif
                        </form>
                        <form id="rejectForm" method="POST" action="{{ route('admin.approvals.reject', $request) }}">
                            @csrf
                            <input type="hidden" name="catatan_keputusan" id="rejectNote">
                        </form>

                        <div class="decision-actions">
                            <button type="button" class="btn-premium-submit" onclick="submitDecision('approve')">
                                <x-icon name="check" class="icon-18" /> Setujui
                            </button>
                            <button type="button" class="btn-secondary" onclick="submitDecision('reject')">
                                <x-icon name="close" class="icon-18" /> Tolak
                            </button>
                        </div>
                    @else
                        <p class="text-xs-muted">Menunggu keputusan penilai lain pada langkah ini.</p>
                    @endif

                    @if($isRequestor)
                    <form method="POST" action="{{ route('admin.approvals.cancel', $request) }}" style="margin-top:1rem">
                        @csrf
                        <button type="submit" class="btn-secondary" onclick="return confirm('Batalkan pengajuan ini?')">Batalkan Pengajuan</button>
                    </form>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="premium-card flow-card" style="margin-top:1.5rem">
            <div class="card-header"><h2>Alur Persetujuan</h2></div>
                <div class="form-body">
                    <div class="approval-timeline">
                        @foreach($request->steps as $step)
                        <div class="timeline-step {{ $step->status }}">
                            <div class="timeline-marker"><span>{{ $step->step_order }}</span></div>
                            <div class="timeline-content">
                                <div class="timeline-title">{{ $step->title }}
                                    @if($step->status === 'pending' && $step->requires_attachment)
                                        <span class="text-xs-muted">(wajib lampiran PDF)</span>
                                    @endif
                                </div>
                                <div class="text-xs-muted">Penilai: {{ $step->approver?->name ?? 'Anggota departemen' }}</div>
                                <div class="timeline-meta">
                                    @if($step->status === 'pending')
                                        <span class="badge-premium badge-warning">Menunggu</span>
                                    @elseif($step->status === 'approved')
                                        <span class="badge-premium badge-teal">Disetujui</span>
                                        {{ $step->decidedBy?->name }} &middot; {{ $step->decided_at?->translatedFormat('d M Y H:i') }}
                                    @elseif($step->status === 'rejected')
                                        <span class="badge-premium badge-danger">Ditolak</span>
                                        {{ $step->decidedBy?->name }} &middot; {{ $step->decided_at?->translatedFormat('d M Y H:i') }}
                                    @else
                                        <span class="badge-premium badge-slate">{{ ucfirst($step->status) }}</span>
                                    @endif
                                </div>
                                @if($step->note)
                                    <div class="text-xs-muted" style="color:#475569">Catatan: {{ $step->note }}</div>
                                @endif
                                @if($step->attachment_path)
                                    <div class="text-xs-muted" style="color:#007774">
                                        <a href="{{ asset('storage/' . $step->attachment_path) }}" target="_blank" rel="noopener">
                                            <x-icon name="document" class="icon-14" /> {{ $step->attachment_original_name ?? 'Lampiran PDF' }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    <script>
        window.submitDecision = function (decision) {
            const textarea = document.getElementById('decisionNote');
            const label = document.getElementById('decisionLabel');
            const hint = document.getElementById('decisionHint');
            const note = textarea.value.trim();

            if (decision === 'reject') {
                label.innerHTML = 'Alasan Penolakan <span class="text-danger">*</span>';
                textarea.placeholder = 'Wajib diisi alasan penolakan...';
                if (!note) {
                    hint.className = 'password-hint invalid';
                    hint.textContent = 'Alasan penolakan wajib diisi.';
                    textarea.focus();
                    return;
                }
                hint.className = 'password-hint';
                hint.textContent = '';
                document.getElementById('rejectNote').value = note;
                document.getElementById('rejectForm').submit();
                return;
            }

            label.innerHTML = 'Catatan Keputusan <span class="text-xs-muted">(Opsional)</span>';
            textarea.placeholder = 'Tambahkan catatan keputusan...';
            hint.className = 'password-hint';
            hint.textContent = '';
            document.getElementById('approveNote').value = note;
            document.getElementById('approveForm').submit();
        };
    </script>
</x-app-layout>