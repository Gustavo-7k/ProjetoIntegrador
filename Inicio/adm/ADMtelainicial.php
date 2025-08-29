<?php
// Configurações da página
$page_title = "NTHMS - Anthems | Admin - Início";
$active_page = "inicio";
$base_path = "../";
$is_admin = true;

// Verificação de permissão de admin (em um sistema real)
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//     header('Location: ../login/login.php');
//     exit;
// }

// Incluir header
include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<!-- Conteúdo Administrativo -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 style="color: var(--primary-color);">Painel Administrativo</h1>
                <span class="badge bg-success fs-6">Administrador</span>
            </div>
        </div>
    </div>
    
    <!-- Cards de estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center" style="border-left: 4px solid var(--primary-color);">
                <div class="card-body">
                    <h3 class="text-primary">1,234</h3>
                    <p class="text-muted mb-0">Usuários Ativos</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <h3 class="text-success">567</h3>
                    <p class="text-muted mb-0">Comentários Hoje</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <h3 class="text-warning">12</h3>
                    <p class="text-muted mb-0">Denúncias Pendentes</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <h3 class="text-danger">3</h3>
                    <p class="text-muted mb-0">Usuários Bloqueados</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ações rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 style="color: var(--primary-color);">Ações Rápidas</h3>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <svg width="48" height="48" class="mb-3" style="color: var(--primary-color);" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
                    </svg>
                    <h5>Gerenciar Denúncias</h5>
                    <p class="text-muted small">Revisar e processar denúncias de usuários</p>
                    <a href="ADMdenuncias.php" class="btn btn-outline-primary btn-sm">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <svg width="48" height="48" class="mb-3" style="color: #28a745;" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                    </svg>
                    <h5>Gerenciar Usuários</h5>
                    <p class="text-muted small">Administrar contas e permissões</p>
                    <button class="btn btn-outline-success btn-sm" onclick="alert('Funcionalidade em desenvolvimento')">Acessar</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <svg width="48" height="48" class="mb-3" style="color: #ffc107;" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5z"/>
                        <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                    </svg>
                    <h5>Relatórios</h5>
                    <p class="text-muted small">Visualizar estatísticas e relatórios</p>
                    <button class="btn btn-outline-warning btn-sm" onclick="alert('Funcionalidade em desenvolvimento')">Acessar</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <svg width="48" height="48" class="mb-3" style="color: #17a2b8;" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                    </svg>
                    <h5>Configurações</h5>
                    <p class="text-muted small">Configurar sistema e preferências</p>
                    <button class="btn btn-outline-info btn-sm" onclick="alert('Funcionalidade em desenvolvimento')">Acessar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Atividades recentes -->
    <div class="row">
        <div class="col-12">
            <h3 style="color: var(--primary-color);">Atividades Recentes</h3>
            <div class="card">
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Novo comentário reportado</div>
                                <small class="text-muted">Usuário reportou comentário inadequado no álbum "In Rainbows"</small>
                            </div>
                            <span class="badge bg-warning rounded-pill">Há 2 min</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Novo usuário registrado</div>
                                <small class="text-muted">Maria Silva se registrou na plataforma</small>
                            </div>
                            <span class="badge bg-success rounded-pill">Há 15 min</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Comentário moderado</div>
                                <small class="text-muted">Comentário foi aprovado após revisão</small>
                            </div>
                            <span class="badge bg-info rounded-pill">Há 1 hora</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/chat-sidebar.php'; ?>

<?php include '../includes/footer.php'; ?>
