<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $rl->nama_asuransi }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h2 { text-align: center; color: #007774; margin-bottom: 3px; }
        .sub { text-align: center; font-size: 10px; color: #666; margin-bottom: 15px; }
        .section { margin-bottom: 15px; }
        .section-title { font-size: 12px; font-weight: bold; color: #007774; border-bottom: 2px solid #007774; padding-bottom: 3px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 6px; border: 1px solid #ddd; font-size: 10px; }
        td.label { background: #f0fdfc; font-weight: bold; width: 30%; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-aktif { background: #dcfce7; color: #166534; }
        .badge-tidak_aktif { background: #fee2e2; color: #991b1b; }
        .badge-expired { background: #fef3c7; color: #92400e; }
        .history-item { padding: 4px 0; border-bottom: 1px dashed #eee; font-size: 10px; }
        .bu-name { font-weight: bold; margin: 5px 0; }
        .print-footer { text-align: right; font-size: 8px; color: #999; margin-top: 15px; }
    </style>
</head>
<body>
    <h2>Monitoring Asuransi</h2>
    <p class="sub">Detail Kontrak Kerjasama</p>

    <div class="section">
        <div class="section-title">Informasi Dasar</div>
        <table>
            <tr><td class="label">Nama Asuransi</td><td>{{ $rl->nama_asuransi }}</td></tr>
            <tr><td class="label">Alias/Versi Tera</td><td>{{ $rl->alias_versi_tera ?? '-' }}</td></tr>
            <tr><td class="label">High Profile</td><td>{{ $rl->is_high_profile ? 'Ya' : 'Tidak' }}</td></tr>
            <tr><td class="label">TPA &amp; PAYOR</td><td>{{ $rl->penjamin_category ?? '-' }}</td></tr>
            <tr><td class="label">Status</td>
                <td>
                    @if($isExpired)
                        <span class="badge badge-expired">Expired</span>
                    @else
                        <span class="badge {{ $rl->status === 'aktif' ? 'badge-aktif' : 'badge-tidak_aktif' }}">{{ $rl->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}</span>
                    @endif
                </td>
            </tr>
            <tr><td class="label">Periode</td><td>{{ $rl->tanggal_mulai?->format('d/m/Y') ?? '-' }} s/d {{ $rl->tanggal_berakhir?->format('d/m/Y') ?? '-' }}</td></tr>
            <tr><td class="label">Sisa Waktu</td><td>{{ max(0, $daysRemaining) }} hari</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">PIC Asuransi &amp; Penagihan</div>
        <table>
            <tr><td class="label">Nama PIC</td><td>{{ $rl->pic_nama ?? '-' }}</td></tr>
            <tr><td class="label">Kontak PIC</td><td>{{ $rl->pic_kontak ?? '-' }}</td></tr>
            <tr><td class="label">Email PIC</td><td>{{ $rl->pic_email ?? '-' }}</td></tr>
            <tr><td class="label">Link Portal</td><td>{{ $rl->pic_portal ?? '-' }}</td></tr>
            <tr><td class="label">Alamat Penagihan</td><td>{{ $rl->alamat_penagihan ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Layanan & Cakupan</div>
        @php $pivots = $rl->badanUsahaPivot; @endphp
        @if($pivots->isNotEmpty())
            @foreach($pivots as $pivot)
                <p class="bu-name">{{ $pivot->badanUsaha?->nama_bu ?? 'BU Tanpa Nama' }}</p>
                <table>
                    @if(!empty($pivot->benefit))
                    <tr><td class="label">Benefit</td><td>{{ is_array($pivot->benefit) ? implode(', ', $pivot->benefit) : $pivot->benefit }}</td></tr>
                    @endif
                    @if(!empty($pivot->cakupan))
                    <tr><td class="label">Cakupan</td><td>{{ is_array($pivot->cakupan) ? implode(', ', $pivot->cakupan) : $pivot->cakupan }}</td></tr>
                    @endif
                    @if(!empty($pivot->deskripsi))
                    <tr><td class="label">Deskripsi</td><td>{{ $pivot->deskripsi }}</td></tr>
                    @endif
                </table>
            @endforeach
        @else
            <p>Tidak ada data badan usaha.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Komersial</div>
        <table>
            <tr><td class="label">Diskon</td><td>{{ $rl->diskon?->first()?->persentase_diskon ? $rl->diskon->first()->persentase_diskon . '%' : '-' }}</td></tr>
            <tr><td class="label">Jangka Waktu Bayar</td><td>{{ $rl->penagihanPembayaran?->first()?->jangka_waktu_hari ? $rl->penagihanPembayaran->first()->jangka_waktu_hari . ' hari (' . $rl->penagihanPembayaran->first()->tipe_hari . ')' : '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Riwayat Kontrak</div>
        @php $history = $rl->contractHistory; @endphp
        @if($history->isNotEmpty())
            @foreach($history as $h)
                <div class="history-item">
                    <strong>{{ $h->action_type }}</strong> — {{ $h->created_at->format('d/m/Y H:i') }}<br>
                    {{ $h->notes }}
                </div>
            @endforeach
        @else
            <p>Tidak ada riwayat.</p>
        @endif
    </div>

    <p class="print-footer">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
</body>
</html>
