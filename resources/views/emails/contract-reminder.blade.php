<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder Kontrak Asuransi</title>
    <style>
        @@media only screen and (max-width: 480px) {
            .outer-pad { padding: 16px 8px !important; }
            .inner-pad { padding: 20px 16px 16px !important; }
            .header-pad { padding: 20px 16px 16px !important; }
            .body-text { font-size: 13px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height:1.6; color:#1e293b; -webkit-font-smoothing:antialiased;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f6f9; padding:32px 16px;" class="outer-pad">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#0f766e; padding:28px 32px 22px; text-align:center;" class="header-pad">
                            <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:rgba(255,255,255,0.7); margin-bottom:6px;">RS AZRA &middot; Sistem Informasi Asuransi</div>
                            <h1 style="font-size:18px; font-weight:700; color:#ffffff; margin:0; line-height:1.3;">Pengingat Kontrak Asuransi</h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px 32px 24px;" class="inner-pad">
                            @php
                                $greetings = [
                                    'admin' => 'Tim Administrasi',
                                    'legal' => 'Tim Legal',
                                    'marketing' => 'Tim Marketing',
                                    'farmasi' => 'Tim Farmasi',
                                    'kasir' => 'Tim Kasir',
                                    'pendaftaran' => 'Tim Pendaftaran',
                                ];
                                $greeting = $greetings[$purpose] ?? 'Tim Administrasi';
                            @endphp

                            <p style="font-size:14px; color:#475569; margin:0 0 20px;" class="body-text">
                                Yth. {{ $greeting }},<br><br>
                                Berikut adalah daftar kontrak asuransi yang perlu mendapat perhatian:
                            </p>

                            @if(count($expiring) > 0)
                                <h2 style="font-size:13px; font-weight:700; color:#0f766e; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.04em;">&rarr; Kontrak Akan Berakhir</h2>
                                <div style="margin:0 0 24px;">
                                    @foreach($expiring as $c)
                                        <div style="padding:6px 0; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">
                                            &bull; <strong>{{ $c['nama_asuransi'] }}</strong>
                                            @if(!empty($c['penjamin_category'])) ({{ $c['penjamin_category'] }})@endif
                                            &mdash; {{ $c['badan_usaha'] ?? '-' }}
                                            &mdash; Berakhir {{ $c['tanggal_berakhir'] }}
                                            <span style="color:#991b1b; font-weight:600;">(H-{{ $c['days_remaining'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(count($expired) > 0)
                                <h2 style="font-size:13px; font-weight:700; color:#991b1b; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.04em;">&rarr; Kontrak Sudah Berakhir</h2>
                                <div style="margin:0 0 24px;">
                                    @foreach($expired as $c)
                                        <div style="padding:6px 0; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">
                                            &bull; <strong>{{ $c['nama_asuransi'] }}</strong>
                                            @if(!empty($c['penjamin_category'])) ({{ $c['penjamin_category'] }})@endif
                                            &mdash; {{ $c['badan_usaha'] ?? '-' }}
                                            &mdash; Berakhir {{ $c['tanggal_berakhir'] }}
                                            <span style="color:#991b1b; font-weight:600;">(Expired)</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:20px 32px; text-align:center; background-color:#f8fafc; border-top:1px solid #e2e8f0;">
                            <p style="font-size:11px; color:#94a3b8; margin:0 0 4px; line-height:1.5;">Email ini dikirim otomatis oleh Sistem Informasi Asuransi RS AZRA.</p>
                            <p style="font-size:11px; color:#94a3b8; margin:0; line-height:1.5;">Tidak perlu membalas email ini. &copy; {{ date('Y') }} {{ config('app.name') }}.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
