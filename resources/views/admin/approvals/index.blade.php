<x-app-layout>
    <x-slot name="title">Persetujuan Kontrak</x-slot>
    @push('navbar-meta')
    <script>
        (function () { const d = document.getElementById('navbarPageDesc'); if (d) { d.textContent = 'Antrean persetujuan berjenjang kontrak asuransi.'; d.style.display = 'block'; } })();
    </script>
    @endpush

    <div class="premium-container">
        @if(session('success'))
            <div class="alert-premium success"><strong>{{ session('success') }}</strong></div>
        @endif
        @if(session('error'))
            <div class="alert-premium error"><strong>{{ session('error') }}</strong></div>
        @endif

        <!-- Menunggu Persetujuan Saya -->
        <div class="premium-header">
            <div class="header-left">
                <h1>Menunggu Persetujuan Saya</h1>
                <p>Kontrak yang menunggu keputusan Anda</p>
            </div>
            <span class="badge-premium badge-teal">{{ $pending->count() }} antrean</span>
        </div>

        @forelse($pending as $req)
        <div class="premium-card mb-3" style="margin-bottom:1rem">
            <div class="form-body">
                <div class="flex-between-center">
                    <div>
                        <div class="font-bold-700 color-slate-900 text-lg" style="font-size:14px">
                            {{ $req->subject?->nama_asuransi ?? 'Kontrak' }}
                        </div>
                        <div class="text-xs-muted">
                            Diajukan: {{ $req->requestor?->name ?? '-' }} &middot; {{ $req->submitted_at?->translatedFormat('d M Y H:i') }}
                            &middot; Langkah {{ $req->current_step }}/{{ $req->steps()->count() }}
                        </div>
                    </div>
                    <a href="{{ route('admin.approvals.show', $req) }}" class="btn-premium-submit w-auto">Tinjau & Putuskan</a>
                </div>
            </div>
        </div>
        @empty
        <div class="premium-card" style="margin-bottom:1.5rem">
            <div class="form-body"><p class="empty-state">Tidak ada pengajuan yang menunggu Anda.</p></div>
        </div>
        @endforelse

        <div class="content-layout" style="margin-top:1.5rem">
            <!-- Pengajuan oleh saya -->
            <div class="premium-card">
                <div class="card-header"><h2>Pengajuan Saya</h2></div>
                <div class="form-body">
                    @forelse($requestedByMe as $req)
                    <div style="padding:0.5rem 0;border-bottom:1px solid #f1f5f9">
                        <a href="{{ route('admin.approvals.show', $req) }}" class="font-bold-700 color-slate-900">{{ $req->subject?->nama_asuransi ?? '-' }}</a>
                        <div class="text-xs-muted">{{ $req->created_at->translatedFormat('d M Y H:i') }}</div>
                        <span class="badge-premium {{ $req->status === 'in_review' ? 'badge-warning' : ($req->status === 'approved' ? 'badge-teal' : 'badge-slate') }}">
                            {{ strtoupper(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </div>
                    @empty
                    <p class="empty-state">Belum ada pengajuan.</p>
                    @endforelse
                </div>
            </div>

            <!-- Riwayat keputusan saya -->
            <div class="premium-card">
                <div class="card-header"><h2>Riwayat Keputusan Saya</h2></div>
                <div class="form-body">
                    @forelse($decidedByMe as $step)
                    <div style="padding:0.5rem 0;border-bottom:1px solid #f1f5f9">
                        <div class="font-bold-700 color-slate-900">{{ $step->request?->subject?->nama_asuransi ?? '-' }}</div>
                        <div class="text-xs-muted">
                            {{ $step->title }} &middot;
                            <span class="badge-premium {{ $step->status === 'approved' ? 'badge-teal' : 'badge-danger' }}">{{ ucfirst($step->status) }}</span>
                            &middot; {{ $step->decided_at?->translatedFormat('d M Y H:i') }}
                        </div>
                    </div>
                    @empty
                    <p class="empty-state">Belum ada keputusan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>