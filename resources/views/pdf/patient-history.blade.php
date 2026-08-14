<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat Pasien - {{ $pregnancy->mother_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a2942; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; vertical-align: top; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Riwayat Pasien - {{ $pregnancy->mother_name }}</h1>
    <p class="muted">Usia kehamilan saat daftar: {{ $pregnancy->gestational_age_weeks_at_registration }} minggu &middot; Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>

    <h2>Kunjungan Klinis</h2>
    <table>
        <tr><th>Tanggal</th><th>Jenis</th><th>Tekanan Darah</th><th>Keluhan</th><th>Catatan Bidan</th></tr>
        @forelse ($pregnancy->clinicalVisits as $visit)
            <tr>
                <td>{{ $visit->visited_at?->translatedFormat('d F Y') }}</td>
                <td>{{ $visit->status_tag }}</td>
                <td>
                    @if ($visit->blood_pressure_systolic)
                        {{ $visit->blood_pressure_systolic }}/{{ $visit->blood_pressure_diastolic }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ implode(', ', $visit->symptoms ?? []) ?: '-' }}</td>
                <td>{{ $visit->clinical_notes ?? '-' }} ({{ $visit->midwife?->full_name }})</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Belum ada catatan kunjungan.</td></tr>
        @endforelse
    </table>

    <h2>Sesi Skrining Mandiri</h2>
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

    <h2>Rujukan</h2>
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
</body>
</html>
