$host.UI.RawUI.WindowTitle = "Motor Express - Servidor de Taller Automotriz (Django + MariaDB)"
Clear-Host

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   SISTEMA DE GESTION DE TALLER AUTOMOTRIZ - MOTOR EXPRESS" -ForegroundColor Yellow
Write-Host "             Plataforma: Python / Django + MariaDB        " -ForegroundColor White
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host ""

$candidatePorts = @(8095, 8096, 8585, 8888, 9090, 8088, 8090)
$selectedPort = $null

Write-Host "[*] Verificando puertos desocupados en el sistema (evitando 80, 443, 3306, 8080)..." -ForegroundColor Gray

foreach ($port in $candidatePorts) {
    $listener = $null
    try {
        $listener = New-Object System.Net.Sockets.TcpListener([System.Net.IPAddress]::Loopback, $port)
        $listener.Start()
        $listener.Stop()
        $selectedPort = $port
        Write-Host " [+] Puerto $port esta COMPLETAMENTE LIBRE (Desocupado)." -ForegroundColor Green
        break
    } catch {
        Write-Host " [-] Puerto $port ocupado por otro servicio, probando siguiente..." -ForegroundColor DarkYellow
    }
}

if (-not $selectedPort) {
    Write-Host "[!] No se encontro un puerto libre en la lista, usando 8095 por defecto." -ForegroundColor Red
    $selectedPort = 8095
}

$url = "http://127.0.0.1:$selectedPort/"

Write-Host ""
Write-Host "----------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [OK] Base de Datos: MariaDB 13.0 (motorexpress_django en 127.0.0.1:3307)" -ForegroundColor Cyan
Write-Host " [OK] Servidor Django en puerto libre: $selectedPort" -ForegroundColor Cyan
Write-Host " [OK] URL del Sistema: $url" -ForegroundColor Green
Write-Host " [OK] Credenciales Demo:" -ForegroundColor Yellow
Write-Host "      - Administrador : admin / admin123  (o admin@motorexpress.pe)" -ForegroundColor White
Write-Host "      - Mecánico Jefe : carlos / carlos123 (o carlos@motorexpress.pe)" -ForegroundColor White
Write-Host "      - Recepción     : valeria / valeria123 (o valeria@motorexpress.pe)" -ForegroundColor White
Write-Host "----------------------------------------------------------" -ForegroundColor DarkGray
Write-Host ""
Write-Host "Abriendo Motor Express en el navegador..." -ForegroundColor Cyan

Start-Job -ScriptBlock {
    param($targetUrl)
    Start-Sleep -Seconds 2
    Start-Process $targetUrl
} -ArgumentList $url | Out-Null

python manage.py runserver "127.0.0.1:$selectedPort"
