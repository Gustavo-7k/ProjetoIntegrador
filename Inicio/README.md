# Projeto Anthems — Guia de Execucao (Windows)

Este guia mostra o passo a passo para subir a aplicacao completa (Web + MySQL + phpMyAdmin + Redis) usando Docker.

## Requisitos
- Windows 10/11 com Docker Desktop instalado (WSL2 habilitado)
  - Download: https://www.docker.com/products/docker-desktop
- (Opcional) Git para clonar o repositorio

## Estrutura principal
- `docker-compose.yml` — Orquestra os servicos
- `Dockerfile` — Imagem do Apache + PHP
- `docker-manager.bat` — Menu completo para gerenciar o stack
- `docker-quickstart.bat` — Sobe tudo e abre o navegador (modo rapido)
 - `docker-quickstart.bat` — Sobe tudo e abre o navegador (modo rapido)
 - `docker-quickstart.ps1` — Alternativa PowerShell para o quickstart (use se o .bat nao funcionar)
- `.env.example` — Modelo de variaveis de ambiente
- `.env` — Variaveis da aplicacao (criado a partir do example)

## 1) Preparar o ambiente (.env)
Se ainda nao existir um arquivo `.env`, crie a partir do exemplo:

- Via Explorer: copie `.env.example` para `.env` e edite as senhas/URLs.
- Via terminal (opcional):

```bash
cp .env.example .env
```

Campos importantes no `.env` (exemplos):
- `APP_URL=http://localhost:8080`
- `MYSQL_ROOT_PASSWORD=...`
- `MYSQL_DATABASE=anthems_db`
- `MYSQL_USER=anthems`
- `MYSQL_PASSWORD=...`

## 2) Subir a aplicacao (tres opcoes)

### Opcao A — Um clique (recomendado)
1. De duplo clique em `docker-quickstart.bat`.
   - Se preferir PowerShell (ou se o `.bat` nao abrir), use `docker-quickstart.ps1` (clique com o botao direito > Executar com PowerShell).
2. Aguarde o "HTTP 200". O navegador abrira a aplicacao e o phpMyAdmin.

### Opcao B — Gerenciador (menu interativo)
1. De duplo clique em `docker-manager.bat`.
2. Escolha:
   - `1` Iniciar Aplicacao
   - `13` Health Check (opcional) para validar Web/DB/Redis
   - `10` Abrir Aplicacao ou `11` phpMyAdmin

### Opcao C — Terminal
Na pasta do projeto (onde esta o `docker-compose.yml`):

```bash
docker compose up -d  # ou: docker-compose up -d
```

## 3) Acessos
- Aplicacao: `http://localhost:8080` (Index: `inicio.php`)
- phpMyAdmin: `http://localhost:8081`  
  - Host: `anthems-db`  
  - Usuario/senha: conforme `.env`

## 4) Comandos uteis

### Parar
- Gerenciador: opcao `2. Parar Aplicacao`
- Terminal:
```bash
docker compose down
```

### Rebuild (forcar nova imagem)
- Gerenciador: `4. Rebuild Completo`
- Terminal:
```bash
docker compose down --volumes --remove-orphans
docker compose build --no-cache --pull
docker compose up -d
```

### Logs
- Gerenciador: `6. Ver Logs`
- Terminal:
```bash
docker compose logs --tail=100 -f
```

### Backup e Restore do banco
- Gerenciador: `7. Backup` / `8. Restaurar`
- Terminal (exemplos):
```bash
# Backup
docker compose exec -T anthems-db mysqldump -u root -p$MYSQL_ROOT_PASSWORD anthems_db > backup.sql

# Restore
docker compose exec -T anthems-db mysql -u root -p$MYSQL_ROOT_PASSWORD anthems_db < backup.sql
```

### Health Check (verificar servicos)
- Gerenciador: `13. Health Check`

