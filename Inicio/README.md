## Anthems — Guia de execução (Windows + Docker)

Este guia ensina a subir a aplicação completa (Web + MySQL + phpMyAdmin + Redis) no Windows com Docker Desktop.

## Requisitos
- Windows 10/11 com Docker Desktop (WSL2 habilitado)
   - Download: https://www.docker.com/products/docker-desktop
- Opcional: Git para clonar o projeto

## Arquivos importantes
- `docker-compose.yml` — Orquestra os serviços
- `Dockerfile` — Imagem Apache + PHP 8.2
- `database.sql` — Criação e seed do banco (carregado automaticamente)
- `.env.example` → copie para `.env` e ajuste senhas/URL
- `docker-quickstart.bat` e `docker-quickstart.ps1` — atalhos para subir e abrir

## 1) Preparar o .env
1. Copie `.env.example` para `.env` e ajuste se necessário.
2. Valores padrão esperados (usados pelo compose):
    - `MYSQL_DATABASE=anthems_db`
    - `MYSQL_USER=anthems_user`
    - `MYSQL_PASSWORD=...` (defina uma senha)
    - `MYSQL_ROOT_PASSWORD=...` (defina uma senha de root)
    - `APP_URL=http://localhost:8080`

Obs.: O docker-compose já injeta no container web as variáveis `DB_HOST=anthems-db`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` e `APP_URL` a partir do `.env`.

## 2) Subir a aplicação
Escolha UM dos caminhos:

- Duplo clique em `docker-quickstart.bat` (recomendado).
- Se o .bat fechar sozinho, clique com o botão direito no `docker-quickstart.ps1` → Executar com PowerShell.
- Via terminal (na pasta do projeto): `docker compose up -d`.

Primeira subida baixa imagens e pode demorar alguns minutos. Aguarde até o MySQL finalizar a inicialização.

## 3) Acessos
- App: http://localhost:8080
- phpMyAdmin: http://localhost:8081
   - Host: anthems-db
   - Usuário: valor de `MYSQL_USER` (padrão: anthems_user)
   - Senha: valor de `MYSQL_PASSWORD`

## 4) Logins de teste (pré-carregados)
- Admin: admin@anthems.com / password
- Usuários: gustavo@example.com / password, maria@example.com / password, etc.

## 5) Operações comuns
- Parar: `docker compose down`
- Recriar tudo (reset DB): `docker compose down -v && docker compose up -d --build`
- Ver logs: `docker compose logs -f --tail=100`

## 6) Dicas e solução de problemas
- Portas 8080/8081 ocupadas: feche o app em uso ou altere as portas no `docker-compose.yml`.
- MySQL não pronto: espere 10–30s após subir. Veja logs do `anthems-db`.
- Caminho com acentos/OneDrive: prefira usar o `docker-quickstart.ps1`.
- Credenciais do DB no app: definidas pelas variáveis do container web (`DB_*`).

Pronto. O fluxo mais rápido no Windows é abrir o `docker-quickstart.bat` e usar os logins de teste.
