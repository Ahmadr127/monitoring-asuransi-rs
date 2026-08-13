<x-app-layout>
    <x-slot name="title">Detail Pengajuan</x-slot>

    @php
        $contract = $request->subject;
        $currentPending = $request->steps->first(fn($s) => $s->status === 'pending');
        $user = auth()->user();
        $canDecide = $request->status === 'in_review' && $currentPending && $user->canApprove($currentPending);
        $isRequestor = (int) $request->requested_by === (int) $user->id;
    @endphp

    <div class="premium-container">
        <div class="flex-center-gap-2 mb-4" style="align-items:center">
            <a href="{{ route('admin.approvals.index') }}" class="btn-secondary"><x-icon name="arrow-left" class="icon-16" /> Kembali</a>
            <h1 style="margin:0">{{ $contract?->nama_asuransi ?? 'Kontrak' }}</h1>
            <span class="badge-premium {{ $request->status === 'in_review' ? 'badge-warning' : ($request->status === 'approved' ? 'badge-teal' : 'badge-slate') }}">
                {{ strtoupper(str_replace('_', ' ', $request->status)) }}
            </span>
        </div>

        <div class="content-layout">
            <div class="premium-card">
                <div class="card-header"><h2>Ringkasan Kontrak</h2></div>
                <div class="form-body">
                    @if($contract)
                    <div class="mb-2"><span class="text-xs-muted">Periode:</span> {{ $contract->tanggal_mulai->format('d M Y') }} &mdash; {{ $contract->tanggal_berakhir->format('d M Y') }}</div>
                    <div class="mb-2"><span class="text-xs-muted">Kategori:</span> {{ $contract->penjamin_category ?? '-' }}</div>
                    <div class="mb-2"><span class="text-xs-muted">High Profile:</span> {{ $contract->is_high_profile ? 'Ya' : 'Tidak' }}</div>
                    <div class="mb-2"><span class="text-xs-muted">Diajukan oleh:</span> {{ $request->requestor?->name ?? '-' }}</div>
                    @else
                    <p class="empty-state">Kontrak tidak ditemukan.</p>
                    @endif
                </div>
            </div>

            <div class="premium-card">
                <div class="card-header"><h2>Alur Persetujuan</h2></div>
                <div class="form-body">
                    <ol style="padding-left:1.25rem;margin:0">
                        @foreach($request->steps as $step)
                        <li style="margin-bottom:0.75rem">
                            <div class="font-bold-700 color-slate-900">{{ $step->title }}</div>
                            <div class="text-xs-muted">
                                Penilai: {{ $step->approver?->name ?? 'Anggota departemen' }}
                                &middot;
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
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>

        @if($request->status === 'in_review' && $currentPending)
        <div class="premium-card" style="margin-top:1.5rem">
            <div class="card-header"><h2>Keputusan pada: {{ $currentPending->title }}</h2></div>
            <div class="form-body">
                @if($canDecide)
                    <div class="content-layout">
                        <form method="POST" action="{{ route('admin.approvals.approve', $request) }}">
                            @csrf
                            <div class="form-group">
                                <label>Catatan (opsional)</label>
                                <input type="text" name="note" class="form-input-minimal" placeholder="Catatan persetujuan">
                            </div>
                            <button type="submit" class="btn-premium-submit w-100"><x-icon name="check" class="icon-18" /> Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.approvals.reject', $request) }}">
                            @csrf
                            <div class="form-group">
                                <label>Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="note" class="form-input-minimal" rows="2" required placeholder="Wajib diisi saat menolak"></textarea>
                            </div>
                            <button type="submit" class="btn-secondary w-100"><x-icon name="close" class="icon-18" /> Tolak</button>
                        </form>
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
</x-app-layout>