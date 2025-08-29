#!/bin/bash
# Script de deploy para o projeto Anthems
# Este script automatiza o processo de deploy da aplicação

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para log
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

# Verificar se Docker está instalado
check_docker() {
    if ! command -v docker &> /dev/null; then
        error "Docker não está instalado. Por favor, instale o Docker primeiro."
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro."
    fi
    
    log "Docker e Docker Compose verificados ✓"
}

# Verificar se arquivo .env existe
check_env() {
    if [ ! -f .env ]; then
        warn "Arquivo .env não encontrado. Criando arquivo de exemplo..."
        cat > .env << EOF
# Configurações do Banco de Dados
MYSQL_ROOT_PASSWORD=senha_root_forte_$(date +%s)
MYSQL_DATABASE=anthems_db
MYSQL_USER=anthems_user
MYSQL_PASSWORD=senha_usuario_forte_$(date +%s)

# Configurações PHP
PHP_MEMORY_LIMIT=256M
PHP_POST_MAX_SIZE=64M
PHP_UPLOAD_MAX_FILESIZE=64M

# Configurações da Aplicação
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080
EOF
        warn "Arquivo .env criado com senhas aleatórias. Por favor, revise as configurações."
        echo "Pressione Enter para continuar ou Ctrl+C para sair..."
        read
    fi
    log "Arquivo .env verificado ✓"
}

# Fazer backup se containers já existirem
backup_if_exists() {
    if docker-compose ps | grep -q anthems; then
        warn "Containers existentes encontrados. Fazendo backup..."
        
        # Backup do banco de dados
        if docker-compose ps | grep -q anthems_db; then
            log "Fazendo backup do banco de dados..."
            docker-compose exec db mysqldump -u root -p$MYSQL_ROOT_PASSWORD $MYSQL_DATABASE > "backup_$(date +%Y%m%d_%H%M%S).sql" 2>/dev/null || warn "Não foi possível fazer backup automático do banco"
        fi
        
        # Parar containers
        log "Parando containers existentes..."
        docker-compose down
    fi
}

# Build e deploy da aplicação
deploy() {
    log "Iniciando build da aplicação..."
    
    # Build das imagens
    docker-compose build --no-cache
    
    log "Iniciando containers..."
    
    # Subir containers
    docker-compose up -d
    
    # Aguardar containers ficarem prontos
    log "Aguardando containers ficarem prontos..."
    sleep 30
    
    # Verificar se containers estão rodando
    if ! docker-compose ps | grep -q "Up"; then
        error "Erro ao iniciar containers. Verificar logs com: docker-compose logs"
    fi
    
    log "Containers iniciados com sucesso ✓"
}

# Verificar saúde da aplicação
health_check() {
    log "Verificando saúde da aplicação..."
    
    # Testar conexão web
    if curl -f http://localhost:8080 > /dev/null 2>&1; then
        log "Aplicação web respondendo ✓"
    else
        warn "Aplicação web não está respondendo na porta 8080"
    fi
    
    # Testar phpMyAdmin
    if curl -f http://localhost:8081 > /dev/null 2>&1; then
        log "phpMyAdmin respondendo ✓"
    else
        warn "phpMyAdmin não está respondendo na porta 8081"
    fi
    
    # Verificar logs por erros críticos
    if docker-compose logs --tail=50 | grep -i "error\|fatal\|exception" > /dev/null; then
        warn "Possíveis erros encontrados nos logs. Execute 'docker-compose logs' para detalhes."
    fi
}

# Mostrar informações de acesso
show_info() {
    log "Deploy concluído com sucesso! 🎉"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "                              INFORMAÇÕES DE ACESSO"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "🎵 Aplicação Anthems:  http://localhost:8080"
    echo "🗄️  phpMyAdmin:        http://localhost:8081"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Comandos úteis:"
    echo "  • Ver logs:           docker-compose logs -f"
    echo "  • Parar aplicação:    docker-compose down"
    echo "  • Reiniciar:          docker-compose restart"
    echo "  • Status containers:  docker-compose ps"
    echo ""
}

# Função principal
main() {
    log "🚀 Iniciando deploy do Anthems..."
    
    check_docker
    check_env
    backup_if_exists
    deploy
    health_check
    show_info
    
    log "Deploy finalizado! Aplicação pronta para uso."
}

# Tratar interrupção do script
trap 'error "Deploy interrompido pelo usuário"' INT

# Executar função principal
main
