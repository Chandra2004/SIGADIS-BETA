<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data SIGADIS - {{ $pregnancy->mother_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a2942; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Salinan Data Saya - SIGADIS</h1>
    <p class="muted">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>

    <h2>Data Kehamilan</h2>
    <table>
        <tr><th>Nama</th><td>{{ $pregnancy->mother_name }}</td></tr>
        <tr><th>Usia kehamilan saat daftar</th><td>{{ $pregnancy->gestational_age_weeks_at_registration }} minggu</td></tr>
        <tr><th>Perkiraan tanggal lahir</th><td>{{ $pregnancy->estimated_due_date?->translatedFormat('d F Y') ?? '-' }}</td></tr>
        <tr><th>Wilayah</th><td>{{ $pregnancy->region_code }}</td></tr>
        <tr><th>Status saat ini</th><td>{{ $pregnancy->status }}</td></tr>
    </table>

    <h2>Riwayat Skrining</h2>
    <table>
        <tr><th>Tanggal</th><th>Jenis</th><th>Hasil Risiko</th></tr>
        @forelse ($pregnancy->screeningSessions as $session)
            <tr>
                <td>{{ $session->started_at?->translatedFormat('d F Y') }}</td>
                <td>{{ $session->session_type }}</td>
                <td>{{ $session->riskAssessment?->risk_level ?? 'Belum ada hasil' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">Belum ada riwayat skrining.</td></tr>
        @endforelse
    </table>

    <h2>Riwayat Rujukan</h2>
    <table>
        <tr><th>Tanggal</th><th>Faskes Tujuan</th></tr>
        @forelse ($pregnancy->referrals as $referral)
            <tr>
                <td>{{ $referral->referred_at?->translatedFormat('d F Y') }}</td>
                <td>{{ $referral->facility?->name }}</td>
            </tr>
        @empty
            <tr><td colspan="2" class="muted">Belum ada riwayat rujukan.</td></tr>
        @endforelse
    </table>

    <h2>Riwayat Persetujuan (Consent)</h2>
    <table>
        <tr><th>Versi</th><th>Diberikan</th><th>Dicabut</th></tr>
        @foreach ($pregnancy->consents as $consent)
            <tr>
                <td>{{ $consent->consent_version }}</td>
                <td>{{ $consent->granted_at?->translatedFormat('d F Y') }}</td>
                <td>{{ $consent->revoked_at?->translatedFormat('d F Y') ?? '-' }}</td>
            </tr>
        @endforeach
    </table>

    <p class="muted" style="margin-top: 24px;">
        Dokumen ini berisi data pribadi Ibu di SIGADIS sesuai permintaan unduh data
        (Privasi &amp; Data Saya). Jaga kerahasiaan dokumen ini.
    </p>
</body>
</html>