## 5) Dicas e solucao de problemas
- Primeira execucao pode demorar (download de imagens).
- Conflito de portas 8080/8081: feche o app que ocupa a porta ou altere as portas no `docker-compose.yml`.
- Se a aplicacao nao abrir:
  - Use `docker-manager.bat` -> `6. Ver Logs` para checar `anthems-web` e `anthems-db`.
  - Verifique o `.env` (senhas e `DB_HOST=anthems-db`).
   - Se algum `.bat` fechar sozinho ou aparecer "foi inesperado neste momento.", execute `docker-quickstart.ps1` como alternativa.
- OneDrive/caminho com espacos/acentos: execute os `.bat` diretamente da pasta do projeto (ja preparado com aspas nos caminhos).

## 6) Desenvolvimento
- Codigo PHP esta servido via Apache dentro do container `anthems-web`.
- Ao editar arquivos locais, o container reflete as mudancas (bind mount no `docker-compose.yml`).
- Reinicie somente se alterar dependencias/ambiente.

## 7) Servicos e portas
- Web (Apache+PHP): `8080 -> 80`
- MySQL: `3306`
- phpMyAdmin: `8081`
- Redis: `6379`

---

Pronto! Para o uso diario, `docker-quickstart.bat` e o caminho mais rapido. Para diagnosticos e operacoes avancadas, use o `docker-manager.bat`.
# ANTHEMS - Migração HTML para PHP

## Resumo da Migração

Este projeto foi completamente refatorado de HTML estático para PHP dinâmico, com melhorias significativas em:

### ✅ **Conversões Realizadas**

1. **CSS Centralizado** (`css/estilos.css`)
   - Removidos todos os estilos inline
   - Criado sistema de variáveis CSS
   - Melhorada responsividade para mobile, tablet e desktop
   - Adicionadas animações e transições

2. **Includes PHP** (`includes/`)
   - `header.php` - Cabeçalho com meta tags e CSS
   - `navbar.php` - Menu de navegação reutilizável
   - `chat-sidebar.php` - Barra lateral de chat
   - `footer.php` - Rodapé com scripts JavaScript

3. **JavaScript Centralizado** (`js/anthems.js`)
   - Módulo de chat com funcionalidades completas
   - Sistema de perfil com upload de imagens
   - Validação de formulários em tempo real
   - Busca e filtros
   - Utilitários para acessibilidade

4. **Páginas Convertidas**
   - `inicio.php` - Página inicial
   - `login/login.php` - Sistema de login
   - `login/novologin.php` - Cadastro de usuários
   - `perfil/perfil.php` - Página de perfil com edição
   - `comentarios/novocomentario.php` - Busca de álbuns
   - `comentarios/EscreverComentário.php` - Formulário de comentário
   - `adm/ADMtelainicial.php` - Painel administrativo

5. **Sistema Backend**
   - `config.php` - Configurações e funções utilitárias
   - `comentarios/submit_comment.php` - Processamento de comentários
   - `login/register_process.php` - Processamento de registro

### 🎨 **Melhorias de Design**

- **Responsividade Aprimorada**: Breakpoints otimizados para todos os dispositivos
- **Sistema de Cores Centralizado**: Variáveis CSS para manter consistência
- **Animações Suaves**: Transições CSS para melhor experiência
- **Acessibilidade**: Navegação por teclado e leitores de tela
- **Loading States**: Feedback visual durante operações

### 🔧 **Funcionalidades Implementadas**

- **Sistema de Autenticação**: Login/logout seguro
- **CSRF Protection**: Tokens para prevenir ataques
- **Validação Robusta**: Frontend e backend
- **Upload de Imagens**: Com redimensionamento automático
- **Sistema de Chat**: Interface moderna
- **Busca Inteligente**: Com autocompletar
- **Painel Admin**: Estatísticas e gerenciamento

## 📁 **Estrutura do Projeto**

