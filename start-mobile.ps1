# CRH Blood Bank - API + Expo from project root (same folder as Laravel)
Set-Location $PSScriptRoot

if (-not (Test-Path ".\app.json")) {
    Write-Host "Missing app.json - Expo app should live in BLOODBANK root." -ForegroundColor Red
    exit 1
}

if (-not (Test-Path ".\node_modules\expo")) {
    Write-Host "Installing npm dependencies (first time)..." -ForegroundColor Yellow
    npm install
}

function Test-ListenPort {
    param([int]$Port)
    return [bool](Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue)
}

$defaultRoute = Get-NetRoute -DestinationPrefix '0.0.0.0/0' -AddressFamily IPv4 |
    Sort-Object RouteMetric, InterfaceMetric |
    Select-Object -First 1
if (-not $defaultRoute) {
    Write-Host "Could not find an active network connection." -ForegroundColor Red
    exit 1
}

$lanIp = Get-NetIPAddress -AddressFamily IPv4 -InterfaceIndex $defaultRoute.InterfaceIndex |
    Where-Object { $_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.254.*' } |
    Select-Object -First 1 -ExpandProperty IPAddress

if (-not $lanIp) {
    Write-Host "Could not determine the computer LAN IPv4 address." -ForegroundColor Red
    exit 1
}

$apiPort = 8000
while (Test-ListenPort -Port $apiPort) {
    $apiPort++
}

$expoPort = 8081
while (Test-ListenPort -Port $expoPort) {
    $expoPort++
}

$rootPath = $PSScriptRoot
$apiUrl = "http://${lanIp}:${apiPort}/api"

Write-Host "Opening API at ${apiUrl}..." -ForegroundColor Green
Start-Process -FilePath "powershell.exe" -WorkingDirectory $rootPath -ArgumentList @(
    "-NoExit",
    "-Command",
    "php artisan serve --host=0.0.0.0 --port=${apiPort}"
)

Start-Sleep -Seconds 2

$env:EXPO_PUBLIC_API_URL = $apiUrl
Write-Host "Opening Expo window on port ${expoPort}..." -ForegroundColor Cyan
Start-Process -FilePath "powershell.exe" -WorkingDirectory $rootPath -ArgumentList @(
    "-NoExit",
    "-Command",
    "npx expo start --lan --port ${expoPort}"
)

Write-Host ""
Write-Host "Expo menu: press W for web or scan the QR code with Expo Go on your phone." -ForegroundColor Yellow
Write-Host "Use the same Wi-Fi on both devices and allow PHP through Windows Firewall on Private networks if prompted." -ForegroundColor Yellow
