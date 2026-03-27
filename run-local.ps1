$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$phpPath = "C:\Users\humeyra.cimen\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
$phpIni = Join-Path (Split-Path $projectRoot -Parent) ".tools\php.ini"
$url = "http://127.0.0.1:8012"

Push-Location $projectRoot

npm install
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    exit $LASTEXITCODE
}

npm run build
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    exit $LASTEXITCODE
}

& $phpPath -c $phpIni artisan migrate --seed
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    exit $LASTEXITCODE
}

Write-Host ""
Write-Host "Laravel local server starting at $url"
Write-Host "Keep this terminal open while testing."
Write-Host ""

& $phpPath -c $phpIni -S 127.0.0.1:8012 -t public

Pop-Location
