@echo off
setlocal EnableExtensions DisableDelayedExpansion
REM chcp 65001 > nul

:: Script de Gerenciamento Docker para Anthems
:: Autor: Sistema Anthems
:: Data: %date%

title Anthems - Gerenciador Docker

:: Garante que ao abrir por duplo-clique a janela permaneça aberta
if /i not "%~1"=="-wrapped" (
    start "Anthems - Gerenciador Docker" cmd /k "%~f0" -wrapped
    goto :eof
)

:: Garantir que o diretório atual seja o do script
pushd "%~dp0"

:: Detectar comando do Compose (docker-compose v1 ou docker compose v2)
set "COMPOSE=docker-compose"
%COMPOSE% version > nul 2>&1
if not %errorlevel%==0 (
    docker compose version > nul 2>&1
    if %errorlevel%==0 (
        set "COMPOSE=docker compose"
    )
)

:: Cores para output
REM Cores desativadas para compatibilidade de console

:: Banner
cls
echo ============================================================
echo             Anthems - Gerenciador Docker v1.0
echo ============================================================
echo(

:: Verificar se Docker/Compose estão instalados
:check_docker
docker --version > nul 2>&1
if %errorlevel% neq 0 (
    echo [ERRO] Docker não está instalado ou não está no PATH!
    echo(
    echo Por favor, instale o Docker Desktop primeiro:
    echo https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

%COMPOSE% version > nul 2>&1
if %errorlevel% neq 0 (
    echo [ERRO] Docker Compose nao foi detectado!
    echo Tente instalar o docker-compose v1 ou use o Docker Desktop (docker compose v2).
    echo(
    echo Pressione qualquer tecla para sair...
    pause > nul
    exit /b 1
)

echo [OK] Docker e Compose detectados (%COMPOSE%)
echo(

:: Menu Principal
:main_menu
echo ============================================================
echo                     MENU PRINCIPAL
echo ------------------------------------------------------------
echo  1. Iniciar Aplicacao
echo  2. Parar Aplicacao
echo  3. Reiniciar Aplicacao
echo  4. Rebuild Completo
echo  5. Ver Status dos Containers
echo  6. Ver Logs
echo  7. Backup do Banco de Dados
echo  8. Restaurar Backup
echo  9. Limpar Sistema Docker
echo 10. Abrir Aplicacao no Browser
echo 11. Abrir phpMyAdmin
echo 12. Configuracoes Avancadas
echo 13. Health Check
echo  0. Sair
echo ============================================================
echo(

set /p choice=Digite sua opcao [0-13]: 

if "%choice%"=="1" goto start_app
if "%choice%"=="2" goto stop_app
if "%choice%"=="3" goto restart_app
if "%choice%"=="4" goto rebuild_app
if "%choice%"=="5" goto status
if "%choice%"=="6" goto logs
if "%choice%"=="7" goto backup
if "%choice%"=="8" goto restore
if "%choice%"=="9" goto cleanup
if "%choice%"=="10" goto open_app
if "%choice%"=="11" goto open_phpmyadmin
if "%choice%"=="12" goto advanced_menu
if "%choice%"=="13" goto health_check
if "%choice%"=="0" goto exit_script

echo Opcao invalida! Tente novamente.
timeout /t 2 > nul
goto main_menu

:: Health Check
:health_check
cls
echo [HEALTH] Verificando saude dos servicos...
echo(

:: Verificar web (porta 8080)
set "WEB_URL=http://localhost:8080"
set "PMA_URL=http://localhost:8081"

where curl > nul 2>&1
if %errorlevel%==0 (
    for /f "delims=" %%H in ('curl -s -o nul -w "%%{http_code}" %WEB_URL% 2^>nul') do set "WEB_CODE=%%H"
) else (
    for /f "delims=" %%H in ('powershell -NoProfile -Command "try{(Invoke-WebRequest -UseBasicParsing '%WEB_URL%').StatusCode}catch{0}"') do set "WEB_CODE=%%H"
)
if "%WEB_CODE%"=="200" (
    echo [OK] Web: %WEB_URL% (200)
) else (
    echo [FALHA] Web: %WEB_URL% (codigo %WEB_CODE%)
)

:: Verificar phpMyAdmin (porta 8081)
where curl > nul 2>&1
if %errorlevel%==0 (
    for /f "delims=" %%H in ('curl -s -o nul -w "%%{http_code}" %PMA_URL% 2^>nul') do set "PMA_CODE=%%H"
) else (
    for /f "delims=" %%H in ('powershell -NoProfile -Command "try{(Invoke-WebRequest -UseBasicParsing '%PMA_URL%').StatusCode}catch{0}"') do set "PMA_CODE=%%H"
)
if "%PMA_CODE%"=="200" (
    echo [OK] phpMyAdmin: %PMA_URL% (200)
) else (
    echo [AVISO] phpMyAdmin: %PMA_URL% (codigo %PMA_CODE%)
)

echo(
:: Verificar DB container com mysqladmin ping
%COMPOSE% exec -T anthems-db sh -lc "mysqladmin ping -uroot -p$MYSQL_ROOT_PASSWORD --silent" > nul 2>&1
if %errorlevel%==0 (
    echo [OK] MySQL: mysqld is alive
) else (
    echo [FALHA] MySQL: nao respondeu ao ping
)

:: Verificar Redis
%COMPOSE% exec -T anthems-redis sh -lc "redis-cli ping" > tmp_redis_check.txt 2>&1
set "REDIS_OUT="
for /f "usebackq delims=" %%R in ("tmp_redis_check.txt") do set "REDIS_OUT=%%R"
del /q tmp_redis_check.txt > nul 2>&1
echo %REDIS_OUT% | find /i "PONG" > nul
if %errorlevel%==0 (
    echo [OK] Redis: PONG
) else (
    echo [AVISO] Redis: indisponivel
)

:: Verificar conexao PHP -> DB
%COMPOSE% exec -T anthems-web php -r "require 'config.php'; try{ new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_TIMEOUT=>2]); echo 'OK'; }catch(Exception $e){ echo 'FAIL'; exit(1);} " > nul 2>&1
if %errorlevel%==0 (
    echo [OK] PHP -> DB: conexao estabelecida
) else (
    echo [FALHA] PHP -> DB: nao conectou
)

echo(
echo Pressione qualquer tecla para voltar ao menu...
pause > nul
goto main_menu

:: Iniciar Aplicacao
:start_app
cls
echo [INICIANDO] Subindo containers da aplicacao...
echo(

:: Verificar se .env existe
if not exist ".env" (
    echo [AVISO] Arquivo .env nao encontrado. Criando a partir do template...
    copy .env.example .env > nul
    echo Por favor, edite o arquivo .env com suas configuracoes antes de continuar.
    echo(
    pause
)

%COMPOSE% up -d
if %errorlevel% equ 0 (
    echo(
    echo [SUCESSO] Aplicacao iniciada com sucesso!
    echo(
    setlocal EnableDelayedExpansion
    set "APP_URL_DISPLAY=http://localhost:8080"
    for /f "usebackq tokens=2 delims==" %%A in (`findstr /B /C:"APP_URL=" .env 2^>nul`) do set "APP_URL_DISPLAY=%%A"
    echo Acessos disponiveis:
    echo - Aplicacao: !APP_URL_DISPLAY!
    echo - phpMyAdmin: http://localhost:8081
    endlocal
    echo(
    echo Aguardando containers ficarem prontos...
    timeout /t 10 > nul
    echo Aplicacao pronta para uso!
) else (
    echo [ERRO] Falha ao iniciar a aplicacao!
    echo Use a opcao "Ver Logs" para diagnosticar o problema.
)
echo(
pause
goto main_menu

:: Parar Aplicacao
:stop_app
cls
echo [PARANDO] Parando containers...
echo(

%COMPOSE% down
if %errorlevel% equ 0 (
    echo [SUCESSO] Aplicacao parada com sucesso!
) else (
    echo [ERRO] Erro ao parar a aplicacao!
)
echo(
pause
goto main_menu

:: Reiniciar Aplicacao
:restart_app
cls
echo [REINICIANDO] Reiniciando aplicacao...
echo(

echo Parando containers...
%COMPOSE% down
echo(
echo Iniciando containers...
%COMPOSE% up -d

if %errorlevel% equ 0 (
    echo(
    echo [SUCESSO] Aplicacao reiniciada com sucesso!
) else (
    echo [ERRO] Erro ao reiniciar a aplicacao!
)
echo(
pause
goto main_menu

:: Rebuild Completo
:rebuild_app
cls
echo [REBUILD] Fazendo rebuild completo da aplicacao...
echo(

echo ATENCAO: Este processo ira:
echo - Parar todos os containers
echo - Remover containers existentes
echo - Rebuild das imagens (pode demorar)
echo - Reiniciar a aplicacao
echo(

set /p confirm=Deseja continuar? [s/N]: 
if /i not "%confirm%"=="s" (
    echo Operacao cancelada.
    pause
    goto main_menu
)

echo(
echo Parando e removendo containers...
%COMPOSE% down --volumes --remove-orphans

echo(
echo Fazendo rebuild das imagens...
%COMPOSE% build --no-cache --pull

echo(
echo Iniciando aplicacao...
%COMPOSE% up -d

if %errorlevel% equ 0 (
    echo(
    echo [SUCESSO] Rebuild concluido com sucesso!
) else (
    echo [ERRO] Erro durante o rebuild!
)
echo(
pause
goto main_menu

:: Ver Status
:status
cls
echo [STATUS] Status dos containers:
echo(

%COMPOSE% ps
echo(

echo Informacoes do sistema:
docker system df
echo(

echo Uso de recursos:
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}"
echo(

pause
goto main_menu

:: Ver Logs
:logs
cls
echo [LOGS] Selecione o servico:
echo(
echo 1. Todos os servicos
echo 2. anthems-web (Apache/PHP)
echo 3. anthems-db (MySQL)
echo 4. anthems-phpmyadmin
echo 5. anthems-redis
echo 0. Voltar
echo(

set /p log_choice=Digite sua opcao [0-5]: 

if "%log_choice%"=="1" %COMPOSE% logs --tail=50 -f
if "%log_choice%"=="2" %COMPOSE% logs anthems-web --tail=50 -f
if "%log_choice%"=="3" %COMPOSE% logs anthems-db --tail=50 -f
if "%log_choice%"=="4" %COMPOSE% logs anthems-phpmyadmin --tail=50 -f
if "%log_choice%"=="5" %COMPOSE% logs anthems-redis --tail=50 -f
if "%log_choice%"=="0" goto main_menu

if not "%log_choice%"=="0" (
    echo(
    echo Pressione Ctrl+C para sair dos logs.
    echo Ao sair, pressione qualquer tecla para voltar ao menu.
    pause > nul
)

goto main_menu

:: Backup do Banco
:backup
cls
echo [BACKUP] Fazendo backup do banco de dados...
echo(

set backup_file=backup_anthems_%date:~6,4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%.sql
set backup_file=%backup_file: =0%

echo Criando backup em: %backup_file%
%COMPOSE% exec -T anthems-db mysqldump -u root -p$MYSQL_ROOT_PASSWORD anthems_db > %backup_file%

if %errorlevel% equ 0 (
    echo [SUCESSO] Backup criado com sucesso!
    echo Arquivo: %backup_file%
) else (
    echo [ERRO] Falha ao criar backup!
    echo Verifique se o container do banco está rodando.
)
echo(
pause
goto main_menu

:: Restaurar Backup
:restore
cls
echo [RESTAURAR] Restaurar backup do banco de dados
echo(

echo Backups disponiveis:
dir /b *.sql 2>nul
echo(

set /p backup_name=Digite o nome do arquivo de backup: 

if not exist "%backup_name%" (
    echo [ERRO] Arquivo nao encontrado!
    pause
    goto main_menu
)

echo(
echo ATENCAO: Esta operacao ira sobrescrever todos os dados atuais!
set /p confirm=Deseja continuar? [s/N]: 
if /i not "%confirm%"=="s" (
    echo Operacao cancelada.
    pause
    goto main_menu
)

echo(
echo Restaurando backup...
%COMPOSE% exec -T anthems-db mysql -u root -p$MYSQL_ROOT_PASSWORD anthems_db < %backup_name%

if %errorlevel% equ 0 (
    echo [SUCESSO] Backup restaurado com sucesso!
) else (
    echo [ERRO] Falha ao restaurar backup!
)
echo(
pause
goto main_menu

:: Limpeza do Sistema Docker
:cleanup
cls
echo [LIMPEZA] Limpeza do sistema Docker
echo(

echo Esta operacao ira:
echo - Remover containers parados
echo - Remover imagens nao utilizadas
echo - Remover volumes orfaos
echo - Remover redes nao utilizadas
echo(

set /p confirm=Deseja continuar? [s/N]: 
if /i not "%confirm%"=="s" (
    echo Operacao cancelada.
    pause
    goto main_menu
)

echo(
echo Limpando sistema Docker...
docker system prune -f
docker volume prune -f

echo(
echo [SUCESSO] Limpeza concluida!
echo(
pause
goto main_menu

:: Abrir Aplicação
:open_app
echo Abrindo aplicacao no browser...
start http://localhost:8080
timeout /t 2 > nul
goto main_menu

:: Abrir phpMyAdmin
:open_phpmyadmin
echo Abrindo phpMyAdmin no browser...
start http://localhost:8081
timeout /t 2 > nul
goto main_menu

:: Menu Avancado
:advanced_menu
cls
echo [AVANCADO] Menu de Configuracoes Avancadas
echo(

echo 1. Executar comando no container web
echo 2. Executar comando no container database
echo 3. Entrar no shell do container web
echo 4. Entrar no shell do container database
echo 5. Verificar configuracoes do PHP
echo 6. Reiniciar servico especifico
echo 7. Ver uso detalhado de recursos
echo 8. Exportar logs para arquivo
echo 0. Voltar ao menu principal
echo(

set /p adv_choice=Digite sua opcao [0-8]: 

if "%adv_choice%"=="1" goto exec_web
if "%adv_choice%"=="2" goto exec_db
if "%adv_choice%"=="3" goto shell_web
if "%adv_choice%"=="4" goto shell_db
if "%adv_choice%"=="5" goto php_info
if "%adv_choice%"=="6" goto restart_service
if "%adv_choice%"=="7" goto detailed_stats
if "%adv_choice%"=="8" goto export_logs
if "%adv_choice%"=="0" goto main_menu

echo Opcao invalida!
timeout /t 2 > nul
goto advanced_menu

:exec_web
set /p cmd=Digite o comando para executar no container web: 
%COMPOSE% exec anthems-web %cmd%
pause
goto advanced_menu

:exec_db
set /p cmd=Digite o comando para executar no container database: 
%COMPOSE% exec anthems-db %cmd%
pause
goto advanced_menu

:shell_web
echo Entrando no shell do container web...
echo Digite 'exit' para sair.
%COMPOSE% exec anthems-web /bin/bash
goto advanced_menu

:shell_db
echo Entrando no shell do container database...
echo Digite 'exit' para sair.
%COMPOSE% exec anthems-db /bin/bash
goto advanced_menu

:php_info
echo Informacoes do PHP:
%COMPOSE% exec anthems-web php -v
echo(
%COMPOSE% exec anthems-web php -m
echo(
%COMPOSE% exec anthems-web php -i | findstr /i "memory_limit max_execution_time upload_max_filesize"
pause
goto advanced_menu

:restart_service
echo Servicos disponiveis: anthems-web, anthems-db, anthems-phpmyadmin, anthems-redis
set /p service=Digite o nome do servico para reiniciar: 
%COMPOSE% restart %service%
echo Servico %service% reiniciado!
pause
goto advanced_menu

:detailed_stats
echo Estatisticas detalhadas:
docker stats --no-stream
echo(
%COMPOSE% top
pause
goto advanced_menu

:export_logs
set log_file=anthems_logs_%date:~6,4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%.txt
set log_file=%log_file: =0%
echo Exportando logs para: %log_file%
%COMPOSE% logs > %log_file%
echo Logs exportados para %log_file%!
pause
goto advanced_menu

:: Sair
:exit_script
cls
echo Obrigado por usar o Gerenciador Docker do Anthems!
echo(
timeout /t 2 > nul
exit /b 0

:: Tratamento de erro
:error
echo [ERRO] Ocorreu um erro inesperado!
echo Código de erro: %errorlevel%
pause
goto main_menu
