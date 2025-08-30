# Anthems - Docker Quickstart (PowerShell)
# Starts the stack, waits until it's ready, and opens the app.

$ErrorActionPreference = 'Stop'

# Ensure script directory as CWD
Set-Location -Path $PSScriptRoot

# Ensure docker-compose.yml exists here
if (-not (Test-Path 'docker-compose.yml')) {
  Write-Err "docker-compose.yml nao encontrado nesta pasta: $((Get-Location).Path)"
  Write-Info 'Mov a este script para a raiz do projeto (onde esta o docker-compose.yml) ou execute a partir da pasta correta.'
  exit 1
}

function Write-Info($msg){ Write-Host "[INFO] $msg" -ForegroundColor Cyan }
function Write-Ok($msg){ Write-Host "[OK]   $msg" -ForegroundColor Green }
function Write-Warn($msg){ Write-Host "[WARN] $msg" -ForegroundColor Yellow }
function Write-Err($msg){ Write-Host "[ERROR] $msg" -ForegroundColor Red }

# Detect compose command
$compose = $null
if (Get-Command docker-compose -ErrorAction SilentlyContinue) {
  $compose = 'docker-compose'
} elseif (docker compose version 2>$null) {
  $compose = 'docker compose'
} else {
  Write-Err 'Docker Compose not found. Install Docker Desktop or docker-compose.'
  exit 1
}

# Check Docker availability
try { docker --version *>$null } catch { Write-Err 'Docker not installed or not in PATH.'; exit 1 }

# Ensure .env exists
if (-not (Test-Path '.env')) {
  Write-Info '.env not found. Creating from .env.example ...'
  if (Test-Path '.env.example') {
    Copy-Item -Path '.env.example' -Destination '.env' -Force
  } else {
    Write-Warn '.env.example not found. Proceeding without it.'
  }
}

# Bring stack up (build if needed)
Write-Info 'Starting containers (this may take a minute)...'
& $compose up -d --build

# Determine APP URL
$envUrl = $null
try {
  $envLine = Select-String -Path '.env' -Pattern '^APP_URL=' -SimpleMatch -ErrorAction SilentlyContinue | Select-Object -First 1
  if ($envLine) { $envUrl = ($envLine.Line -replace '^APP_URL=', '').Trim() }
} catch {}
if (-not $envUrl) { $envUrl = 'http://localhost:8080' }

# Wait for web to be up (timeout ~60s)
$webUrl = $envUrl
$max = 30
$count = 0
Write-Info "Checking web at $webUrl ..."
while ($count -lt $max) {
  try {
    $resp = Invoke-WebRequest -UseBasicParsing -Uri $webUrl -Method GET -TimeoutSec 5
    if ($resp.StatusCode -eq 200) { break }
  } catch {}
  Start-Sleep -Seconds 2
  $count++
  Write-Host ("  ...waiting ({0}/{1})" -f $count, $max)
}

if ($count -lt $max) {
  Write-Ok 'Web responded with HTTP 200.'
  Write-Info "Opening: $webUrl"
  Start-Process $webUrl
  Write-Info 'Also available: phpMyAdmin at http://localhost:8081'
  Start-Process 'http://localhost:8081'
  Write-Ok 'Anthems is up.'
} else {
  Write-Warn 'Web did not return HTTP 200 in time.'
  Write-Info "You can still try opening: $webUrl"
  Start-Process $webUrl
  Write-Info 'Check logs with docker-manager.bat -> "Ver Logs"'
}
