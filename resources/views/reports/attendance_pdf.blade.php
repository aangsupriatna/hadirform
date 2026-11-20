<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir - {{ $form->title }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .org-name {
            font-size: 14px;
            font-weight: bold;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .event-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 4px 0;
        }
        .event-meta {
            font-size: 11px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .photo-cell {
            text-align: center;
            width: 70px;
            padding: 3px;
        }
        .photo-cell img {
            max-width: 60px;
            max-height: 48px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            display: inline-block;
        }
        .sig-cell {
            text-align: center;
            width: 95px;
            height: 48px;
            padding: 2px;
        }
        .sig-cell img {
            max-width: 90px;
            max-height: 42px;
            display: inline-block;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-verified { background: #dcfce7; color: #15803d; }
        .badge-submitted { background: #e0f2fe; color: #0369a1; }
        .badge-rejected { background: #fee2e2; color: #b91c1c; }
        .footer {
            margin-top: 25px;
            width: 100%;
        }
        .footer-table {
            border: none;
            width: 100%;
        }
        .footer-table td {
            border: none;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="org-name">{{ $organization->name }}</div>
        <div class="event-title">{{ $event->title }}</div>
        <div class="event-meta">
            <strong>Formulir:</strong> {{ $form->title }} | 
            <strong>Tempat:</strong> {{ $event->venue ?? 'Online / hybrid' }} | 
            <strong>Dicetak:</strong> {{ $generated_at }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 70px;">Kode Bukti</th>
                @foreach($textFields as $tf)
                    <th>{{ $tf['label'] }}</th>
                @endforeach
                @foreach($photoFields as $pf)
                    <th style="width: 70px;" class="text-center">{{ $pf['label'] }}</th>
                @endforeach
                <th style="width: 70px;">Waktu</th>
                <th style="width: 55px;" class="text-center">Status</th>
                @if($hasAnySignature)
                    <th style="width: 95px;" class="text-center">Tanda Tangan</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td><strong style="font-family: monospace; font-size: 10px;">{{ $row['receipt_code'] }}</strong></td>
                    @foreach($textFields as $tf)
                        <td>{{ $row['answers'][$tf['key']] ?? '-' }}</td>
                    @endforeach
                    @foreach($photoFields as $pf)
                        <td class="photo-cell">
                            @if(!empty($row['photos'][$pf['key']]))
                                <img src="{{ $row['photos'][$pf['key']] }}" alt="Foto">
                            @else
                                <span style="color: #94a3b8; font-size: 8px;">(Tidak ada)</span>
                            @endif
                        </td>
                    @endforeach
                    <td style="font-size: 10px;">{{ $row['submitted_at'] }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $row['status'] }}">
                            {{ $row['status'] }}
                        </span>
                    </td>
                    @if($hasAnySignature)
                        <td class="sig-cell">
                            @if(!empty($row['signature_base64']))
                                <img src="{{ $row['signature_base64'] }}" alt="Ttd">
                            @else
                                <span style="color: #94a3b8; font-size: 8px;">(Tidak ada)</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + count($textFields) + count($photoFields) + ($hasAnySignature ? 1 : 0) }}" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Belum ada peserta yang mengisi kehadiran.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-table" style="margin-top: 30px;">
        <tr>
            <td style="width: 70%; vertical-align: bottom;">
                <p style="font-size: 10px; color: #64748b;">
                    Total Peserta: <strong>{{ count($rows) }} orang</strong><br>
                    Dokumen ini digenerate secara otomatis oleh sistem <strong>HadirForm</strong>.
                </p>
            </td>
            <td style="width: 30%; text-align: center; vertical-align: bottom;">
                <p style="font-size: 10px; margin-bottom: 50px;">
                    Mengetahui / Petugas Kegiatan,
                </p>
                <div style="border-bottom: 1px solid #334155; width: 150px; margin: 0 auto;"></div>
                <p style="font-size: 10px; color: #64748b; margin-top: 4px;">Nama & Tanda Tangan Petugas</p>
            </td>
        </tr>
    </table>
</body>
</html>
