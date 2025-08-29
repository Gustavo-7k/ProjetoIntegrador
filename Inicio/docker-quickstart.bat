@echo off
setlocal EnableExtensions DisableDelayedExpansion

REM Anthems - Docker Quickstart (one-shot)
REM Starts the stack, waits until it's ready, and opens the app.

title Anthems - Docker Quickstart

REM Ensure script directory as CWD
pushd "%~dp0"

REM Detect compose command (v1 or v2)
set "COMPOSE=docker-compose"
%COMPOSE% version > nul 2>&1
if not %errorlevel%==0 (
    docker compose version > nul 2>&1
    if %errorlevel%==0 (
        set "COMPOSE=docker compose"
    )
)

REM Check Docker availability
docker --version > nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not installed or not in PATH.
    echo Install Docker Desktop: https://www.docker.com/products/docker-desktop
    goto :end
)

%COMPOSE% version > nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker Compose not detected.
    echo Use docker-compose v1 or Docker Desktop (docker compose v2).
    goto :end
)

REM Ensure .env exists
if not exist ".env" (
    echo [INFO] .env not found. Creating from .env.example ...
    if exist ".env.example" (
        copy /y .env.example .env > nul
    ) else (
        echo [WARN] .env.example not found. Proceeding without it.
    )
)

REM Bring stack up (build if needed)
echo [START] Starting containers (this may take a minute)...
%COMPOSE% up -d --build
if %errorlevel% neq 0 (
    echo [ERROR] Failed to start containers.
    goto :end
)

REM Determine APP URL
set "APP_URL_DISPLAY=http://localhost:8080"
for /f "usebackq tokens=2 delims==" %%A in (`findstr /B /C:"APP_URL=" .env 2^>nul`) do set "APP_URL_DISPLAY=%%A"

REM Wait for web to be up (timeout ~60s)
set "WEB_URL=%APP_URL_DISPLAY%"
set "WAIT_MAX=30"
set /a "WAIT_COUNT=0"

echo [WAIT] Checking web at %WEB_URL% ...
:wait_loop
set "HTTP_CODE="
where curl > nul 2>&1
if %errorlevel%==0 (
    for /f "delims=" %%H in ('curl -s -o nul -w "%%{http_code}" %WEB_URL% 2^>nul') do set "HTTP_CODE=%%H"
) else (
    for /f "delims=" %%H in ('powershell -NoProfile -Command "try{(Invoke-WebRequest -UseBasicParsing '%WEB_URL%').StatusCode}catch{0}"') do set "HTTP_CODE=%%H"
)

if "%HTTP_CODE%"=="200" goto ready
set /a "WAIT_COUNT+=1"
if %WAIT_COUNT% GEQ %WAIT_MAX% goto not_ready
>nul timeout /t 2
echo   ...waiting (%WAIT_COUNT%/%WAIT_MAX%)
goto wait_loop

:ready
echo [OK] Web responded with HTTP 200.
echo Opening: %WEB_URL%
start %WEB_URL%
echo Also available: phpMyAdmin at http://localhost:8081
start http://localhost:8081
echo(
echo [DONE] Anthems is up.
goto end

:not_ready
echo [WARN] Web did not return HTTP 200 in time.
echo You can still try opening: %WEB_URL%
start %WEB_URL%
echo Check logs with: docker-manager.bat -> "Ver Logs"

:end
popd
echo(
pause
exit /b
