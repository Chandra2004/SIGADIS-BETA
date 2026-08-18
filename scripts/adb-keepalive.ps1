# Skrip Otomatis ADB Keep-Alive untuk Emulator MEmu (Port 21503)
# Skrip ini memastikan koneksi ADB tetap hidup terus-menerus selama emulator aktif.

$PORT = "127.0.0.1:21503"

Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host "  SIGADIS Mobile - ADB Auto Keep-Alive Daemon       " -ForegroundColor Yellow
Write-Host "  Target Emulator Port: $PORT                       " -ForegroundColor Green
Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host "Menjaga koneksi ADB tetap hidup... (Tekan Ctrl+C untuk berhenti)" -ForegroundColor Gray

while ($true) {
    try {
        $devices = adb devices 2>$null | Out-String
        if ($devices -notmatch "$PORT\s+device") {
            Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Menghubungkan ulang ADB ke $PORT..." -ForegroundColor Yellow
            $result = adb connect $PORT 2>$null | Out-String
            if ($result -match "connected to") {
                Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Terhubung Berhasil!" -ForegroundColor Green
            }
        }
    } catch {
        # Abaikan kesalahan sesaat
    }
    Start-Sleep -Seconds 3
}