```
/Inicio/
├── config.php                 # Configurações principais
├── inicio.php                 # Página inicial
├── includes/                  # Arquivos de inclusão
│   ├── header.php
│   ├── navbar.php
│   ├── chat-sidebar.php
│   └── footer.php
├── css/
│   ├── inicioTEMPLATE.css    # Bootstrap customizado
│   └── estilos.css           # CSS centralizado
├── js/
│   └── anthems.js            # JavaScript centralizado
├── login/
│   ├── login.php             # Formulário de login
│   ├── novologin.php         # Cadastro
│   └── register_process.php  # Processamento cadastro
├── perfil/
│   └── perfil.php            # Página de perfil
├── comentarios/
│   ├── novocomentario.php    # Busca de álbuns
│   ├── EscreverComentário.php # Formulário de comentário
│   └── submit_comment.php    # Processamento comentário
├── adm/
│   └── ADMtelainicial.php    # Painel administrativo
├── img/                      # Imagens do projeto
└── uploads/                  # Uploads de usuários
```

## 🚀 **Instruções de Instalação**

### 1. Configuração do Banco de Dados

Crie as seguintes tabelas no MySQL:

```sql
-- Tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email_token VARCHAR(64),
    email_verified_at DATETIME NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    newsletter BOOLEAN DEFAULT FALSE,
    terms_accepted_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de álbuns
CREATE TABLE albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) NOT NULL,
    cover_image VARCHAR(255),
    release_year YEAR,
    genre VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de comentários
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    album_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE
);

-- Tabela de denúncias
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    comment_id INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
);
```

### 2. Configuração do PHP

Edite o arquivo `config.php` com suas configurações:

```php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'anthems_db');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');

// URL da aplicação
define('APP_URL', 'http://localhost/anthems/');
```

### 3. Permissões de Diretórios

```bash
chmod 755 uploads/
chmod 755 logs/
```

### 4. Dependências (Opcional)

Para funcionalidades avançadas, instale via Composer:

```bash
composer require phpmailer/phpmailer  # Para envio de emails
composer require intervention/image   # Para manipulação de imagens
```

## 🔧 **Próximos Passos Recomendados**

### Páginas Restantes para Converter

1. **Comentários**
   - `comentarios/Vercomentario.php` - Visualizar comentário específico
   - `comentarios/VerComentariosConexoes.php` - Feed de comentários

2. **Administrativo**
   - `adm/ADMdenuncias.php` - Gerenciar denúncias
   - `adm/ADMVerComentario.php` - Moderar comentário

3. **Outros**
   - `notificacoes/todasnotificacoes.php` - Sistema de notificações
   - `albuns/vertodosalbunsartista.php` - Lista de álbuns
   - `login/novasenha.php` - Recuperação de senha

### Melhorias Sugeridas

1. **Segurança**
   - Rate limiting para formulários
   - Validação de arquivos de imagem mais rigorosa
   - Logs de segurança detalhados

2. **Performance**
   - Cache de consultas SQL
   - Compressão de imagens automática
   - CDN para assets estáticos

3. **Funcionalidades**
   - Sistema de follows/conexões
   - Notificações em tempo real
   - API REST para mobile app

4. **UX/UI**
   - Dark mode
   - PWA (Progressive Web App)
   - Busca com filtros avançados

## 📱 **Responsividade**

O sistema foi otimizado para:

- **Mobile** (< 576px): Layout vertical, menu colapsível
- **Tablet** (576px - 991px): Layout híbrido
- **Desktop** (> 992px): Layout completo com sidebar

## 🛡️ **Segurança Implementada**

- Proteção CSRF em todos os formulários
- Sanitização de entrada de dados
- Validação server-side
- Hash seguro de senhas (bcrypt)
- Proteção contra SQL injection (PDO prepared statements)
- Headers de segurança

## 🎯 **Compatibilidade**

- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Browsers**: Chrome 80+, Firefox 75+, Safari 13+, Edge 80+

---

**Desenvolvido com ❤️ para a comunidade musical Anthems**
