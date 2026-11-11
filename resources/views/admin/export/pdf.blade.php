<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Kerjasama</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; color: #007774; }
        .sub { text-align: center; font-size: 9px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #007774; color: white; padding: 6px 4px; text-align: left; font-size: 8px; }
        td { padding: 4px; border: 1px solid #ddd; font-size: 8px; vertical-align: top; }
        tr:nth-child(even) { background: #f9f9f9; }
        .print-footer { text-align: right; font-size: 8px; color: #999; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>Monitoring Asuransi</h2>
    <p class="sub">Daftar Kerjasama - {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Asuransi</th>
                <th>Alias</th>
                <th>High Profile</th>
                <th>TPA &amp; PAYOR</th>
                <th>PIC</th>
                <th>Badan Usaha</th>
                <th>Diskon</th>
                <th>Mulai</th>
                <th>Berakhir</th>
                <th>Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contracts as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $c->nama_asuransi }}</td>
                <td>{{ $c->alias_versi_tera ?? '-' }}</td>
                <td>{{ $c->is_high_profile ? 'Ya' : 'Tidak' }}</td>
                <td>{{ $c->penjamin_category ?? '-' }}</td>
                <td>{{ $c->pic_nama ?? '-' }}{{ $c->pic_kontak ? ' / '.$c->pic_kontak : '' }}</td>
                <td>
                    @php
                        $buNames = $c->badanUsahaPivot->map(fn($p) => $p->badanUsaha?->nama_bu)->filter();
                        if ($buNames->isEmpty() && $c->badanUsaha) $buNames = collect([$c->badanUsaha->nama_bu]);
                    @endphp
                    {{ $buNames->implode(', ') }}
                </td>
                <td>{{ $c->diskon?->first()?->persentase_diskon ? $c->diskon->first()->persentase_diskon . '%' : '-' }}</td>
                <td>{{ $c->tanggal_mulai?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $c->tanggal_berakhir?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ max(0, $c->days_remaining ?? 0) }}</td>
                <td>{{ $c->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="print-footer">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
</body>
</html>
